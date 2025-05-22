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
    // Fetch poll options with vote counts
    $stmt = $conn->prepare("SELECT o.*, COUNT(v.id) as vote_count 
                           FROM poll_options o 
                           LEFT JOIN votes v ON o.id = v.option_id 
                           WHERE o.poll_id = ? 
                           GROUP BY o.id");
    $stmt->execute([$poll_id]);
    $options = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate total votes
    $total_votes = array_sum(array_column($options, 'vote_count'));
    
    // Return results
    echo json_encode([
        'options' => $options,
        'total_votes' => $total_votes
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error']);
} 