<?php
session_start();
include '../config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit();
}

// Get dashboard statistics
$stats = [];

// Total users
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM users1 WHERE status = 'active'");
$stats['users'] = mysqli_fetch_assoc($result)['count'];

// Total vehicles
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM vehicles WHERE status != 'inactive'");
$stats['vehicles'] = mysqli_fetch_assoc($result)['count'];

// Total bookings
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM bookings1");
$stats['bookings'] = mysqli_fetch_assoc($result)['count'];

// Pending bookings
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM bookings1 WHERE status = 'pending'");
$stats['pending_bookings'] = mysqli_fetch_assoc($result)['count'];

// Recent bookings
$recent_bookings = mysqli_query($conn, "
    SELECT b.*, u.full_name, v.model, vb.brand_name 
    FROM bookings1 b 
    JOIN users1 u ON b.user_id = u.id 
    JOIN vehicles v ON b.vehicle_id = v.id 
    JOIN vehicle_brands vb ON v.brand_id = vb.id 
    ORDER BY b.booking_date DESC 
    LIMIT 5
");

// Revenue this month
$result = mysqli_query($conn, "
    SELECT SUM(total_amount) as revenue 
    FROM bookings1 
    WHERE MONTH(booking_date) = MONTH(CURRENT_DATE()) 
    AND YEAR(booking_date) = YEAR(CURRENT_DATE())
    AND status IN ('confirmed', 'completed')
");
$stats['revenue'] = mysqli_fetch_assoc($result)['revenue'] ?? 0;

// Feedback statistics
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM feedback WHERE status = 'pending'");
if ($result) {
    $feedback_stats = mysqli_fetch_assoc($result);
    $stats['pending_feedback'] = $feedback_stats['count'] ?? 0;
} else {
    $stats['pending_feedback'] = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Car Rental System</title>
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
        .stat-card {
            border-radius: 15px;
            border: none;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
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
                        <a class="nav-link active" href="dashboard.php">
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
                        <a class="nav-link" href="feedback.php">
                            <i class="fas fa-comments"></i> Feedback Management
                        </a>
                        <a class="nav-link" href="settings.php">
                            <i class="fas fa-cog"></i> Settings
                        </a>
                        <hr class="border-secondary">
                        <a class="nav-link" href="../logout.php">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center py-3 px-4 border-bottom">
                    <h4 class="mb-0">Dashboard</h4>
                    <div class="d-flex align-items-center">
                        <span class="me-3">Welcome, <?php echo $_SESSION['admin_name']; ?></span>
                        <img src="https://via.placeholder.com/40" class="rounded-circle" alt="Admin">
                    </div>
                </div>

                <!-- Dashboard Content -->
                <div class="p-4">
                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3 mb-3">
                            <div class="card stat-card bg-primary text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h3 class="mb-0"><?php echo $stats['users']; ?></h3>
                                            <p class="mb-0">Total Users</p>
                                        </div>
                                        <i class="fas fa-users fa-2x opacity-75"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card stat-card bg-success text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h3 class="mb-0"><?php echo $stats['vehicles']; ?></h3>
                                            <p class="mb-0">Total Vehicles</p>
                                        </div>
                                        <i class="fas fa-car fa-2x opacity-75"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card stat-card bg-warning text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h3 class="mb-0"><?php echo $stats['bookings']; ?></h3>
                                            <p class="mb-0">Total Bookings</p>
                                        </div>
                                        <i class="fas fa-calendar-check fa-2x opacity-75"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card stat-card bg-info text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h3 class="mb-0">₹<?php echo number_format($stats['revenue']); ?></h3>
                                            <p class="mb-0">Monthly Revenue</p>
                                        </div>
                                        <i class="fas fa-rupee-sign fa-2x opacity-75"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div class="row">
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Recent Bookings</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Customer</th>
                                                    <th>Vehicle</th>
                                                    <th>Date</th>
                                                    <th>Amount</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php while ($booking = mysqli_fetch_assoc($recent_bookings)): ?>
                                                <tr>
                                                    <td><?php echo $booking['full_name']; ?></td>
                                                    <td><?php echo $booking['brand_name'] . ' ' . $booking['model']; ?></td>
                                                    <td><?php echo date('M d, Y', strtotime($booking['booking_date'])); ?></td>
                                                    <td>₹<?php echo number_format($booking['total_amount']); ?></td>
                                                    <td>
                                                        <span class="badge bg-<?php 
                                                            echo $booking['status'] == 'confirmed' ? 'success' : 
                                                                ($booking['status'] == 'pending' ? 'warning' : 'secondary'); 
                                                        ?>">
                                                            <?php echo ucfirst($booking['status']); ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                                <?php endwhile; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Quick Actions</h5>
                                </div>
                                <div class="card-body">
                                    <div class="d-grid gap-2">
                                        <a href="vehicles.php?action=add" class="btn btn-primary">
                                            <i class="fas fa-plus"></i> Add New Vehicle
                                        </a>
                                        <a href="bookings.php?status=pending" class="btn btn-warning">
                                            <i class="fas fa-clock"></i> Pending Bookings (<?php echo $stats['pending_bookings']; ?>)
                                        </a>
                                        <a href="users.php" class="btn btn-info">
                                            <i class="fas fa-users"></i> Manage Users
                                        </a>
                                        <a href="contact_queries.php" class="btn btn-secondary">
                                            <i class="fas fa-envelope"></i> View Messages
                                        </a>
                                        <a href="feedback.php" class="btn btn-success">
                                            <i class="fas fa-comments"></i> Feedback (<?php echo $stats['pending_feedback']; ?>)
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>