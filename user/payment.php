<?php
session_start();
include '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Check if booking data exists in session
if (!isset($_SESSION['booking_data'])) {
    // Redirect to vehicles page if no booking data
    header('Location: vehicles.php');
    exit();
}

// Debug: Log booking data (remove in production)
error_log("Booking data: " . print_r($_SESSION['booking_data'], true));

$booking_data = $_SESSION['booking_data'];
$message = '';

// Handle payment processing
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $payment_method = $_POST['payment_method'];
    $user_id = $_SESSION['user_id'];
    
    // Insert booking into database
    $vehicle_id = $booking_data['vehicle_id'];
    $pickup_date = $booking_data['pickup_date'];
    $return_date = $booking_data['return_date'];
    $total_amount = $booking_data['total_amount'];
    
    $query = "INSERT INTO bookings1 (user_id, vehicle_id, pickup_date, return_date, total_amount, status) 
              VALUES ($user_id, $vehicle_id, '$pickup_date', '$return_date', $total_amount, 'confirmed')";
    
    if (mysqli_query($conn, $query)) {
        $booking_id = mysqli_insert_id($conn);
        
        // Insert payment record (check if payments table exists)
        $table_check = mysqli_query($conn, "SHOW TABLES LIKE 'payments'");
        if (mysqli_num_rows($table_check) > 0) {
            $payment_query = "INSERT INTO payments (booking_id, payment_method, amount, status, transaction_id) 
                             VALUES ($booking_id, '$payment_method', $total_amount, 'completed', 'TXN" . time() . "')";
            mysqli_query($conn, $payment_query);
        }
        
        // Update vehicle status
        mysqli_query($conn, "UPDATE vehicles SET status = 'rented' WHERE id = $vehicle_id");
        
        // Clear booking data from session
        unset($_SESSION['booking_data']);
        
        // Redirect to success page
        header('Location: booking_success.php?booking_id=' . $booking_id);
        exit();
    } else {
        $message = "Error processing booking: " . mysqli_error($conn);
    }
}

// Get vehicle details
$vehicle_query = "SELECT v.*, vb.brand_name FROM vehicles v 
                  JOIN vehicle_brands vb ON v.brand_id = vb.id 
                  WHERE v.id = " . $booking_data['vehicle_id'];
$vehicle_result = mysqli_query($conn, $vehicle_query);
$vehicle = mysqli_fetch_assoc($vehicle_result);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - Car Rental System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .payment-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }
        .payment-method {
            border: 2px solid #e9ecef;
            border-radius: 15px;
            transition: all 0.3s;
            cursor: pointer;
            background: white;
        }
        .payment-method:hover {
            border-color: #667eea;
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.2);
            transform: translateY(-2px);
        }
        .payment-method.selected {
            border-color: #667eea;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
        }
        .payment-method.selected .payment-icon {
            color: white !important;
        }
        .payment-method.selected small {
            color: rgba(255, 255, 255, 0.8) !important;
        }
        .payment-icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
        }
        .payment-details {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 20px;
            margin-top: 20px;
        }
        .form-control {
            border-radius: 10px;
            border: 2px solid #e9ecef;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-pay {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border: none;
            border-radius: 10px;
            padding: 15px 30px;
            font-size: 1.1rem;
            font-weight: bold;
        }
        .btn-pay:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.4);
        }
    </style>
</head>
<body>
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

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="payment-container p-5">
                    <!-- Header -->
                    <div class="text-center mb-5">
                        <i class="fas fa-credit-card fa-3x text-primary mb-3"></i>
                        <h2>Complete Your Payment</h2>
                        <p class="text-muted">Choose your preferred payment method to confirm your booking</p>
                    </div>

                    <?php if ($message): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle"></i> <?php echo $message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <div class="row">
                        <!-- Booking Summary -->
                        <div class="col-md-4">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0"><i class="fas fa-receipt"></i> Booking Summary</h5>
                                </div>
                                <div class="card-body">
                                    <div class="text-center mb-3">
                                        <?php if ($vehicle['image'] && file_exists("../uploads/vehicles/" . $vehicle['image'])): ?>
                                            <img src="../uploads/vehicles/<?php echo $vehicle['image']; ?>" 
                                                 alt="<?php echo $vehicle['brand_name'] . ' ' . $vehicle['model']; ?>" 
                                                 class="img-fluid rounded" style="max-height: 150px;">
                                        <?php else: ?>
                                            <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 150px;">
                                                <i class="fas fa-car fa-3x text-muted"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <h6><?php echo $vehicle['brand_name'] . ' ' . $vehicle['model']; ?></h6>
                                    <hr>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Pickup Date:</span>
                                        <strong><?php echo date('M d, Y', strtotime($booking_data['pickup_date'])); ?></strong>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Return Date:</span>
                                        <strong><?php echo date('M d, Y', strtotime($booking_data['return_date'])); ?></strong>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Duration:</span>
                                        <strong><?php echo $booking_data['days']; ?> days</strong>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Daily Rate:</span>
                                        <strong>₹<?php echo number_format($vehicle['daily_rate']); ?></strong>
                                    </div>
                                    <hr>
                                    <div class="d-flex justify-content-between">
                                        <span class="h5">Total Amount:</span>
                                        <span class="h5 text-success">₹<?php echo number_format($booking_data['total_amount']); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Methods -->
                        <div class="col-md-8">
                            <form method="POST" id="paymentForm">
                                <h5 class="mb-4">Select Payment Method</h5>
                                
                                <div class="row g-3 mb-4">
                                    <!-- Credit/Debit Card -->
                                    <div class="col-md-6">
                                        <div class="payment-method p-4 text-center h-100" data-method="card">
                                            <input type="radio" name="payment_method" value="card" class="d-none" required>
                                            <div class="payment-icon text-primary">
                                                <i class="fas fa-credit-card"></i>
                                            </div>
                                            <h6>Credit/Debit Card</h6>
                                            <small class="text-muted">Visa, MasterCard, RuPay</small>
                                        </div>
                                    </div>

                                    <!-- UPI Payment -->
                                    <div class="col-md-6">
                                        <div class="payment-method p-4 text-center h-100" data-method="upi">
                                            <input type="radio" name="payment_method" value="upi" class="d-none" required>
                                            <div class="payment-icon text-success">
                                                <i class="fas fa-mobile-alt"></i>
                                            </div>
                                            <h6>UPI Payment</h6>
                                            <small class="text-muted">PhonePe, GPay, Paytm</small>
                                        </div>
                                    </div>

                                    <!-- Net Banking -->
                                    <div class="col-md-6">
                                        <div class="payment-method p-4 text-center h-100" data-method="netbanking">
                                            <input type="radio" name="payment_method" value="netbanking" class="d-none" required>
                                            <div class="payment-icon text-info">
                                                <i class="fas fa-university"></i>
                                            </div>
                                            <h6>Net Banking</h6>
                                            <small class="text-muted">All major banks</small>
                                        </div>
                                    </div>

                                    <!-- Digital Wallet -->
                                    <div class="col-md-6">
                                        <div class="payment-method p-4 text-center h-100" data-method="wallet">
                                            <input type="radio" name="payment_method" value="wallet" class="d-none" required>
                                            <div class="payment-icon text-warning">
                                                <i class="fas fa-wallet"></i>
                                            </div>
                                            <h6>Digital Wallet</h6>
                                            <small class="text-muted">Paytm, Amazon Pay</small>
                                        </div>
                                    </div>

                                    <!-- Cash on Pickup -->
                                    <div class="col-md-6">
                                        <div class="payment-method p-4 text-center h-100" data-method="cash">
                                            <input type="radio" name="payment_method" value="cash" class="d-none" required>
                                            <div class="payment-icon text-secondary">
                                                <i class="fas fa-money-bill-wave"></i>
                                            </div>
                                            <h6>Cash on Pickup</h6>
                                            <small class="text-muted">Pay at pickup location</small>
                                        </div>
                                    </div>

                                    <!-- EMI Options -->
                                    <div class="col-md-6">
                                        <div class="payment-method p-4 text-center h-100" data-method="emi">
                                            <input type="radio" name="payment_method" value="emi" class="d-none" required>
                                            <div class="payment-icon text-danger">
                                                <i class="fas fa-calendar-alt"></i>
                                            </div>
                                            <h6>EMI Options</h6>
                                            <small class="text-muted">3, 6, 12 months</small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Payment Details Section -->
                                <div id="paymentDetails" style="display: none;">
                                    <!-- Card Details -->
                                    <div id="cardDetails" class="payment-details" style="display: none;">
                                        <h6><i class="fas fa-credit-card"></i> Card Details</h6>
                                        <div class="row g-3">
                                            <div class="col-12">
                                                <label class="form-label">Card Number</label>
                                                <input type="text" class="form-control" placeholder="1234 5678 9012 3456" maxlength="19">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Expiry Date</label>
                                                <input type="text" class="form-control" placeholder="MM/YY" maxlength="5">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">CVV</label>
                                                <input type="text" class="form-control" placeholder="123" maxlength="3">
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label">Cardholder Name</label>
                                                <input type="text" class="form-control" placeholder="Name on card">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- UPI Details -->
                                    <div id="upiDetails" class="payment-details" style="display: none;">
                                        <h6><i class="fas fa-mobile-alt"></i> UPI Payment</h6>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label class="form-label">UPI ID</label>
                                                <input type="text" class="form-control" placeholder="yourname@upi">
                                                <small class="text-muted">Or scan QR code with your UPI app</small>
                                            </div>
                                            <div class="col-md-6 text-center">
                                                <div class="border rounded p-3">
                                                    <i class="fas fa-qrcode fa-4x text-muted mb-2"></i>
                                                    <p class="small text-muted">QR Code</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Net Banking Details -->
                                    <div id="netbankingDetails" class="payment-details" style="display: none;">
                                        <h6><i class="fas fa-university"></i> Select Your Bank</h6>
                                        <select class="form-select">
                                            <option value="">Choose your bank</option>
                                            <option value="sbi">State Bank of India</option>
                                            <option value="hdfc">HDFC Bank</option>
                                            <option value="icici">ICICI Bank</option>
                                            <option value="axis">Axis Bank</option>
                                            <option value="pnb">Punjab National Bank</option>
                                            <option value="kotak">Kotak Mahindra Bank</option>
                                            <option value="other">Other Banks</option>
                                        </select>
                                    </div>

                                    <!-- Wallet Details -->
                                    <div id="walletDetails" class="payment-details" style="display: none;">
                                        <h6><i class="fas fa-wallet"></i> Select Wallet</h6>
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <div class="card text-center p-3 wallet-option" data-wallet="paytm">
                                                    <i class="fas fa-mobile-alt text-primary fa-2x mb-2"></i>
                                                    <h6>Paytm</h6>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="card text-center p-3 wallet-option" data-wallet="amazonpay">
                                                    <i class="fab fa-amazon text-warning fa-2x mb-2"></i>
                                                    <h6>Amazon Pay</h6>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="card text-center p-3 wallet-option" data-wallet="phonepe">
                                                    <i class="fas fa-wallet text-success fa-2x mb-2"></i>
                                                    <h6>PhonePe</h6>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Cash Details -->
                                    <div id="cashDetails" class="payment-details" style="display: none;">
                                        <h6><i class="fas fa-money-bill-wave"></i> Cash Payment Instructions</h6>
                                        <div class="alert alert-info">
                                            <ul class="mb-0">
                                                <li>Pay the full amount at the time of vehicle pickup</li>
                                                <li>Carry exact change if possible</li>
                                                <li>Receipt will be provided after payment</li>
                                                <li>Valid ID and driving license required</li>
                                                <li>Security deposit may be required separately</li>
                                            </ul>
                                        </div>
                                    </div>

                                    <!-- EMI Details -->
                                    <div id="emiDetails" class="payment-details" style="display: none;">
                                        <h6><i class="fas fa-calendar-alt"></i> EMI Options</h6>
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <div class="card text-center p-3 emi-option" data-months="3">
                                                    <h5>3 Months</h5>
                                                    <p class="mb-1">₹<?php echo number_format($booking_data['total_amount'] / 3); ?>/month</p>
                                                    <small class="text-muted">Interest: 12% p.a.</small>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="card text-center p-3 emi-option" data-months="6">
                                                    <h5>6 Months</h5>
                                                    <p class="mb-1">₹<?php echo number_format($booking_data['total_amount'] / 6); ?>/month</p>
                                                    <small class="text-muted">Interest: 14% p.a.</small>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="card text-center p-3 emi-option" data-months="12">
                                                    <h5>12 Months</h5>
                                                    <p class="mb-1">₹<?php echo number_format($booking_data['total_amount'] / 12); ?>/month</p>
                                                    <small class="text-muted">Interest: 16% p.a.</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Terms and Submit -->
                                <div class="mt-4">
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="checkbox" id="terms" required>
                                        <label class="form-check-label" for="terms">
                                            I agree to the <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">Terms and Conditions</a>
                                        </label>
                                    </div>
                                    
                                    <div class="d-flex gap-3">
                                        <a href="book_vehicle.php?id=<?php echo $booking_data['vehicle_id']; ?>" class="btn btn-outline-secondary">
                                            <i class="fas fa-arrow-left"></i> Back
                                        </a>
                                        <button type="submit" class="btn btn-pay flex-fill" id="payButton" disabled>
                                            <i class="fas fa-lock"></i> Pay ₹<?php echo number_format($booking_data['total_amount']); ?>
                                        </button>
                                    </div>
                                </div>
                            </form>
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
                    <h5 class="modal-title">Terms and Conditions</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <h6>Payment Terms:</h6>
                    <ul>
                        <li>All payments are processed securely</li>
                        <li>Refunds are subject to cancellation policy</li>
                        <li>Additional charges may apply for damages</li>
                        <li>Security deposit may be required</li>
                    </ul>
                    <h6>Booking Terms:</h6>
                    <ul>
                        <li>Valid driving license required</li>
                        <li>Minimum age requirement: 21 years</li>
                        <li>Vehicle must be returned in same condition</li>
                        <li>Late return charges apply</li>
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
        // Payment method selection
        document.querySelectorAll('.payment-method').forEach(method => {
            method.addEventListener('click', function() {
                // Remove selected class from all methods
                document.querySelectorAll('.payment-method').forEach(m => {
                    m.classList.remove('selected');
                    // Also uncheck all radio buttons
                    const radio = m.querySelector('input[type="radio"]');
                    if (radio) radio.checked = false;
                });
                
                // Add selected class to clicked method
                this.classList.add('selected');
                
                // Check the radio button
                const radio = this.querySelector('input[type="radio"]');
                if (radio) {
                    radio.checked = true;
                    
                    // Show payment details
                    showPaymentDetails(radio.value);
                    
                    // Enable pay button if terms are checked
                    checkFormValidity();
                }
            });
        });

        function showPaymentDetails(method) {
            // Hide all payment details first
            document.querySelectorAll('.payment-details').forEach(detail => {
                detail.style.display = 'none';
            });
            
            // Show payment details container
            const paymentDetailsContainer = document.getElementById('paymentDetails');
            if (paymentDetailsContainer) {
                paymentDetailsContainer.style.display = 'block';
                
                // Show selected payment details
                const detailsDiv = document.getElementById(method + 'Details');
                if (detailsDiv) {
                    detailsDiv.style.display = 'block';
                } else {
                    // If no specific details, hide the container
                    paymentDetailsContainer.style.display = 'none';
                }
            }
        }

        // Terms checkbox
        const termsCheckbox = document.getElementById('terms');
        if (termsCheckbox) {
            termsCheckbox.addEventListener('change', checkFormValidity);
        }

        function checkFormValidity() {
            const paymentSelected = document.querySelector('input[name="payment_method"]:checked');
            const termsChecked = document.getElementById('terms').checked;
            const payButton = document.getElementById('payButton');
            
            if (payButton) {
                payButton.disabled = !(paymentSelected && termsChecked);
                
                // Update button text based on selection
                if (paymentSelected && termsChecked) {
                    payButton.classList.remove('btn-secondary');
                    payButton.classList.add('btn-pay');
                } else {
                    payButton.classList.remove('btn-pay');
                    payButton.classList.add('btn-secondary');
                }
            }
        }

        // Card number formatting
        document.addEventListener('input', function(e) {
            if (e.target.placeholder === '1234 5678 9012 3456') {
                let value = e.target.value.replace(/\s/g, '').replace(/[^0-9]/gi, '');
                let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
                e.target.value = formattedValue;
            }
            
            if (e.target.placeholder === 'MM/YY') {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length >= 2) {
                    value = value.substring(0, 2) + '/' + value.substring(2, 4);
                }
                e.target.value = value;
            }
        });

        // Wallet selection
        document.querySelectorAll('.wallet-option').forEach(wallet => {
            wallet.addEventListener('click', function() {
                document.querySelectorAll('.wallet-option').forEach(w => w.classList.remove('border-primary'));
                this.classList.add('border-primary');
            });
        });

        // EMI selection
        document.querySelectorAll('.emi-option').forEach(emi => {
            emi.addEventListener('click', function() {
                document.querySelectorAll('.emi-option').forEach(e => e.classList.remove('border-primary'));
                this.classList.add('border-primary');
            });
        });

        // Initialize form validation on page load
        document.addEventListener('DOMContentLoaded', function() {
            checkFormValidity();
            
            // Add click event listeners to payment methods
            document.querySelectorAll('.payment-method').forEach(method => {
                method.style.cursor = 'pointer';
                
                // Add visual feedback on hover
                method.addEventListener('mouseenter', function() {
                    if (!this.classList.contains('selected')) {
                        this.style.transform = 'translateY(-2px)';
                    }
                });
                
                method.addEventListener('mouseleave', function() {
                    if (!this.classList.contains('selected')) {
                        this.style.transform = 'translateY(0)';
                    }
                });
            });
            
            console.log('Payment form initialized');
        });
    </script>
</body>
</html>