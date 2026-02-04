<?php
/**
 * Update order and payment states from Order Management.
 */

header('Content-Type: application/json');

require_once '../auth.php';
require_once '../../database/connect_database.php';

$validation = validateSession(false);
if (!$validation['valid']) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

if (!isManagementLevel()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $order_id = isset($input['order_id']) ? (int)$input['order_id'] : 0;
    $action = isset($input['action']) ? trim((string)$input['action']) : '';
    $reason = isset($input['reason']) ? trim((string)$input['reason']) : '';

    if ($order_id <= 0 || $action === '') {
        throw new Exception('Order ID and action are required');
    }

    $allowedActions = [
        'confirm_order',
        'process_order',
        'ship_order',
        'deliver_order',
        'mark_paid',
        'cancel_order',
        'refund_payment'
    ];
    if (!in_array($action, $allowedActions, true)) {
        throw new Exception('Invalid action');
    }

    $pdo->beginTransaction();

    $stmt = $pdo->prepare("SELECT * FROM orders WHERE order_id = :order_id FOR UPDATE");
    $stmt->execute([':order_id' => $order_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        throw new Exception('Order not found');
    }

    $newOrderStatus = $order['order_status'];
    $newPaymentStatus = $order['payment_status'];

    switch ($action) {
        case 'confirm_order':
            if ($order['order_status'] !== 'pending') {
                throw new Exception('Only pending orders can be confirmed');
            }
            $newOrderStatus = 'confirmed';
            break;

        case 'process_order':
            if ($order['order_status'] !== 'confirmed') {
                throw new Exception('Only confirmed orders can be moved to processing');
            }
            $newOrderStatus = 'processing';
            break;

        case 'ship_order':
            if ($order['order_status'] !== 'processing') {
                throw new Exception('Only processing orders can be marked shipped');
            }
            $newOrderStatus = 'shipped';
            break;

        case 'deliver_order':
            if ($order['order_status'] !== 'shipped') {
                throw new Exception('Only shipped orders can be marked delivered');
            }
            $newOrderStatus = 'delivered';
            break;

        case 'mark_paid':
            if ($order['payment_status'] !== 'pending') {
                throw new Exception('Only pending payments can be completed');
            }
            if ($order['order_status'] === 'cancelled') {
                throw new Exception('Cannot complete payment for a cancelled order');
            }
            $newPaymentStatus = 'paid';
            break;

        case 'cancel_order':
            if (!in_array($order['order_status'], ['pending', 'confirmed'], true)) {
                throw new Exception('Order cancellation is allowed only before processing');
            }
            $newOrderStatus = 'cancelled';
            break;

        case 'refund_payment':
            if ($order['payment_status'] !== 'paid') {
                throw new Exception('Only paid orders can be refunded');
            }
            if (!in_array($order['order_status'], ['cancelled', 'delivered'], true)) {
                throw new Exception('Refund is only allowed for eligible orders');
            }
            $newPaymentStatus = 'refunded';
            break;
    }

    $update = $pdo->prepare("
        UPDATE orders
        SET order_status = :order_status,
            payment_status = :payment_status,
            notes = CONCAT(COALESCE(notes, ''), :note_append),
            updated_at = NOW()
        WHERE order_id = :order_id
    ");

    $noteAppend = '';
    if ($reason !== '') {
        $actor = trim(($_SESSION['fname'] ?? '') . ' ' . ($_SESSION['lname'] ?? ''));
        $noteAppend = "\n[" . date('Y-m-d H:i:s') . "] {$action} by {$actor}: {$reason}";
    }

    $update->execute([
        ':order_status' => $newOrderStatus,
        ':payment_status' => $newPaymentStatus,
        ':note_append' => $noteAppend,
        ':order_id' => $order_id
    ]);

    $audit = $pdo->prepare("
        INSERT INTO audit_trail
        (user_id, session_username, action, entity_type, entity_id, old_value, new_value, change_reason, ip_address, user_agent)
        VALUES
        (:user_id, :session_username, :action, :entity_type, :entity_id, :old_value, :new_value, :change_reason, :ip_address, :user_agent)
    ");
    $audit->execute([
        ':user_id' => $_SESSION['user_id'] ?? null,
        ':session_username' => trim(($_SESSION['fname'] ?? '') . ' ' . ($_SESSION['lname'] ?? '')),
        ':action' => 'update_order_state',
        ':entity_type' => 'order',
        ':entity_id' => $order_id,
        ':old_value' => json_encode([
            'order_status' => $order['order_status'],
            'payment_status' => $order['payment_status']
        ]),
        ':new_value' => json_encode([
            'order_status' => $newOrderStatus,
            'payment_status' => $newPaymentStatus,
            'action' => $action
        ]),
        ':change_reason' => $reason !== '' ? $reason : $action,
        ':ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
        ':user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
    ]);

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Order updated successfully',
        'order' => [
            'order_id' => $order_id,
            'order_status' => $newOrderStatus,
            'payment_status' => $newPaymentStatus
        ]
    ]);
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

