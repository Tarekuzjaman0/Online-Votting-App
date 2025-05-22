<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get poll ID from URL
$poll_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$poll_id) {
    header("Location: index.php");
    exit();
}

// Fetch poll details
$stmt = $conn->prepare("SELECT p.*, u.name as creator_name 
                       FROM polls p 
                       LEFT JOIN users u ON p.created_by = u.id 
                       WHERE p.id = ?");
$stmt->execute([$poll_id]);
$poll = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$poll) {
    header("Location: index.php");
    exit();
}

// Handle vote submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'vote') {
        $option_id = filter_input(INPUT_POST, 'option_id', FILTER_VALIDATE_INT);
        
        try {
            // Check if user has already voted
            $stmt = $conn->prepare("SELECT id FROM votes WHERE poll_id = ? AND user_id = ?");
            $stmt->execute([$poll_id, $_SESSION['user_id']]);
            if ($stmt->fetch()) {
                $error = "You have already voted on this poll.";
            } else {
                // Record vote
                $stmt = $conn->prepare("INSERT INTO votes (poll_id, option_id, user_id) VALUES (?, ?, ?)");
                $stmt->execute([$poll_id, $option_id, $_SESSION['user_id']]);
                $success = "Your vote has been recorded!";
            }
        } catch (Exception $e) {
            $error = "Error recording vote: " . $e->getMessage();
        }
    }
    
    // Handle comment submission
    if ($_POST['action'] === 'comment') {
        $comment_text = filter_input(INPUT_POST, 'comment_text', FILTER_SANITIZE_STRING);
        
        try {
            $stmt = $conn->prepare("INSERT INTO comments (poll_id, user_id, comment_text) VALUES (?, ?, ?)");
            $stmt->execute([$poll_id, $_SESSION['user_id'], $comment_text]);
            $success = "Comment added successfully!";
        } catch (Exception $e) {
            $error = "Error adding comment: " . $e->getMessage();
        }
    }
}

// Fetch poll options with vote counts
$stmt = $conn->prepare("SELECT o.*, COUNT(v.id) as vote_count 
                       FROM poll_options o 
                       LEFT JOIN votes v ON o.id = v.option_id 
                       WHERE o.poll_id = ? 
                       GROUP BY o.id");
$stmt->execute([$poll_id]);
$options = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Check if user has voted
$stmt = $conn->prepare("SELECT option_id FROM votes WHERE poll_id = ? AND user_id = ?");
$stmt->execute([$poll_id, $_SESSION['user_id']]);
$user_vote = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch comments
$stmt = $conn->prepare("SELECT c.*, u.name as user_name 
                       FROM comments c 
                       JOIN users u ON c.user_id = u.id 
                       WHERE c.poll_id = ? 
                       ORDER BY c.created_at DESC");
$stmt->execute([$poll_id]);
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate total votes
$total_votes = array_sum(array_column($options, 'vote_count'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($poll['title']); ?> - National Voting Platform</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">National Voting Platform</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-body">
                        <h2 class="card-title"><?php echo htmlspecialchars($poll['title']); ?></h2>
                        <p class="text-muted">Created by <?php echo htmlspecialchars($poll['creator_name']); ?> on <?php echo date('M d, Y', strtotime($poll['created_at'])); ?></p>
                        <p class="card-text"><?php echo nl2br(htmlspecialchars($poll['description'])); ?></p>
                        
                        <?php if (isset($success)): ?>
                            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                        <?php endif; ?>
                        
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>
                        
                        <?php if ($poll['status'] === 'active' && !$user_vote): ?>
                            <form method="POST" action="">
                                <input type="hidden" name="action" value="vote">
                                <div class="mb-3">
                                    <?php foreach ($options as $option): ?>
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="radio" name="option_id" id="option<?php echo $option['id']; ?>" value="<?php echo $option['id']; ?>" required>
                                            <label class="form-check-label" for="option<?php echo $option['id']; ?>">
                                                <?php echo htmlspecialchars($option['option_text']); ?>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <button type="submit" class="btn btn-primary">Submit Vote</button>
                            </form>
                        <?php else: ?>
                            <div class="poll-results">
                                <h4>Results</h4>
                                <?php foreach ($options as $option): ?>
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between">
                                            <span><?php echo htmlspecialchars($option['option_text']); ?></span>
                                            <span><?php echo $option['vote_count']; ?> votes (<?php echo $total_votes > 0 ? round(($option['vote_count'] / $total_votes) * 100) : 0; ?>%)</span>
                                        </div>
                                        <div class="result-bar">
                                            <div class="result-fill" style="width: <?php echo $total_votes > 0 ? ($option['vote_count'] / $total_votes) * 100 : 0; ?>%"></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                <p class="text-muted">Total votes: <?php echo $total_votes; ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Comments Section -->
                <div class="card">
                    <div class="card-body">
                        <h4>Comments</h4>
                        
                        <?php if ($poll['status'] === 'active'): ?>
                            <form method="POST" action="" class="mb-4">
                                <input type="hidden" name="action" value="comment">
                                <div class="mb-3">
                                    <textarea class="form-control" name="comment_text" rows="3" required placeholder="Share your thoughts..."></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary">Post Comment</button>
                            </form>
                        <?php endif; ?>
                        
                        <div class="comments-list">
                            <?php foreach ($comments as $comment): ?>
                                <div class="comment">
                                    <div class="d-flex justify-content-between">
                                        <strong><?php echo htmlspecialchars($comment['user_name']); ?></strong>
                                        <small class="text-muted"><?php echo date('M d, Y H:i', strtotime($comment['created_at'])); ?></small>
                                    </div>
                                    <p class="mb-0"><?php echo nl2br(htmlspecialchars($comment['comment_text'])); ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Poll Status</h5>
                        <p class="card-text">
                            Status: <span class="badge bg-<?php echo $poll['status'] === 'active' ? 'success' : ($poll['status'] === 'closed' ? 'danger' : 'warning'); ?>">
                                <?php echo ucfirst($poll['status']); ?>
                            </span>
                        </p>
                        <p class="card-text">Total Votes: <?php echo $total_votes; ?></p>
                        <p class="card-text">Total Comments: <?php echo count($comments); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="bg-light mt-5 py-3">
        <div class="container text-center">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> National Voting Platform. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html> 