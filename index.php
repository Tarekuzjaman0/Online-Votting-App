<?php
session_start();
require_once 'config/database.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>National Voting Platform</title>
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
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php">Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">Logout</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php">Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-8">
                <h2>Active Polls</h2>
                <div class="polls-container">
                    <?php
                    $stmt = $conn->query("SELECT * FROM polls WHERE status = 'active' ORDER BY created_at DESC");
                    while($poll = $stmt->fetch(PDO::FETCH_ASSOC)):
                    ?>
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($poll['title']); ?></h5>
                            <p class="card-text"><?php echo htmlspecialchars($poll['description']); ?></p>
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">Created: <?php echo date('M d, Y', strtotime($poll['created_at'])); ?></small>
                                <a href="vote.php?id=<?php echo $poll['id']; ?>" class="btn btn-primary">Vote Now</a>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">About the Platform</h5>
                        <p class="card-text">                        
                            <p class="card-text">সকলের মতামত প্রকাশের স্বাধীনতা গণতন্ত্রের মূল ভিত্তি। জাতীয় ভোটিং প্ল্যাটফর্মে আপনি নিরাপদে মতামত প্রকাশ করে গুরুত্বপূর্ণ বিষয়ে অংশ নিতে পারেন, যা নীতিনির্ধারণে ভূমিকা রাখে।</p>
                        <?php if(!isset($_SESSION['user_id'])): ?>
                            <a href="register.php" class="btn btn-success">Join Now</a>
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
</body>
</html> 