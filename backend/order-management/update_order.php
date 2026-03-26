<?php
/**
 * Update order and payment states from Order Management.
 */

header('Content-Type: application/json');

require_once '../auth.php';
require_once '../../database/connect_database.php';
require_once 'order_workflow_helper.php';
require_once '../../library/EmailService.php';

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

function orderActionError($message, $statusCode = 400) {
    http_response_code($statusCode);
    echo json_encode(['success' => false, 'message' => $message]);
    exit;
}

function buildStatusUpdateEmail(array $order, $headline, $message, $extraNote = '') {
    $orderNumber = htmlspecialchars((string)($order['order_number'] ?? ''));
    $customerName = htmlspecialchars((string)($order['customer_name'] ?? 'Customer'));
    $statusLabel = htmlspecialchars(mincDescribeOrderStatus($order['order_status'] ?? '', $order['delivery_method'] ?? 'shipping'));
    $paymentLabel = htmlspecialchars(mincDescribePaymentStatus($order['payment_status'] ?? '', $order['payment_method'] ?? 'cod', $order['payment_proof_path'] ?? ''));
    $extraNoteHtml = $extraNote !== ''
        ? '<p style="margin-top:14px;padding:12px;border:1px solid #fed7d7;background:#fff5f5;border-radius:8px;">' . htmlspecialchars($extraNote) . '</p>'
        : '';

    return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: Arial, sans-serif; background:#f8fafc; color:#1f2937; }
                .container { max-width:640px; margin:0 auto; background:#ffffff; border:1px solid #e5e7eb; border-radius:14px; overflow:hidden; }
                .header { background: linear-gradient(135deg, #08415c 0%, #0a5273 100%); color:#ffffff; padding:24px; }
                .content { padding:24px; }
                .summary { background:#f8fafc; border:1px solid #e5e7eb; border-radius:10px; padding:16px; margin-top:16px; }
                .footer { padding:0 24px 24px; font-size:12px; color:#6b7280; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h2 style="margin:0 0 8px;">' . htmlspecialchars($headline) . '</h2>
                    <p style="margin:0;">MinC Auto Supply</p>
                </div>
                <div class="content">
                    <p>Hello <strong>' . $customerName . '</strong>,</p>
                    <p>' . htmlspecialchars($message) . '</p>
                    <div class="summary">
                        <p><strong>Order Number:</strong> ' . $orderNumber . '</p>
                        <p><strong>Order Status:</strong> ' . $statusLabel . '</p>
                        <p><strong>Payment Status:</strong> ' . $paymentLabel . '</p>
                    </div>
                    ' . $extraNoteHtml . '
                </div>
                <div class="footer">This is an automated update from MinC Auto Supply.</div>
            </div>
        </body>
        </html>';
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!is_array($input)) {
        orderActionError('Invalid request payload');
    }

    $order_id = isset($input['order_id']) ? (int)$input['order_id'] : 0;
    $action = isset($input['action']) ? trim((string)$input['action']) : '';
    $reason = mincNormalizeWhitespace($input['reason'] ?? '');
    $tracking_number = mincNormalizeWhitespace($input['tracking_number'] ?? '');

    if ($order_id <= 0 || $action === '') {
        orderActionError('Order ID and action are required');
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
        orderActionError('Invalid action');
    }

    $pdo->beginTransaction();

    $stmt = $pdo->prepare('
        SELECT o.*, CONCAT(c.first_name, " ", c.last_name) AS customer_name, c.email AS customer_email
        FROM orders o
        INNER JOIN customers c ON c.customer_id = o.customer_id
        WHERE o.order_id = :order_id
        FOR UPDATE
    ');
    $stmt->execute([':order_id' => $order_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        throw new Exception('Order not found');
    }

    $orderColumns = mincGetTableColumns($pdo, 'orders');
    $newOrderStatus = $order['order_status'];
    $newPaymentStatus = $order['payment_status'];
    $updateParts = [
        'order_status = :order_status',
        'payment_status = :payment_status',
        'updated_at = NOW()'
    ];
    $updateParams = [
        ':order_status' => $newOrderStatus,
        ':payment_status' => $newPaymentStatus,
        ':order_id' => $order_id
    ];

    $statusEmailHeadline = 'Order Updated';
    $statusEmailMessage = 'Your order status has been updated.';
    $emailExtraNote = '';
    $successMessage = 'Order updated successfully.';

    switch ($action) {
        case 'confirm_order':
            if ($order['order_status'] !== 'pending') {
                throw new Exception('Only pending orders can be confirmed.');
            }
            $newOrderStatus = 'confirmed';
            if (mincPaymentMethodRequiresProof($order['payment_method'])) {
                if (trim((string)($order['payment_proof_path'] ?? '')) === '') {
                    throw new Exception('Payment proof must be attached before confirming this order.');
                }
                $newPaymentStatus = 'paid';
                if (in_array('payment_reviewed_at', $orderColumns, true)) {
                    $updateParts[] = 'payment_reviewed_at = NOW()';
                }
                if (in_array('payment_reviewed_by', $orderColumns, true)) {
                    $updateParts[] = 'payment_reviewed_by = :payment_reviewed_by';
                    $updateParams[':payment_reviewed_by'] = $_SESSION['user_id'] ?? null;
                }
                if (in_array('payment_review_notes', $orderColumns, true)) {
                    $updateParts[] = 'payment_review_notes = :payment_review_notes';
                    $updateParams[':payment_review_notes'] = $reason !== '' ? $reason : 'Payment proof approved.';
                }
                $statusEmailHeadline = 'Order Confirmed';
                $statusEmailMessage = 'Your payment proof has been reviewed and your order is now confirmed.';
                $successMessage = 'Payment proof approved and order confirmed.';
            } else {
                $statusEmailHeadline = 'COD Order Confirmed';
                $statusEmailMessage = 'Your COD order has been confirmed and will move to processing.';
                $successMessage = 'COD order confirmed.';
            }
            if (in_array('confirmed_at', $orderColumns, true)) {
                $updateParts[] = 'confirmed_at = NOW()';
            }
            break;

        case 'process_order':
            if ($order['order_status'] !== 'confirmed') {
                throw new Exception('Only confirmed orders can be moved to processing.');
            }
            $newOrderStatus = 'processing';
            $statusEmailHeadline = 'Order Processing';
            $statusEmailMessage = 'Your order is now being prepared.';
            $successMessage = 'Order moved to processing.';
            break;

        case 'ship_order':
            if ($order['order_status'] !== 'processing') {
                throw new Exception('Only processing orders can be marked ready for release.');
            }
            $newOrderStatus = 'shipped';
            if (($order['delivery_method'] ?? 'shipping') !== 'pickup' && $tracking_number !== '' && in_array('tracking_number', $orderColumns, true)) {
                $updateParts[] = 'tracking_number = :tracking_number';
                $updateParams[':tracking_number'] = $tracking_number;
            }
            $statusEmailHeadline = ($order['delivery_method'] ?? 'shipping') === 'pickup' ? 'Order Ready for Pickup' : 'Order Out for Delivery';
            $statusEmailMessage = ($order['delivery_method'] ?? 'shipping') === 'pickup'
                ? 'Your order is ready for pickup at the store.'
                : 'Your order has been released for delivery.';
            $successMessage = ($order['delivery_method'] ?? 'shipping') === 'pickup'
                ? 'Order marked ready for pickup.'
                : 'Order released for delivery.';
            if ($tracking_number !== '') {
                $emailExtraNote = 'Tracking Number: ' . $tracking_number;
            }
            break;

        case 'deliver_order':
            if ($order['order_status'] !== 'shipped') {
                throw new Exception('Only released orders can be completed.');
            }
            if (($order['payment_method'] ?? 'cod') === 'cod' && $order['payment_status'] !== 'paid') {
                throw new Exception('Record COD payment before completing the order.');
            }
            $newOrderStatus = 'delivered';
            if (in_array('completed_at', $orderColumns, true)) {
                $updateParts[] = 'completed_at = NOW()';
            }
            if (in_array('receipt_path', $orderColumns, true)) {
                $updateParts[] = 'receipt_path = :receipt_path';
                $updateParams[':receipt_path'] = 'html/order-receipt.php?order=' . rawurlencode((string)$order['order_number']);
            }
            if (in_array('receipt_uploaded_at', $orderColumns, true)) {
                $updateParts[] = 'receipt_uploaded_at = NOW()';
            }
            $statusEmailHeadline = 'Order Completed';
            $statusEmailMessage = 'Your order has been completed. A receipt is now attached to your order record.';
            $successMessage = 'Order completed and receipt attached.';
            break;

        case 'mark_paid':
            if ($order['payment_status'] !== 'pending') {
                throw new Exception('Only pending payments can be marked as paid.');
            }
            if ($order['order_status'] === 'cancelled') {
                throw new Exception('Cannot mark payment for a cancelled order.');
            }
            if (mincPaymentMethodRequiresProof($order['payment_method'])) {
                throw new Exception('Use Confirm Order after reviewing the uploaded proof for online payments.');
            }
            $newPaymentStatus = 'paid';
            if (in_array('payment_review_notes', $orderColumns, true)) {
                $updateParts[] = 'payment_review_notes = :payment_review_notes';
                $updateParams[':payment_review_notes'] = $reason !== '' ? $reason : 'COD payment collected.';
            }
            $statusEmailHeadline = 'Payment Recorded';
            $statusEmailMessage = 'Your payment has been recorded successfully.';
            $successMessage = 'Payment recorded successfully.';
            break;

        case 'cancel_order':
            if (!in_array($order['order_status'], ['pending', 'confirmed', 'processing'], true)) {
                throw new Exception('Order cancellation is allowed only before release to the customer.');
            }
            if ($reason === '') {
                throw new Exception('Cancellation reason is required.');
            }
            $newOrderStatus = 'cancelled';
            if (in_array('cancel_reason', $orderColumns, true)) {
                $updateParts[] = 'cancel_reason = :cancel_reason';
                $updateParams[':cancel_reason'] = $reason;
            }
            if (in_array('cancelled_at', $orderColumns, true)) {
                $updateParts[] = 'cancelled_at = NOW()';
            }
            if (in_array('cancelled_by', $orderColumns, true)) {
                $updateParts[] = 'cancelled_by = :cancelled_by';
                $updateParams[':cancelled_by'] = $_SESSION['user_id'] ?? null;
            }
            $statusEmailHeadline = 'Order Cancelled';
            $statusEmailMessage = 'Your order was cancelled by the admin team.';
            $emailExtraNote = 'Reason: ' . $reason;
            $successMessage = 'Order cancelled successfully.';
            break;

        case 'refund_payment':
            if ($order['payment_status'] !== 'paid') {
                throw new Exception('Only paid orders can be refunded.');
            }
            if (!in_array($order['order_status'], ['cancelled', 'delivered'], true)) {
                throw new Exception('Refund is only allowed for completed or cancelled orders.');
            }
            $newPaymentStatus = 'refunded';
            if (in_array('payment_review_notes', $orderColumns, true)) {
                $updateParts[] = 'payment_review_notes = :payment_review_notes';
                $updateParams[':payment_review_notes'] = $reason !== '' ? $reason : 'Payment refunded.';
            }
            $statusEmailHeadline = 'Payment Refunded';
            $statusEmailMessage = 'Your payment has been refunded.';
            if ($reason !== '') {
                $emailExtraNote = 'Refund note: ' . $reason;
            }
            $successMessage = 'Refund processed successfully.';
            break;
    }

    $updateParams[':order_status'] = $newOrderStatus;
    $updateParams[':payment_status'] = $newPaymentStatus;

    if ($reason !== '' && !in_array('payment_review_notes = :payment_review_notes', $updateParts, true)) {
        $timestamp = date('Y-m-d H:i:s');
        $actor = trim((string)(($_SESSION['fname'] ?? '') . ' ' . ($_SESSION['lname'] ?? '')));
        $existingNotes = trim((string)($order['notes'] ?? ''));
        $combinedNote = trim($existingNotes . "\n[{$timestamp}] {$action} by {$actor}: {$reason}");
        $updateParts[] = 'notes = :notes';
        $updateParams[':notes'] = $combinedNote;
    }

    $update = $pdo->prepare('UPDATE orders SET ' . implode(', ', $updateParts) . ' WHERE order_id = :order_id');
    $update->execute($updateParams);

    $audit = $pdo->prepare('
        INSERT INTO audit_trail
        (user_id, session_username, action, entity_type, entity_id, old_value, new_value, change_reason, ip_address, user_agent)
        VALUES
        (:user_id, :session_username, :action, :entity_type, :entity_id, :old_value, :new_value, :change_reason, :ip_address, :user_agent)
    ');
    $audit->execute([
        ':user_id' => $_SESSION['user_id'] ?? null,
        ':session_username' => trim((string)(($_SESSION['fname'] ?? '') . ' ' . ($_SESSION['lname'] ?? ''))),
        ':action' => 'update_order_state',
        ':entity_type' => 'order',
        ':entity_id' => $order_id,
        ':old_value' => json_encode([
            'order_status' => $order['order_status'],
            'payment_status' => $order['payment_status'],
            'tracking_number' => $order['tracking_number'] ?? null
        ]),
        ':new_value' => json_encode([
            'order_status' => $newOrderStatus,
            'payment_status' => $newPaymentStatus,
            'action' => $action,
            'tracking_number' => $tracking_number !== '' ? $tracking_number : ($order['tracking_number'] ?? null)
        ]),
        ':change_reason' => $reason !== '' ? $reason : $action,
        ':ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
        ':user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
    ]);

    $pdo->commit();

    $order['order_status'] = $newOrderStatus;
    $order['payment_status'] = $newPaymentStatus;
    if (isset($updateParams[':tracking_number'])) {
        $order['tracking_number'] = $updateParams[':tracking_number'];
    }
    if (isset($updateParams[':receipt_path'])) {
        $order['receipt_path'] = $updateParams[':receipt_path'];
    }

    try {
        $emailService = new EmailService();
        $emailBody = buildStatusUpdateEmail($order, $statusEmailHeadline, $statusEmailMessage, $emailExtraNote);
        $emailService->send((string)$order['customer_email'], 'MinC order update: ' . $statusEmailHeadline, $emailBody);
    } catch (Exception $emailError) {
        error_log('Order status email failed: ' . $emailError->getMessage());
    }

    echo json_encode([
        'success' => true,
        'message' => $successMessage,
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
    orderActionError($e->getMessage());
}
?>
