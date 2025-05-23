<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch user details
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch user's voting history
$stmt = $conn->prepare("SELECT p.*, v.created_at as voted_at, o.option_text as voted_option 
                       FROM votes v 
                       JOIN polls p ON v.poll_id = p.id 
                       JOIN poll_options o ON v.option_id = o.id 
                       WHERE v.user_id = ? 
                       ORDER BY v.created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$voting_history = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch user's comments
$stmt = $conn->prepare("SELECT c.*, p.title as poll_title 
                       FROM comments c 
                       JOIN polls p ON c.poll_id = p.id 
                       WHERE c.user_id = ? 
                       ORDER BY c.created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch active polls user hasn't voted on
$stmt = $conn->prepare("SELECT p.* 
                       FROM polls p 
                       WHERE p.status = 'active' 
                       AND p.id NOT IN (
                           SELECT poll_id FROM votes WHERE user_id = ?
                       )
                       ORDER BY p.created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$available_polls = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - National Voting Platform</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .navbar {
            box-shadow: 0 2px 4px rgba(0,0,0,.1);
        }
        .navbar-brand {
            font-weight: 600;
            font-size: 1.5rem;
        }
        .nav-link {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            transition: color 0.3s ease;
        }
        .nav-link:hover {
            color: #fff !important;
            background: rgba(255,255,255,0.1);
            border-radius: 4px;
        }
        .navbar-toggler {
            border: none;
            padding: 0.5rem;
        }
        .navbar-toggler:focus {
            box-shadow: none;
        }
        @media (max-width: 991px) {
            .navbar-collapse {
                padding: 1rem 0;
            }
            .nav-link {
                padding: 0.75rem 1rem;
            }
        }
    </style>
</head>
<body>    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="index.php">
                <i class="material-icons me-2">how_to_vote</i>
                National Voting Platform
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if($_SESSION['user_role'] === 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="admin/dashboard.php">
                                <i class="material-icons">admin_panel_settings</i>
                                <span>Admin Dashboard</span>
                            </a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center" href="dashboard.php">
                            <i class="material-icons">dashboard</i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center" href="profile.php">
                            <i class="material-icons">person</i>
                            <span>Profile</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center" href="logout.php">
                            <i class="material-icons">logout</i>
                            <span>Logout</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-4">
                <!-- User Profile Card -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Welcome, <?php echo htmlspecialchars($user['name']); ?></h5>
                        <p class="card-text">Email: <?php echo htmlspecialchars($user['email']); ?></p>
                        <p class="card-text">Member since: <?php echo date('M d, Y', strtotime($user['created_at'])); ?></p>
                        <a href="profile.php" class="btn btn-primary">Edit Profile</a>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Your Activity</h5>
                        <p class="card-text">Total Votes: <?php echo count($voting_history); ?></p>
                        <p class="card-text">Total Comments: <?php echo count($comments); ?></p>
                        <p class="card-text">Available Polls: <?php echo count($available_polls); ?></p>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <!-- Available Polls -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Available Polls</h5>
                        <?php if (empty($available_polls)): ?>
                            <p class="text-muted">No new polls available at the moment.</p>
                        <?php else: ?>
                            <?php foreach ($available_polls as $poll): ?>
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <h6 class="card-title"><?php echo htmlspecialchars($poll['title']); ?></h6>
                                        <p class="card-text"><?php echo htmlspecialchars($poll['description']); ?></p>
                                        <a href="vote.php?id=<?php echo $poll['id']; ?>" class="btn btn-primary btn-sm">Vote Now</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Voting History -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Your Voting History</h5>
                        <?php if (empty($voting_history)): ?>
                            <p class="text-muted">You haven't voted on any polls yet.</p>
                        <?php else: ?>
                            <?php foreach ($voting_history as $vote): ?>
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <h6 class="card-title"><?php echo htmlspecialchars($vote['title']); ?></h6>
                                        <p class="card-text">Your vote: <?php echo htmlspecialchars($vote['voted_option']); ?></p>
                                        <small class="text-muted">Voted on: <?php echo date('M d, Y', strtotime($vote['voted_at'])); ?></small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Recent Comments -->
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Your Recent Comments</h5>
                        <?php if (empty($comments)): ?>
                            <p class="text-muted">You haven't made any comments yet.</p>
                        <?php else: ?>
                            <?php foreach ($comments as $comment): ?>
                                <div class="card mb-3" id="comment-<?php echo $comment['id']; ?>">
                                    <div class="card-body">
                                        <h6 class="card-title"><?php echo htmlspecialchars($comment['poll_title']); ?></h6>
                                        <p class="card-text comment-text"><?php echo nl2br(htmlspecialchars($comment['comment_text'])); ?></p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-muted">Posted on: <?php echo date('M d, Y H:i', strtotime($comment['created_at'])); ?></small>
                                            <div class="btn-group">
                                                <button class="btn btn-sm btn-outline-primary edit-comment" data-comment-id="<?php echo $comment['id']; ?>">Edit</button>
                                                <button class="btn btn-sm btn-outline-danger delete-comment" data-comment-id="<?php echo $comment['id']; ?>">Delete</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
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
    <script>
        // Add CSRF token to all fetch requests if you're using CSRF protection
        window.addEventListener('DOMContentLoaded', (event) => {
            console.log('DOM loaded - comment management initialized');
        });
    </script>
</body>
</html>