<?php
session_start();
include '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location:login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

// Handle booking cancellation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cancel_booking'])) {
    $booking_id = (int)$_POST['booking_id'];
    
    // Verify the booking belongs to the current user
    $verify_query = "SELECT * FROM bookings1 WHERE id = $booking_id AND user_id = $user_id";
    $verify_result = mysqli_query($conn, $verify_query);
    
    if (mysqli_num_rows($verify_result) == 1) {
        $booking = mysqli_fetch_assoc($verify_result);
        
        // Only allow cancellation of pending or confirmed bookings
        if (in_array($booking['status'], ['pending', 'confirmed'])) {
            $update_query = "UPDATE bookings1 SET status = 'cancelled' WHERE id = $booking_id";
            if (mysqli_query($conn, $update_query)) {
                $message = 'Booking cancelled successfully!';
            } else {
                $error = 'Failed to cancel booking. Please try again.';
            }
        } else {
            $error = 'This booking cannot be cancelled.';
        }
    } else {
        $error = 'Invalid booking.';
    }
}

// Get user's bookings with vehicle details
$bookings_query = "SELECT b.*, v.model, v.image, vb.brand_name 
                  FROM bookings1 b 
                  JOIN vehicles v ON b.vehicle_id = v.id 
                  JOIN vehicle_brands vb ON v.brand_id = vb.id 
                  WHERE b.user_id = $user_id 
                  ORDER BY b.booking_date DESC";
$bookings_result = mysqli_query($conn, $bookings_query);

// Get booking statistics
$stats = array();

// Total bookings
$total_query = "SELECT COUNT(*) as count FROM bookings1 WHERE user_id = $user_id";
$total_result = mysqli_query($conn, $total_query);
$stats['total'] = mysqli_fetch_assoc($total_result)['count'];

// Active bookings
$active_query = "SELECT COUNT(*) as count FROM bookings1 WHERE user_id = $user_id AND status IN ('pending', 'confirmed')";
$active_result = mysqli_query($conn, $active_query);
$stats['active'] = mysqli_fetch_assoc($active_result)['count'];

// Completed bookings
$completed_query = "SELECT COUNT(*) as count FROM bookings1 WHERE user_id = $user_id AND status = 'completed'";
$completed_result = mysqli_query($conn, $completed_query);
$stats['completed'] = mysqli_fetch_assoc($completed_result)['count'];

// Total spent
$spent_query = "SELECT SUM(total_amount) as total FROM bookings1 WHERE user_id = $user_id AND status = 'completed'";
$spent_result = mysqli_query($conn, $spent_query);
$stats['spent'] = mysqli_fetch_assoc($spent_result)['total'] ?: 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings - Car Rental System</title>
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
                            <li><a class="dropdown-item" href="dashboard.php">Dashboard</a></li>
                            <li><a class="dropdown-item active" href="bookings.php">My Bookings</a></li>
                            <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="../logout.php">Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item active">My Bookings</li>
            </ol>
        </nav>

        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h4 class="card-title">
                            <i class="fas fa-calendar-check"></i> My Bookings
                        </h4>
                        <p class="card-text">View and manage your vehicle rental bookings</p>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle"></i> <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

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
                            <h3 class="mb-0"><?php echo $stats['total']; ?></h3>
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
                            <h3 class="mb-0"><?php echo $stats['active']; ?></h3>
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
                            <h3 class="mb-0"><?php echo $stats['completed']; ?></h3>
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
                            <h3 class="mb-0">Rs<?php echo number_format($stats['spent']); ?></h3>
                            <p class="mb-0">Total Spent</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bookings List -->
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-list"></i> Booking History
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (mysqli_num_rows($bookings_result) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Vehicle</th>
                                            <th>Pickup Date</th>
                                            <th>Return Date</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                            <th>Booking Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($booking = mysqli_fetch_assoc($bookings_result)): ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <?php if ($booking['image']): ?>
                                                            <img src="../assets/img/<?php echo $booking['image']; ?>" 
                                                                 alt="<?php echo $booking['brand_name'] . ' ' . $booking['model']; ?>" 
                                                                 class="me-3" style="width: 50px; height: 40px; object-fit: cover; border-radius: 5px;">
                                                        <?php else: ?>
                                                            <div class="bg-light me-3 d-flex align-items-center justify-content-center" 
                                                                 style="width: 50px; height: 40px; border-radius: 5px;">
                                                                <i class="fas fa-car text-muted"></i>
                                                            </div>
                                                        <?php endif; ?>
                                                        <div>
                                                            <strong><?php echo $booking['brand_name'] . ' ' . $booking['model']; ?></strong>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td><?php echo date('M d, Y', strtotime($booking['pickup_date'])); ?></td>
                                                <td><?php echo date('M d, Y', strtotime($booking['return_date'])); ?></td>
                                                <td>Rs<?php echo number_format($booking['total_amount'], 2); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php 
                                                        echo $booking['status'] == 'pending' ? 'warning' : 
                                                            ($booking['status'] == 'confirmed' ? 'success' : 
                                                            ($booking['status'] == 'cancelled' ? 'danger' : 'secondary')); 
                                                    ?>">
                                                        <?php echo ucfirst($booking['status']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo date('M d, Y', strtotime($booking['booking_date'])); ?></td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <a href="booking_details.php?id=<?php echo $booking['id']; ?>" 
                                                           class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-eye"></i> View
                                                        </a>
                                                        <?php if (in_array($booking['status'], ['pending', 'confirmed'])): ?>
                                                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                                                    onclick="cancelBooking(<?php echo $booking['id']; ?>)">
                                                                <i class="fas fa-times"></i> Cancel
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No bookings found</h5>
                                <p class="text-muted">You haven't made any bookings yet.</p>
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

    <!-- Cancel Booking Modal -->
    <div class="modal fade" id="cancelModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Cancel Booking</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to cancel this booking? This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No, Keep Booking</button>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="booking_id" id="cancelBookingId">
                        <button type="submit" name="cancel_booking" class="btn btn-danger">
                            <i class="fas fa-times"></i> Yes, Cancel Booking
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function cancelBooking(bookingId) {
            document.getElementById('cancelBookingId').value = bookingId;
            new bootstrap.Modal(document.getElementById('cancelModal')).show();
        }
    </script>
</body>
</html>


