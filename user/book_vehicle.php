<?php
session_start();
include '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get vehicle ID from URL
$vehicle_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$vehicle_id) {
    header('Location: vehicles.php');
    exit();
}

// Get vehicle details
$vehicle_query = "SELECT v.*, vb.brand_name FROM vehicles v 
                  JOIN vehicle_brands vb ON v.brand_id = vb.id 
                  WHERE v.id = $vehicle_id AND v.status = 'available'";
$vehicle_result = mysqli_query($conn, $vehicle_query);

if (mysqli_num_rows($vehicle_result) == 0) {
    header('Location: vehicles.php');
    exit();
}

$vehicle = mysqli_fetch_assoc($vehicle_result);
$message = '';

// Handle booking form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pickup_date = $_POST['pickup_date'];
    $return_date = $_POST['return_date'];
    
    // Validate dates
    $pickup_timestamp = strtotime($pickup_date);
    $return_timestamp = strtotime($return_date);
    $today = strtotime(date('Y-m-d'));
    
    if ($pickup_timestamp < $today) {
        $message = "Pickup date cannot be in the past.";
    } elseif ($return_timestamp <= $pickup_timestamp) {
        $message = "Return date must be after pickup date.";
    } else {
        // Calculate total amount
        $days = ($return_timestamp - $pickup_timestamp) / (60 * 60 * 24);
        $total_amount = $days * $vehicle['daily_rate'];
        
        // Store booking data in session
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Vehicle - Car Rental System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .vehicle-card {
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .price-display {
            font-size: 1.5rem;
            font-weight: bold;
            color: #28a745;
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
                <a class="nav-link" href="vehicles.php">Vehicles</a>
                <a class="nav-link" href="logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <div class="row">
            <!-- Vehicle Details -->
            <div class="col-md-6">
                <div class="card vehicle-card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-car"></i> Vehicle Details
                        </h5>
                    </div>
                    <div class="card-body">
                        <h4><?php echo $vehicle['brand_name'] . ' ' . $vehicle['model']; ?></h4>
                        <div class="price-display mb-3">
                            ₹<?php echo number_format($vehicle['daily_rate']); ?> <small class="text-muted">per day</small>
                        </div>
                        
                        <div class="row">
                            <div class="col-6">
                                <p><strong>Year:</strong> <?php echo $vehicle['year']; ?></p>
                                <p><strong>Color:</strong> <?php echo ucfirst($vehicle['color']); ?></p>
                                <p><strong>Transmission:</strong> <?php echo ucfirst($vehicle['transmission']); ?></p>
                            </div>
                            <div class="col-6">
                                <p><strong>Fuel Type:</strong> <?php echo ucfirst($vehicle['fuel_type']); ?></p>
                                <p><strong>Seats:</strong> <?php echo $vehicle['seats']; ?> persons</p>
                                <p><strong>Status:</strong> 
                                    <span class="badge bg-success">Available</span>
                                </p>
                            </div>
                        </div>
                        
                        <?php if ($vehicle['description']): ?>
                            <div class="mt-3">
                                <strong>Description:</strong>
                                <p><?php echo nl2br(htmlspecialchars($vehicle['description'])); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Booking Form -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-calendar-check"></i> Book This Vehicle
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if ($message): ?>
                            <div class="alert alert-danger"><?php echo $message; ?></div>
                        <?php endif; ?>

                        <form method="POST" id="bookingForm">
                            <div class="mb-3">
                                <label for="pickup_date" class="form-label">Pickup Date</label>
                                <input type="date" class="form-control" id="pickup_date" name="pickup_date" 
                                       min="<?php echo date('Y-m-d'); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="return_date" class="form-label">Return Date</label>
                                <input type="date" class="form-control" id="return_date" name="return_date" 
                                       min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" required>
                            </div>

                            <div class="mb-3">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6>Rental Summary</h6>
                                        <div id="rentalSummary">
                                            <p class="mb-1">Select dates to see pricing</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="terms" required>
                                    <label class="form-check-label" for="terms">
                                        I agree to the <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">rental terms and conditions</a>
                                    </label>
                                </div>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-success btn-lg" id="bookButton" disabled>
                                    <i class="fas fa-credit-card"></i> Proceed to Payment
                                </button>
                                <a href="vehicles.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left"></i> Back to Vehicles
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Features -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h6 class="mb-0">Why Choose Us?</h6>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-4">
                                <i class="fas fa-shield-alt text-primary fa-2x mb-2"></i>
                                <p class="small mb-0">Fully Insured</p>
                            </div>
                            <div class="col-4">
                                <i class="fas fa-headset text-success fa-2x mb-2"></i>
                                <p class="small mb-0">24/7 Support</p>
                            </div>
                            <div class="col-4">
                                <i class="fas fa-gas-pump text-info fa-2x mb-2"></i>
                                <p class="small mb-0">Full Tank</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Terms Modal -->
    <div class="modal fade" id="termsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Rental Terms and Conditions</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <h6>Rental Requirements:</h6>
                    <ul>
                        <li>Valid driving license (minimum 1 year old)</li>
                        <li>Government-issued photo ID</li>
                        <li>Minimum age: 21 years</li>
                        <li>Security deposit required</li>
                    </ul>
                    <h6>Terms:</h6>
                    <ul>
                        <li>Vehicle must be returned with same fuel level</li>
                        <li>Late return charges: ₹500 per hour</li>
                        <li>Damage charges as per assessment</li>
                        <li>No smoking in vehicles</li>
                        <li>Maximum speed limit: 80 km/h</li>
                    </ul>
                    <h6>Cancellation Policy:</h6>
                    <ul>
                        <li>Free cancellation up to 24 hours before pickup</li>
                        <li>50% refund for cancellation within 24 hours</li>
                        <li>No refund for no-show</li>
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">I Understand</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const dailyRate = <?php echo $vehicle['daily_rate']; ?>;
        const pickupDateInput = document.getElementById('pickup_date');
        const returnDateInput = document.getElementById('return_date');
        const rentalSummary = document.getElementById('rentalSummary');
        const termsCheckbox = document.getElementById('terms');
        const bookButton = document.getElementById('bookButton');

        function updateSummary() {
            const pickupDate = new Date(pickupDateInput.value);
            const returnDate = new Date(returnDateInput.value);
            
            if (pickupDateInput.value && returnDateInput.value && returnDate > pickupDate) {
                const timeDiff = returnDate.getTime() - pickupDate.getTime();
                const days = Math.ceil(timeDiff / (1000 * 3600 * 24));
                const totalAmount = days * dailyRate;
                
                rentalSummary.innerHTML = `
                    <p class="mb-1"><strong>Duration:</strong> ${days} day${days > 1 ? 's' : ''}</p>
                    <p class="mb-1"><strong>Daily Rate:</strong> ₹${dailyRate.toLocaleString()}</p>
                    <hr class="my-2">
                    <p class="mb-0 h6 text-success"><strong>Total Amount: ₹${totalAmount.toLocaleString()}</strong></p>
                `;
                
                // Update return date minimum
                const minReturnDate = new Date(pickupDate);
                minReturnDate.setDate(minReturnDate.getDate() + 1);
                returnDateInput.min = minReturnDate.toISOString().split('T')[0];
            } else {
                rentalSummary.innerHTML = '<p class="mb-1">Select valid dates to see pricing</p>';
            }
            
            checkFormValidity();
        }

        function checkFormValidity() {
            const pickupDate = new Date(pickupDateInput.value);
            const returnDate = new Date(returnDateInput.value);
            const datesValid = pickupDateInput.value && returnDateInput.value && returnDate > pickupDate;
            const termsAccepted = termsCheckbox.checked;
            
            bookButton.disabled = !(datesValid && termsAccepted);
        }

        pickupDateInput.addEventListener('change', updateSummary);
        returnDateInput.addEventListener('change', updateSummary);
        termsCheckbox.addEventListener('change', checkFormValidity);

        // Set minimum pickup date to today
        const today = new Date().toISOString().split('T')[0];
        pickupDateInput.min = today;
    </script>
</body>
</html>