<?php
/**
 * Chat Message Handler
 * Handles sending and retrieving chat messages
 */

header('Content-Type: application/json');
require_once '../../database/connect_database.php';

try {
    // Get the request method
    $method = $_SERVER['REQUEST_METHOD'];
    
    if ($method === 'POST') {
        // Send message
        $rawInput = file_get_contents('php://input');
        $data = json_decode($rawInput, true);
        
        // Log for debugging
        error_log("Chat POST received: " . json_encode([
            'data_keys' => array_keys($data ?? []),
            'session_id' => $data['session_id'] ?? 'NOT SET',
            'message_length' => strlen($data['message_content'] ?? '')
        ]));
        
        if (!isset($data['message_content']) || empty(trim($data['message_content']))) {
            throw new Exception('Message content is required');
        }
        
        $sender_name = $data['sender_name'] ?? 'Anonymous Customer';
        $sender_email = $data['sender_email'] ?? null;
        $message_content = htmlspecialchars($data['message_content']);
        $sender_id = isset($data['sender_id']) ? intval($data['sender_id']) : null;
        $sender_type = isset($data['sender_type']) && $data['sender_type'] === 'admin' ? 'admin' : 'customer';
        
        // Use session_id from request data, fallback to PHP session_id
        $session_id = $data['session_id'] ?? session_id();
        
        if (empty($session_id)) {
            error_log("ERROR: Session ID is empty!");
            throw new Exception('Session ID is required');
        }
        
        error_log("Chat message will be saved with session_id: " . $session_id);
        
        // Insert message
        $query = "INSERT INTO chat_messages 
                  (sender_id, sender_name, sender_email, sender_type, message_content, session_id, created_at) 
                  VALUES (:sender_id, :sender_name, :sender_email, :sender_type, :message_content, :session_id, NOW())";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute([
            ':sender_id' => $sender_id,
            ':sender_name' => $sender_name,
            ':sender_email' => $sender_email,
            ':sender_type' => $sender_type,
            ':message_content' => $message_content,
            ':session_id' => $session_id
        ]);
        
        // Get the last inserted message
        $lastId = $pdo->lastInsertId();
        $getQuery = "SELECT * FROM chat_messages WHERE message_id = :message_id";
        $getStmt = $pdo->prepare($getQuery);
        $getStmt->execute([':message_id' => $lastId]);
        $message = $getStmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Message sent successfully',
            'data' => $message
        ]);
        
    } elseif ($method === 'GET') {
        // Get messages
        $sender_type = $_GET['type'] ?? 'customer'; // 'customer' or 'admin'
        
        // Get session_id from query parameter, fallback to PHP session_id
        $session_id = $_GET['session_id'] ?? session_id();
        
        if (empty($session_id)) {
            throw new Exception('Session ID is required');
        }
        
        if ($sender_type === 'admin') {
            // Admin can see all unread messages and conversation summaries
            $query = "SELECT DISTINCT 
                        MIN(message_id) as first_message_id,
                        sender_id, 
                        sender_name, 
                        sender_email,
                        sender_type,
                        session_id,
                        COUNT(*) as message_count,
                        MAX(created_at) as last_message_time,
                        SUM(CASE WHEN is_read = 0 THEN 1 ELSE 0 END) as unread_count
                      FROM chat_messages 
                      WHERE sender_type = 'customer'
                      GROUP BY session_id, sender_email
                      ORDER BY last_message_time DESC
                      LIMIT 50";
        } else {
            // Customer sees their own messages and admin responses
            $query = "SELECT * FROM chat_messages 
                      WHERE session_id = :session_id OR (sender_type = 'admin' AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY))
                      ORDER BY created_at ASC
                      LIMIT 100";
        }
        
        $stmt = $pdo->prepare($query);
        if ($sender_type !== 'admin') {
            $stmt->execute([':session_id' => $session_id]);
        } else {
            $stmt->execute();
        }
        
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'status' => 'success',
            'data' => $messages,
            'session_id' => $session_id
        ]);
        
    } else {
        throw new Exception('Invalid request method');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>
