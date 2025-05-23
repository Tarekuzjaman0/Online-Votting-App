<?php
session_start();
require_once '../config/database.php';
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$action = $_POST['action'] ?? '';
$comment_id = filter_input(INPUT_POST, 'comment_id', FILTER_VALIDATE_INT);

if (!$comment_id) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid comment ID']);
    exit();
}

try {
    // Check if the comment belongs to the user
    $stmt = $conn->prepare("SELECT * FROM comments WHERE id = ? AND user_id = ?");
    $stmt->execute([$comment_id, $_SESSION['user_id']]);
    $comment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$comment) {
        http_response_code(403);
        echo json_encode(['error' => 'Not authorized to modify this comment']);
        exit();
    }

    switch ($action) {
        case 'edit':
            $new_text = trim($_POST['comment_text'] ?? '');
            if (empty($new_text)) {
                http_response_code(400);
                echo json_encode(['error' => 'Comment text cannot be empty']);
                exit();
            }

            $stmt = $conn->prepare("UPDATE comments SET comment_text = ? WHERE id = ?");
            $stmt->execute([$new_text, $comment_id]);
            echo json_encode(['success' => true, 'message' => 'Comment updated successfully']);
            break;

        case 'delete':
            $stmt = $conn->prepare("DELETE FROM comments WHERE id = ?");
            $stmt->execute([$comment_id]);
            echo json_encode(['success' => true, 'message' => 'Comment deleted successfully']);
            break;

        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}