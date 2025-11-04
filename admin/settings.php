<?php
session_start();
include '../config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit();
}

$message = '';

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_profile':
                $admin_id = $_SESSION['admin_id'];
                $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
                $email = mysqli_real_escape_string($conn, $_POST['email']);
                
                $query = "UPDATE admin SET full_name = '$full_name', email = '$email' WHERE id = $admin_id";
                if (mysqli_query($conn, $query)) {
                    $_SESSION['admin_name'] = $full_name;
                    $message = "Profile updated successfully!";
                } else {
                    $message = "Error updating profile: " . mysqli_error($conn);
                }
                break;
                
            case 'change_password':
                $admin_id = $_SESSION['admin_id'];
                $current_password = $_POST['current_password'];
                $new_password = $_POST['new_password'];
                $confirm_password = $_POST['confirm_password'];
                
                // Get current admin data
                $admin_result = mysqli_query($conn, "SELECT * FROM admin WHERE id = $admin_id");
                $admin = mysqli_fetch_assoc($admin_result);
                
                if ($current_password === $admin['password']) {
                    if ($new_password === $confirm_password) {
                        $query = "UPDATE admin SET password = '$new_password' WHERE id = $admin_id";
                        mysqli_query($conn, $query);
                        $message = "Password changed successfully!";
                    } else {
                        $message = "New passwords do not match!";
                    }
                } else {
                    $message = "Current password is incorrect!";
                }
                break;
        }
    }
}

// Get current admin data
$admin_result = mysqli_query($conn, "SELECT * FROM admin WHERE id = " . $_SESSION['admin_id']);
$admin = mysqli_fetch_assoc($admin_result);

// Get system statistics
$stats = [];
$stats['total_users'] = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users1"))['count'];
$stats['total_vehicles'] = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM vehicles"))['count'];
$stats['total_bookings'] = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM bookings1"))['count'];
$stats['total_revenue'] = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(total_amount) as total FROM bookings1 WHERE status IN ('confirmed', 'completed')"))['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, #2c3e50 0%, #34495e 100%);
        }
        .sidebar .nav-link {
            color: #ecf0f1;
            padding: 12px 20px;
            border-radius: 8px;
            margin: 2px 10px;
            transition: all 0.3s;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0">
                <div class="sidebar">
                    <div class="p-3 text-center border-bottom border-secondary">
                        <h5 class="text-white mb-0">
                            <i class="fas fa-car"></i> Admin Panel
                        </h5>
                    </div>
                    <nav class="nav flex-column py-3">
                        <a class="nav-link" href="dashboard.php">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                        <a class="nav-link" href="users.php">
                            <i class="fas fa-users"></i> Users
                        </a>
                        <a class="nav-link" href="vehicles.php">
                            <i class="fas fa-car"></i> Vehicles
                        </a>
                        <a class="nav-link" href="bookings.php">
                            <i class="fas fa-calendar-check"></i> Bookings
                        </a>
                        <a class="nav-link" href="brands.php">
                            <i class="fas fa-tags"></i> Brands
                        </a>
                        <a class="nav-link" href="testimonials.php">
                            <i class="fas fa-star"></i> Testimonials
                        </a>
                        <a class="nav-link" href="contact_queries.php">
                            <i class="fas fa-envelope"></i> Contact Queries
                        </a>
                        <a class="nav-link active" href="settings.php">
                            <i class="fas fa-cog"></i> Settings
                        </a>
                        <hr class="border-secondary">
                        <a class="nav-link" href="logout.php">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center py-3 px-4 border-bottom">
                    <h4 class="mb-0">Settings</h4>
                    <div class="d-flex align-items-center">
                        <span class="me-3">Welcome, <?php echo $_SESSION['admin_name']; ?></span>
                        <img src="https://via.placeholder.com/40" class="rounded-circle" alt="Admin">
                    </div>
                </div>

                <!-- Content -->
                <div class="p-4">
                    <?php if ($message): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php echo $message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <div class="row">
                        <!-- Profile Settings -->
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-user"></i> Profile Settings</h5>
                                </div>
                                <div class="card-body">
                                    <form method="POST">
                                        <input type="hidden" name="action" value="update_profile">
                                        <div class="mb-3">
                                            <label class="form-label">Full Name</label>
                                            <input type="text" class="form-control" name="full_name" value="<?php echo htmlspecialchars($admin['full_name']); ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Email</label>
                                            <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($admin['email']); ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Username</label>
                                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($admin['username']); ?>" readonly>
                                            <small class="text-muted">Username cannot be changed</small>
                                        </div>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save"></i> Update Profile
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Change Password -->
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-lock"></i> Change Password</h5>
                                </div>
                                <div class="card-body">
                                    <form method="POST">
                                        <input type="hidden" name="action" value="change_password">
                                        <div class="mb-3">
                                            <label class="form-label">Current Password</label>
                                            <input type="password" class="form-control" name="current_password" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">New Password</label>
                                            <input type="password" class="form-control" name="new_password" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Confirm New Password</label>
                                            <input type="password" class="form-control" name="confirm_password" required>
                                        </div>
                                        <button type="submit" class="btn btn-warning">
                                            <i class="fas fa-key"></i> Change Password
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- System Statistics -->
                        <div class="col-12 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-chart-bar"></i> System Statistics</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3 text-center">
                                            <div class="border rounded p-3">
                                                <h3 class="text-primary"><?php echo $stats['total_users']; ?></h3>
                                                <p class="mb-0">Total Users</p>
                                            </div>
                                        </div>
                                        <div class="col-md-3 text-center">
                                            <div class="border rounded p-3">
                                                <h3 class="text-success"><?php echo $stats['total_vehicles']; ?></h3>
                                                <p class="mb-0">Total Vehicles</p>
                                            </div>
                                        </div>
                                        <div class="col-md-3 text-center">
                                            <div class="border rounded p-3">
                                                <h3 class="text-warning"><?php echo $stats['total_bookings']; ?></h3>
                                                <p class="mb-0">Total Bookings</p>
                                            </div>
                                        </div>
                                        <div class="col-md-3 text-center">
                                            <div class="border rounded p-3">
                                                <h3 class="text-info">₹<?php echo number_format($stats['total_revenue']); ?></h3>
                                                <p class="mb-0">Total Revenue</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- System Information -->
                       
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>