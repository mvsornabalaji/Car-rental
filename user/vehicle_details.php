<?php
session_start();
include '../config/database.php';

// Get vehicle ID from URL
$vehicle_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$vehicle_id) {
    header('Location: vehicles.php');
    exit();
}

// Get vehicle details
$vehicle_query = "SELECT v.*, vb.brand_name, vb.description as brand_description 
                 FROM vehicles v 
                 JOIN vehicle_brands vb ON v.brand_id = vb.id 
                 WHERE v.id = $vehicle_id AND v.status = 'available'";
$vehicle_result = mysqli_query($conn, $vehicle_query);

if (mysqli_num_rows($vehicle_result) == 0) {
    header('Location: vehicles.php');
    exit();
}

$vehicle = mysqli_fetch_assoc($vehicle_result);

// Get similar vehicles
$similar_query = "SELECT v.*, vb.brand_name 
                 FROM vehicles v 
                 JOIN vehicle_brands vb ON v.brand_id = vb.id 
                 WHERE v.status = 'available' 
                 AND v.brand_id = {$vehicle['brand_id']} 
                 AND v.id != $vehicle_id 
                 ORDER BY v.daily_rate ASC 
                 LIMIT 3";
$similar_result = mysqli_query($conn, $similar_query);

// Handle booking form submission
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['book_vehicle'])) {
    if (!isset($_SESSION['user_id'])) {
        $error = 'Please login to book a vehicle.';
    } else {
        $pickup_date = mysqli_real_escape_string($conn, $_POST['pickup_date']);
        $return_date = mysqli_real_escape_string($conn, $_POST['return_date']);
        
        // Validation
        if (empty($pickup_date) || empty($return_date)) {
            $error = 'Please select pickup and return dates.';
        } elseif (strtotime($pickup_date) < strtotime(date('Y-m-d'))) {
            $error = 'Pickup date cannot be in the past.';
        } elseif (strtotime($return_date) <= strtotime($pickup_date)) {
            $error = 'Return date must be after pickup date.';
        } else {
            // Calculate total amount
            $pickup = new DateTime($pickup_date);
            $return = new DateTime($return_date);
            $days = $return->diff($pickup)->days;
            $total_amount = $days * $vehicle['daily_rate'];
            
            // Check if vehicle is available for selected dates
            $availability_query = "SELECT COUNT(*) as count FROM bookings1 
                                 WHERE vehicle_id = $vehicle_id 
                                 AND status IN ('pending', 'confirmed') 
                                 AND (
                                     (pickup_date <= '$pickup_date' AND return_date >= '$pickup_date') 
                                     OR (pickup_date <= '$return_date' AND return_date >= '$return_date')
                                     OR (pickup_date >= '$pickup_date' AND return_date <= '$return_date')
                                 )";
            $availability_result = mysqli_query($conn, $availability_query);
            $availability = mysqli_fetch_assoc($availability_result);
            
            if ($availability['count'] > 0) {
                $error = 'Vehicle is not available for the selected dates.';
            } else {
                // Store booking data in session and redirect to payment
                $_SESSION['booking_data'] = [
                    'vehicle_id' => $vehicle_id,
                    'pickup_date' => $pickup_date,
                    'return_date' => $return_date,
                    'days' => $days,
                    'total_amount' => $total_amount
                ];
                
                // Redirect to payment page
                header('Location: payment.php');
                exit();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $vehicle['brand_name'] . ' ' . $vehicle['model']; ?> - Car Rental System</title>
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
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="vehicles.php">Vehicles</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="about.php">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.php">Contact</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <?php if(isset($_SESSION['user_id'])): ?>
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

    <div class="container py-5">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item"><a href="vehicles.php">Vehicles</a></li>
                <li class="breadcrumb-item active"><?php echo $vehicle['brand_name'] . ' ' . $vehicle['model']; ?></li>
            </ol>
        </nav>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- Vehicle Image and Details -->
            <div class="col-lg-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <?php if ($vehicle['image']): ?>
                                    <img src="../assets/img/<?php echo $vehicle['image'];?>"
                                         class="card-img-top" alt="<?php echo $vehicle['image']; ?>">
                                         
                                       
                                <?php else: ?>
                                    <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 300px;">
                                        <i class="fas fa-car fa-4x text-muted"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <h2 class="card-title"><?php echo $vehicle['brand_name'] . ' ' . $vehicle['model']; ?></h2>
                                <p class="text-muted"><?php echo $vehicle['year']; ?> • <?php echo ucfirst($vehicle['color']); ?></p>
                                
                                <div class="mb-3">
                                    <h4 class="text-primary">₹<?php echo number_format($vehicle['daily_rate'], 2); ?> <small class="text-muted">per day</small></h4>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-6">
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="fas fa-cog text-primary me-2"></i>
                                            <span><?php echo ucfirst($vehicle['transmission']); ?></span>
                                        </div>
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="fas fa-gas-pump text-primary me-2"></i>
                                            <span><?php echo ucfirst($vehicle['fuel_type']); ?></span>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="fas fa-users text-primary me-2"></i>
                                            <span><?php echo $vehicle['seats']; ?> Seats</span>
                                        </div>
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="fas fa-check-circle text-success me-2"></i>
                                            <span>Available</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <h6>Description:</h6>
                                    <p class="text-muted"><?php echo $vehicle['description']; ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Vehicle Specifications -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-info-circle"></i> Specifications</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>Brand:</strong></td>
                                        <td><?php echo $vehicle['brand_name']; ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Model:</strong></td>
                                        <td><?php echo $vehicle['model']; ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Year:</strong></td>
                                        <td><?php echo $vehicle['year']; ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Color:</strong></td>
                                        <td><?php echo ucfirst($vehicle['color']); ?></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>Transmission:</strong></td>
                                        <td><?php echo ucfirst($vehicle['transmission']); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Fuel Type:</strong></td>
                                        <td><?php echo ucfirst($vehicle['fuel_type']); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Seats:</strong></td>
                                        <td><?php echo $vehicle['seats']; ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Daily Rate:</strong></td>
                                        <td>₹<?php echo number_format($vehicle['daily_rate'], 2); ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Booking Form -->
            <div class="col-lg-4">
                <div class="card shadow-sm sticky-top" style="top: 100px;">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-calendar-check"></i> Book This Vehicle</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!isset($_SESSION['user_id'])): ?>
                            <div class="text-center mb-3">
                                <p class="text-muted">Please login to book this vehicle.</p>
                                <a href="login.php" class="btn btn-primary">Login</a>
                                <a href="register.php" class="btn btn-outline-primary">Register</a>
                            </div>
                        <?php else: ?>
                            <form method="POST" id="bookingForm">
                                <div class="mb-3">
                                    <label for="pickup_date" class="form-label">Pickup Date</label>
                                    <input type="date" class="form-control" id="pickup_date" name="pickup_date" 
                                           min="<?php echo date('Y-m-d'); ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="return_date" class="form-label">Return Date</label>
                                    <input type="date" class="form-control" id="return_date" name="return_date" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Total Amount</label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rs</span>
                                        <input type="text" class="form-control" id="total_amount" readonly>
                                    </div>
                                    <small class="text-muted">Calculated based on selected dates</small>
                                </div>
                                
                                <button type="submit" name="book_vehicle" class="btn btn-primary w-100">
                                    <i class="fas fa-credit-card"></i> Proceed to Payment
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Similar Vehicles -->
        <?php if (mysqli_num_rows($similar_result) > 0): ?>
            <div class="row mt-5">
                <div class="col-12">
                    <h3 class="mb-4">Similar Vehicles</h3>
                    <div class="row">
                        <?php while($similar = mysqli_fetch_assoc($similar_result)): ?>
                            <div class="col-md-4 mb-4">
                                <div class="card h-100 shadow-sm">
                                    <?php if ($similar['image']): ?>
                                        <img src="../assets/img/<?php echo $similar['image']; ?>" 
                                             class="card-img-top" alt="<?php echo $similar['brand_name'] . ' ' . $similar['model']; ?>"
                                             style="height: 200px; object-fit: cover;">
                                    <?php else: ?>
                                        <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                            <i class="fas fa-car fa-3x text-muted"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo $similar['brand_name'] . ' ' . $similar['model']; ?></h5>
                                        <p class="card-text text-muted"><?php echo $similar['year']; ?> • <?php echo ucfirst($similar['color']); ?></p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="h5 text-primary mb-0">₹<?php echo number_format($similar['daily_rate'], 2); ?></span>
                                            <a href="vehicle_details.php?id=<?php echo $similar['id']; ?>" class="btn btn-outline-primary btn-sm">View Details</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Calculate total amount based on selected dates
        function calculateTotal() {
            const pickupDate = new Date($('#pickup_date').val());
            const returnDate = new Date($('#return_date').val());
            const dailyRate = <?php echo $vehicle['daily_rate']; ?>;
            
            if (pickupDate && returnDate && returnDate > pickupDate) {
                const days = Math.ceil((returnDate - pickupDate) / (1000 * 60 * 60 * 24));
                const total = days * dailyRate;
                $('#total_amount').val(total.toFixed(2));
            } else {
                $('#total_amount').val('');
            }
        }
        
        $('#pickup_date, #return_date').on('change', calculateTotal);
        
        // Set minimum return date based on pickup date
        $('#pickup_date').on('change', function() {
            const pickupDate = $(this).val();
            if (pickupDate) {
                const minReturnDate = new Date(pickupDate);
                minReturnDate.setDate(minReturnDate.getDate() + 1);
                $('#return_date').attr('min', minReturnDate.toISOString().split('T')[0]);
            }
        });
    </script>
</body>
</html>
