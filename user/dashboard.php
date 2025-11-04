<?php
session_start();
include '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location:../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Get user's booking statistics
$stats = array();

// Total bookings
$total_bookings_query = "SELECT COUNT(*) as count FROM bookings1 WHERE user_id = $user_id";
$total_bookings_result = mysqli_query($conn, $total_bookings_query);
$stats['total_bookings'] = mysqli_fetch_assoc($total_bookings_result)['count'];

// Active bookings
$active_bookings_query = "SELECT COUNT(*) as count FROM bookings1 WHERE user_id = $user_id AND status IN ('pending', 'confirmed')";
$active_bookings_result = mysqli_query($conn, $active_bookings_query);
$stats['active_bookings'] = mysqli_fetch_assoc($active_bookings_result)['count'];

// Completed bookings
$completed_bookings_query = "SELECT COUNT(*) as count FROM bookings1 WHERE user_id = $user_id AND status = 'completed'";
$completed_bookings_result = mysqli_query($conn, $completed_bookings_query);
$stats['completed_bookings'] = mysqli_fetch_assoc($completed_bookings_result)['count'];

// Total spent
$total_spent_query = "SELECT SUM(total_amount) as total FROM bookings1 WHERE user_id = $user_id AND status = 'completed'";
$total_spent_result = mysqli_query($conn, $total_spent_query);
$stats['total_spent'] = mysqli_fetch_assoc($total_spent_result)['total'] ?: 0;

// Recent bookings
$recent_bookings_query = "SELECT b.*, v.model, vb.brand_name, v.image 
                         FROM bookings1 b 
                         JOIN vehicles v ON b.vehicle_id = v.id 
                         JOIN vehicle_brands vb ON v.brand_id = vb.id 
                         WHERE b.user_id = $user_id 
                         ORDER BY b.booking_date DESC LIMIT 5";
$recent_bookings_result = mysqli_query($conn, $recent_bookings_query);

// Get user details
$user_query = "SELECT * FROM users1 WHERE id = $user_id";
$user_result = mysqli_query($conn, $user_query);
$user = mysqli_fetch_assoc($user_result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - Car Rental System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-car"></i> Trip Wheels
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">
                            <i class="fas fa-home"></i> Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="vehicles.php">
                            <i class="fas fa-car"></i> Vehicles
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="all_feedback.php">
                            <i class="fas fa-star"></i> Reviews
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="about.php">
                            <i class="fas fa-info-circle"></i> About
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.php">
                            <i class="fas fa-envelope"></i> Contact
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user"></i> <?php echo $_SESSION['user_name']; ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                            <li><a class="dropdown-item" href="bookings.php">My Bookings</a></li>
                            <li><a class="dropdown-item" href="feedback.php">Feedback</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <!-- Welcome Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h4 class="card-title">
                            <i class="fas fa-user-circle"></i> Welcome back, <?php echo $_SESSION['user_name']; ?>!
                        </h4>
                        <p class="card-text">Manage your bookings and profile from your personal dashboard.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="dashboard-card">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="card-icon">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                        </div>
                        <div class="col">
                            <h3 class="mb-0"><?php echo $stats['total_bookings']; ?></h3>
                            <p class="mb-0">Total Bookings</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="dashboard-card">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="card-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                        </div>
                        <div class="col">
                            <h3 class="mb-0"><?php echo $stats['active_bookings']; ?></h3>
                            <p class="mb-0">Active Bookings</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="dashboard-card">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="card-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                        </div>
                        <div class="col">
                            <h3 class="mb-0"><?php echo $stats['completed_bookings']; ?></h3>
                            <p class="mb-0">Completed</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="dashboard-card">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="card-icon">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                        </div>
                        <div class="col">
                            <h3 class="mb-0">₹<?php echo number_format($stats['total_spent']); ?></h3>
                            <p class="mb-0">Total Spent</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-bolt"></i> Quick Actions
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <a href="vehicles.php" class="btn btn-primary w-100">
                                    <i class="fas fa-car"></i> Rent a Vehicle
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="bookings.php" class="btn btn-success w-100">
                                    <i class="fas fa-list"></i> View Bookings
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="profile.php" class="btn btn-warning w-100">
                                    <i class="fas fa-user-edit"></i> Edit Profile
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="feedback.php" class="btn btn-info w-100">
                                    <i class="fas fa-comments"></i> Give Feedback
                                </a>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <a href="all_feedback.php" class="btn btn-outline-primary w-100">
                                    <i class="fas fa-star"></i> View Customer Reviews
                                </a>
                            </div>
                            <div class="col-md-6 mb-3">
                                <a href="contact.php" class="btn btn-outline-secondary w-100">
                                    <i class="fas fa-envelope"></i> Contact Support
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Bookings -->
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-history"></i> Recent Bookings
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (mysqli_num_rows($recent_bookings_result) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Vehicle</th>
                                            <th>Pickup Date</th>
                                            <th>Return Date</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($booking = mysqli_fetch_assoc($recent_bookings_result)): ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <?php if ($booking['image']): ?>
                                                            <img src="../assets/img/<?php echo $booking['image']; ?>" 
                                                                 alt="<?php echo $booking['brand_name'] . ' ' . $booking['model']; ?>" 
                                                                 class="me-2" style="width: 40px; height: 30px; object-fit: cover;">
                                                        <?php endif; ?>
                                                        <div>
                                                            <strong><?php echo $booking['brand_name'] . ' ' . $booking['model']; ?></strong>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td><?php echo date('M d, Y', strtotime($booking['pickup_date'])); ?></td>
                                                <td><?php echo date('M d, Y', strtotime($booking['return_date'])); ?></td>
                                                <td>₹<?php echo number_format($booking['total_amount']); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php 
                                                        echo $booking['status'] == 'pending' ? 'warning' : 
                                                            ($booking['status'] == 'confirmed' ? 'success' : 
                                                            ($booking['status'] == 'cancelled' ? 'danger' : 'secondary')); 
                                                    ?>">
                                                        <?php echo ucfirst($booking['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="booking_details.php?id=<?php echo $booking['id']; ?>" 
                                                       class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-eye"></i> View
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="text-center">
                                <a href="bookings.php" class="btn btn-outline-primary">
                                    View All Bookings
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No bookings yet</h5>
                                <p class="text-muted">Start by renting your first vehicle!</p>
                                <a href="vehicles.php" class="btn btn-primary">
                                    <i class="fas fa-car"></i> Browse Vehicles
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../assets/js/script.js"></script>
</body>
</html>


