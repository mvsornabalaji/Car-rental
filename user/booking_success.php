<?php
session_start();
include '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get booking ID from URL
$booking_id = isset($_GET['booking_id']) ? (int)$_GET['booking_id'] : 0;

if (!$booking_id) {
    header('Location: dashboard.php');
    exit();
}

// Get booking details
$booking_query = "
    SELECT b.*, u.full_name, u.email, u.phone, 
           v.model, vb.brand_name, v.daily_rate,
           p.payment_method, p.transaction_id, p.amount as payment_amount
    FROM bookings1 b 
    JOIN users1 u ON b.user_id = u.id 
    JOIN vehicles v ON b.vehicle_id = v.id 
    JOIN vehicle_brands vb ON v.brand_id = vb.id 
    LEFT JOIN payments p ON b.id = p.booking_id
    WHERE b.id = $booking_id AND b.user_id = " . $_SESSION['user_id'];

$booking_result = mysqli_query($conn, $booking_query);

if (mysqli_num_rows($booking_result) == 0) {
    header('Location: dashboard.php');
    exit();
}

$booking = mysqli_fetch_assoc($booking_result);
$days = (strtotime($booking['return_date']) - strtotime($booking['pickup_date'])) / (60 * 60 * 24);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmed - Car Rental System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .success-icon {
            font-size: 4rem;
            color: #28a745;
        }
        .booking-card {
            border-left: 4px solid #28a745;
        }
    </style>
</head>
<body class="bg-light">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-car"></i> Trip Wheels
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="dashboard.php">Dashboard</a>
                <a class="nav-link" href="logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <!-- Success Message -->
                <div class="text-center mb-4">
                    <div class="success-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h2 class="text-success mt-3">Booking Confirmed!</h2>
                    <p class="lead">Your car rental booking has been successfully confirmed.</p>
                </div>

                <!-- Booking Details -->
                <div class="card booking-card shadow">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-receipt"></i> Booking Details
                            <span class="float-end">ID: #<?php echo $booking['id']; ?></span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Vehicle Information</h6>
                                <p><strong>Vehicle:</strong> <?php echo $booking['brand_name'] . ' ' . $booking['model']; ?></p>
                                <p><strong>Daily Rate:</strong> ₹<?php echo number_format($booking['daily_rate']); ?></p>
                                
                                <h6 class="mt-4">Rental Period</h6>
                                <p><strong>Pickup Date:</strong> <?php echo date('F d, Y', strtotime($booking['pickup_date'])); ?></p>
                                <p><strong>Return Date:</strong> <?php echo date('F d, Y', strtotime($booking['return_date'])); ?></p>
                                <p><strong>Duration:</strong> <?php echo $days; ?> days</p>
                            </div>
                            <div class="col-md-6">
                                <h6>Payment Information</h6>
                                <p><strong>Payment Method:</strong> <?php echo ucfirst($booking['payment_method']); ?></p>
                                <p><strong>Transaction ID:</strong> <?php echo $booking['transaction_id']; ?></p>
                                <p><strong>Amount Paid:</strong> ₹<?php echo number_format($booking['payment_amount']); ?></p>
                                
                                <h6 class="mt-4">Status</h6>
                                <p><strong>Booking Status:</strong> 
                                    <span class="badge bg-success">Confirmed</span>
                                </p>
                                <p><strong>Booking Date:</strong> <?php echo date('F d, Y H:i', strtotime($booking['booking_date'])); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Important Information -->
                <div class="card mt-4">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0"><i class="fas fa-info-circle"></i> Important Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>What to bring:</h6>
                                <ul>
                                    <li>Valid driving license</li>
                                    <li>Government-issued ID</li>
                                    <li>Credit card for security deposit</li>
                                    <li>This booking confirmation</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6>Pickup Instructions:</h6>
                                <ul>
                                    <li>Arrive 15 minutes early</li>
                                    <li>Vehicle inspection will be done</li>
                                    <li>Security deposit may be required</li>
                                    <li>Contact us for any changes</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="text-center mt-4">
                    <div class="btn-group" role="group">
                        <button class="btn btn-primary" onclick="window.print()">
                            <i class="fas fa-print"></i> Print Confirmation
                        </button>
                        <a href="dashboard.php" class="btn btn-success">
                            <i class="fas fa-tachometer-alt"></i> Go to Dashboard
                        </a>
                        <a href="vehicles.php" class="btn btn-outline-primary">
                            <i class="fas fa-car"></i> Book Another Vehicle
                        </a>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="card mt-4">
                    <div class="card-body text-center">
                        <h6>Need Help?</h6>
                        <p class="mb-0">
                            <i class="fas fa-phone"></i> Call us: +91 9585740928 | 
                            <i class="fas fa-envelope"></i> Email: support@carrental.com
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>