<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "voting_platform";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->exec("SET NAMES utf8mb4");
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Array of bad words (you can expand this list)
$bad_words = [
    'badword1',
    'badword2',
    'offensive',
    'inappropriate',
    // Add more words as needed
];

// Function to check and delete bad comments
function checkAndDeleteBadComment($conn, $comment_text, $comment_id) {
    global $bad_words;
    
    // Convert comment to lowercase for comparison
    $comment_text = strtolower($comment_text);
    
    // Check if comment contains any bad words
    foreach ($bad_words as $word) {
        if (strpos($comment_text, strtolower($word)) !== false) {
            try {
                // Prepare and execute delete query
                $stmt = $conn->prepare("DELETE FROM comments WHERE id = :id");
                $stmt->bindParam(':id', $comment_id, PDO::PARAM_INT);
                $stmt->execute();
                
                return true; // Comment deleted
            } catch(PDOException $e) {
                error_log("Error deleting comment: " . $e->getMessage());
                return false;
            }
        }
    }
    return false; // No bad words found
}

// Example usage: Check all new comments
try {
    // Fetch recent comments (modify WHERE clause as needed)
    $stmt = $conn->query("SELECT id, comment_text FROM comments ORDER BY created_at DESC");
    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($comments as $comment) {
        if (checkAndDeleteBadComment($conn, $comment['comment_text'], $comment['id'])) {
            echo "Comment ID {$comment['id']} deleted due to inappropriate content.\n";
        }
    }
} catch(PDOException $e) {
    error_log("Error fetching comments: " . $e->getMessage());
}

// Close connection
$conn = null;
?>