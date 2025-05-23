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

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    $errors = [];
    
    // Validate name
    if (empty($name) || strlen($name) < 2) {
        $errors[] = "Name must be at least 2 characters long";
    }
    
    // If password change is requested
    if (!empty($new_password)) {
        // Verify current password
        if (!password_verify($current_password, $user['password'])) {
            $errors[] = "Current password is incorrect";
        }
        
        // Validate new password
        if (strlen($new_password) < 8) {
            $errors[] = "New password must be at least 8 characters long";
        }
        if (!preg_match("/[A-Z]/", $new_password)) {
            $errors[] = "New password must contain at least one uppercase letter";
        }
        if (!preg_match("/[a-z]/", $new_password)) {
            $errors[] = "New password must contain at least one lowercase letter";
        }
        if (!preg_match("/[0-9]/", $new_password)) {
            $errors[] = "New password must contain at least one number";
        }
        
        // Confirm new password
        if ($new_password !== $confirm_password) {
            $errors[] = "New passwords do not match";
        }
    }
    
    if (empty($errors)) {
        try {
            if (!empty($new_password)) {
                // Update name and password
                $stmt = $conn->prepare("UPDATE users SET name = ?, password = ? WHERE id = ?");
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt->execute([$name, $hashed_password, $_SESSION['user_id']]);
            } else {
                // Update only name
                $stmt = $conn->prepare("UPDATE users SET name = ? WHERE id = ?");
                $stmt->execute([$name, $_SESSION['user_id']]);
            }
            
            $success = "Profile updated successfully!";
            
            // Refresh user data
            $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $errors[] = "Error updating profile: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - National Voting Platform</title>
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
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center" href="dashboard.php">
                            <i class="material-icons">dashboard</i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center active" href="profile.php">
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
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <h3 class="card-title text-center mb-4">Edit Profile</h3>
                        
                        <?php if (isset($success)): ?>
                            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                        <?php endif; ?>
                        
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo htmlspecialchars($error); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="name" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email address</label>
                                <input type="email" class="form-control" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                                <div class="form-text">Email cannot be changed</div>
                            </div>
                            
                            <hr>
                            
                            <h5 class="mb-3">Change Password</h5>
                            <div class="mb-3">
                                <label for="current_password" class="form-label">Current Password</label>
                                <input type="password" class="form-control" id="current_password" name="current_password">
                            </div>
                            <div class="mb-3">
                                <label for="new_password" class="form-label">New Password</label>
                                <input type="password" class="form-control" id="new_password" name="new_password">
                                <div class="form-text">Leave blank to keep current password</div>
                            </div>
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Update Profile</button>
                            </div>
                        </form>
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