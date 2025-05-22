<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Get poll ID from URL
$poll_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$poll_id) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid poll ID']);
    exit();
}

try {
    // Fetch comments with user names
    $stmt = $conn->prepare("SELECT c.*, u.name as user_name 
                           FROM comments c 
                           JOIN users u ON c.user_id = u.id 
                           WHERE c.poll_id = ? 
                           ORDER BY c.created_at DESC");
    $stmt->execute([$poll_id]);
    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Return comments
    echo json_encode([
        'comments' => $comments
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error']);
} 