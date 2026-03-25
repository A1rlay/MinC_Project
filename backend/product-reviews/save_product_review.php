<?php

header('Content-Type: application/json');

require_once '../../database/connect_database.php';
require_once '../auth.php';
require_once 'review_helpers.php';

$validation = validateSession(false);
if (!$validation['valid']) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Please login to submit a review.',
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method.',
    ]);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!is_array($input)) {
        throw new Exception('Invalid request payload.');
    }

    $productId = (int)($input['product_id'] ?? 0);
    $rating = (int)($input['rating'] ?? 0);
    $reviewTitle = trim((string)($input['review_title'] ?? ''));
    $reviewText = trim((string)($input['review_text'] ?? ''));
    $userId = (int)($_SESSION['user_id'] ?? 0);
    $userEmail = trim((string)($_SESSION['email'] ?? ''));

    if ($userId <= 0) {
        throw new Exception('Please login to submit a review.');
    }

    if ($productId <= 0) {
        throw new Exception('Product ID is required.');
    }

    if ($rating < 1 || $rating > 5) {
        throw new Exception('Please select a star rating from 1 to 5.');
    }

    if ($reviewTitle !== '' && strlen($reviewTitle) > 255) {
        throw new Exception('Review headline must be 255 characters or fewer.');
    }

    if (strlen($reviewText) < 20) {
        throw new Exception('Please write at least 20 characters for your review.');
    }

    if (strlen($reviewText) > 5000) {
        throw new Exception('Review text must be 5000 characters or fewer.');
    }

    ensureProductReviewsTable($pdo);

    $productStmt = $pdo->prepare("
        SELECT product_id
        FROM products
        WHERE product_id = :product_id
          AND status = 'active'
        LIMIT 1
    ");
    $productStmt->execute([':product_id' => $productId]);
    if (!$productStmt->fetch(PDO::FETCH_ASSOC)) {
        throw new Exception('Product not found.');
    }

    $userStmt = $pdo->prepare("
        SELECT user_id, email
        FROM users
        WHERE user_id = :user_id
          AND user_status = 'active'
        LIMIT 1
    ");
    $userStmt->execute([':user_id' => $userId]);
    $user = $userStmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        throw new Exception('User account not found.');
    }

    $verifiedPurchase = hasVerifiedPurchaseForProduct(
        $pdo,
        $userId,
        $productId,
        $userEmail !== '' ? $userEmail : (string)($user['email'] ?? '')
    );

    $stmt = $pdo->prepare("
        INSERT INTO product_reviews (
            product_id,
            user_id,
            rating,
            review_title,
            review_text,
            created_at,
            updated_at
        ) VALUES (
            :product_id,
            :user_id,
            :rating,
            :review_title,
            :review_text,
            NOW(),
            NOW()
        )
        ON DUPLICATE KEY UPDATE
            rating = VALUES(rating),
            review_title = VALUES(review_title),
            review_text = VALUES(review_text),
            updated_at = NOW()
    ");
    $stmt->execute([
        ':product_id' => $productId,
        ':user_id' => $userId,
        ':rating' => $rating,
        ':review_title' => $reviewTitle !== '' ? $reviewTitle : null,
        ':review_text' => $reviewText,
    ]);

    $payload = getProductReviewsPayload($pdo, $productId, $userId);

    echo json_encode([
        'success' => true,
        'message' => 'Your review has been saved.',
        'summary' => $payload['summary'],
        'reviews' => $payload['reviews'],
        'current_user_review' => $payload['current_user_review'],
        'is_verified_purchase' => $verifiedPurchase,
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
    ]);
}
