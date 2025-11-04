<?php
session_start();
include '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$booking_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$booking_id) {
    header('Location: bookings.php');
    exit();
}

// Get booking details with vehicle information
$booking_query = "SELECT b.*, v.model, v.image, v.color, v.transmission, v.fuel_type, v.seats, v.year, vb.brand_name
                  FROM bookings1 b
                  JOIN vehicles v ON b.vehicle_id = v.id
                  JOIN vehicle_brands vb ON v.brand_id = vb.id
                  WHERE b.id = $booking_id AND b.user_id = $user_id";
$booking_result = mysqli_query($conn, $booking_query);

if (mysqli_num_rows($booking_result) == 0) {
    header('Location:  bookings.php');
    exit();
}

$booking = mysqli_fetch_assoc($booking_result);

// Calculate rental duration
$pickup_date = new DateTime($booking['pickup_date']);
$return_date = new DateTime($booking['return_date']);
$duration = $pickup_date->diff($return_date)->days;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Details - Car Rental System</title>
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
                            <li><a class="dropdown-item" href="bookings.php">My Bookings</a></li>
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
                <li class="breadcrumb-item"><a href="bookings.php">My Bookings</a></li>
                <li class="breadcrumb-item active">Booking Details</li>
            </ol>
        </nav>

        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h4 class="card-title">
                            <i class="fas fa-calendar-check"></i> Booking Details
                        </h4>
                        <p class="card-text">Booking #<?php echo $booking_id; ?> - <?php echo $booking['brand_name'] . ' ' . $booking['model']; ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Vehicle Information -->
            <div class="col-lg-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-car"></i> Vehicle Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <?php if ($booking['image']): ?>
                                    <img src="../assets/img/<?php echo $booking['image']; ?>" 
                                         alt="<?php echo $booking['brand_name'] . ' ' . $booking['model']; ?>" 
                                         class="img-fluid rounded">
                                <?php else: ?>
                                    <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 200px;">
                                        <i class="fas fa-car fa-3x text-muted"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-8">
                                <h4><?php echo $booking['brand_name'] . ' ' . $booking['model']; ?></h4>
                                <p class="text-muted"><?php echo $booking['year']; ?> • <?php echo ucfirst($booking['color']); ?></p>
                                
                                <div class="row mb-3">
                                    <div class="col-6">
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="fas fa-cog text-primary me-2"></i>
                                            <span><?php echo ucfirst($booking['transmission']); ?></span>
                                        </div>
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="fas fa-gas-pump text-primary me-2"></i>
                                            <span><?php echo ucfirst($booking['fuel_type']); ?></span>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="fas fa-users text-primary me-2"></i>
                                            <span><?php echo $booking['seats']; ?> Seats</span>
                                        </div>
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="fas fa-calendar text-primary me-2"></i>
                                            <span><?php echo $booking['year']; ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Booking Information -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-info-circle"></i> Booking Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>Booking ID:</strong></td>
                                        <td>#<?php echo $booking_id; ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Pickup Date:</strong></td>
                                        <td><?php echo date('F d, Y (l)', strtotime($booking['pickup_date'])); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Return Date:</strong></td>
                                        <td><?php echo date('F d, Y (l)', strtotime($booking['return_date'])); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Duration:</strong></td>
                                        <td><?php echo $duration; ?> day<?php echo $duration > 1 ? 's' : ''; ?></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>Status:</strong></td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                echo $booking['status'] == 'pending' ? 'warning' : 
                                                    ($booking['status'] == 'confirmed' ? 'success' : 
                                                    ($booking['status'] == 'cancelled' ? 'danger' : 'secondary')); 
                                            ?>">
                                                <?php echo ucfirst($booking['status']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Booking Date:</strong></td>
                                        <td><?php echo date('F d, Y', strtotime($booking['booking_date'])); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Daily Rate:</strong></td>
                                        <td>Rs<?php echo number_format($booking['total_amount'] / $duration); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Total Amount:</strong></td>
                                        <td class="h5 text-primary">Rs<?php echo number_format($booking['total_amount']); ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Panel -->
            <div class="col-lg-4">
                <div class="card shadow-sm sticky-top" style="top: 100px;">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-tools"></i> Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="bookings.php" class="btn btn-outline-primary">
                                <i class="fas fa-arrow-left"></i> Back to Bookings
                            </a>
                            
                            <?php if (in_array($booking['status'], ['pending', 'confirmed'])): ?>
                                <button type="button" class="btn btn-outline-danger" 
                                        onclick="cancelBooking(<?php echo $booking_id; ?>)">
                                    <i class="fas fa-times"></i> Cancel Booking
                                </button>
                            <?php endif; ?>
                            
                            <?php if ($booking['status'] == 'confirmed'): ?>
                                <a href="contact.php" class="btn btn-outline-info">
                                    <i class="fas fa-phone"></i> Contact Support
                                </a>
                            <?php endif; ?>
                            
                            <a href="vehicles.php" class="btn btn-outline-success">
                                <i class="fas fa-car"></i> Rent Another Vehicle
                            </a>
                        </div>
                        
                        <?php if ($booking['admin_notes']): ?>
                            <hr>
                            <h6>Admin Notes:</h6>
                            <p class="text-muted small"><?php echo $booking['admin_notes']; ?></p>
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
                    <p class="text-muted small">Booking #<?php echo $booking_id; ?> - <?php echo $booking['brand_name'] . ' ' . $booking['model']; ?></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No, Keep Booking</button>
                    <form method="POST" action="bookings.php" style="display: inline;">
                        <input type="hidden" name="booking_id" value="<?php echo $booking_id; ?>">
                        <button type="submit" name="cancel_booking" class="btn btn-danger">
                            <i class="fas fa-times"></i> Yes, Cancel Booking
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function cancelBooking(bookingId) {
            new bootstrap.Modal(document.getElementById('cancelModal')).show();
        }
    </script>
</body>
</html>


