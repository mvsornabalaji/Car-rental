<?php
session_start();
include '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success_message = '';
$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $rating = mysqli_real_escape_string($conn, $_POST['rating']);
    $subject = mysqli_real_escape_string($conn, trim($_POST['subject']));
    $message = mysqli_real_escape_string($conn, trim($_POST['message']));
    $feedback_type = mysqli_real_escape_string($conn, $_POST['feedback_type']);
    $booking_id = !empty($_POST['booking_id']) ? mysqli_real_escape_string($conn, $_POST['booking_id']) : 'NULL';
    
    // Validate input
    if (empty($rating) || empty($subject) || empty($message)) {
        $error_message = "Please fill in all required fields.";
    } elseif ($rating < 1 || $rating > 5) {
        $error_message = "Please select a valid rating.";
    } else {
        $booking_value = ($booking_id === 'NULL') ? 'NULL' : "'$booking_id'";
        $query = "INSERT INTO feedback (user_id, booking_id, rating, subject, message, feedback_type) 
                  VALUES ('$user_id', $booking_value, '$rating', '$subject', '$message', '$feedback_type')";
        
        if (mysqli_query($conn, $query)) {
            $success_message = "Thank you for your feedback! We appreciate your input.";
        } else {
            $error_message = "Error submitting feedback. Please try again.";
        }
    }
}

// Get user's bookings for dropdown
$bookings_query = "
    SELECT b.id, CONCAT(vb.brand_name, ' ', v.model) as vehicle_name, b.booking_date 
    FROM bookings1 b 
    JOIN vehicles v ON b.vehicle_id = v.id 
    JOIN vehicle_brands vb ON v.brand_id = vb.id 
    WHERE b.user_id = '$user_id' 
    ORDER BY b.booking_date DESC
";
$bookings_result = mysqli_query($conn, $bookings_query);
$user_bookings = [];
if ($bookings_result) {
    while ($row = mysqli_fetch_assoc($bookings_result)) {
        $user_bookings[] = $row;
    }
}

// Get user's previous feedback
$feedback_query = "
    SELECT f.*, 
           CASE 
               WHEN f.booking_id IS NOT NULL THEN CONCAT(vb.brand_name, ' ', v.model)
               ELSE 'General Feedback' 
           END as related_booking
    FROM feedback f 
    LEFT JOIN bookings1 b ON f.booking_id = b.id 
    LEFT JOIN vehicles v ON b.vehicle_id = v.id
    LEFT JOIN vehicle_brands vb ON v.brand_id = vb.id
    WHERE f.user_id = '$user_id' 
    ORDER BY f.created_at DESC 
    LIMIT 10
";
$feedback_result = mysqli_query($conn, $feedback_query);
$previous_feedback = [];
if ($feedback_result) {
    while ($row = mysqli_fetch_assoc($feedback_result)) {
        $previous_feedback[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback - Car Rental System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .feedback-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            margin: 2rem 0;
        }
        
        .rating-stars {
            font-size: 2rem;
            color: #ddd;
            cursor: pointer;
            transition: color 0.3s;
        }
        
        .rating-stars.active,
        .rating-stars:hover {
            color: #ffc107;
        }
        
        .feedback-card {
            background: #f8f9fa;
            border-left: 4px solid #007bff;
            margin-bottom: 1rem;
        }
        
        .status-badge {
            font-size: 0.8rem;
        }
        
        .btn-primary {
            background: linear-gradient(45deg, #007bff, #0056b3);
            border: none;
            border-radius: 25px;
            padding: 12px 30px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 123, 255, 0.4);
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Navigation -->
        <nav class="navbar navbar-expand-lg navbar-dark mb-4">
            <div class="container-fluid">
                <a class="navbar-brand" href="dashboard.php">
                    <i class="fas fa-car me-2"></i>Trip Wheels
                </a>
                <div class="navbar-nav ms-auto">
                    <a class="nav-link" href="dashboard.php">
                        <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                    </a>
                    <a class="nav-link" href="logout.php">
                        <i class="fas fa-sign-out-alt me-1"></i>Logout
                    </a>
                </div>
            </div>
        </nav>

        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="feedback-container p-4">
                    <div class="text-center mb-4">
                        <h2 class="mb-3">
                            <i class="fas fa-comments text-primary me-2"></i>
                            Share Your Feedback
                        </h2>
                        <p class="text-muted">We value your opinion and strive to improve our services</p>
                        <div class="mb-3">
                            <a href="all_feedback.php" class="btn btn-outline-primary">
                                <i class="fas fa-star me-1"></i>View All Customer Reviews
                            </a>
                        </div>
                    </div>

                    <?php if ($success_message): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i><?php echo $success_message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if ($error_message): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i><?php echo $error_message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <div class="row">
                        <!-- Feedback Form -->
                        <div class="col-lg-6">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0">
                                        <i class="fas fa-edit me-2"></i>Submit Feedback
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <form method="POST">
                                        <div class="mb-3">
                                            <label class="form-label">Rating *</label>
                                            <div class="rating-container">
                                                <input type="hidden" name="rating" id="rating" required>
                                                <div class="d-flex">
                                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                                        <i class="fas fa-star rating-stars" data-rating="<?php echo $i; ?>"></i>
                                                    <?php endfor; ?>
                                                </div>
                                                <small class="text-muted">Click to rate (1-5 stars)</small>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="feedback_type" class="form-label">Feedback Type *</label>
                                            <select class="form-select" name="feedback_type" required>
                                                <option value="">Select Type</option>
                                                <option value="general">General</option>
                                                <option value="service">Service Quality</option>
                                                <option value="vehicle">Vehicle Condition</option>
                                                <option value="booking">Booking Process</option>
                                                <option value="complaint">Complaint</option>
                                                <option value="suggestion">Suggestion</option>
                                            </select>
                                        </div>

                                        <div class="mb-3">
                                            <label for="booking_id" class="form-label">Related Booking (Optional)</label>
                                            <select class="form-select" name="booking_id">
                                                <option value="">Select Booking</option>
                                                <?php foreach ($user_bookings as $booking): ?>
                                                    <option value="<?php echo $booking['id']; ?>">
                                                        <?php echo htmlspecialchars($booking['vehicle_name'] . ' - ' . date('M d, Y', strtotime($booking['booking_date']))); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="mb-3">
                                            <label for="subject" class="form-label">Subject *</label>
                                            <input type="text" class="form-control" name="subject" required maxlength="255" placeholder="Brief subject of your feedback">
                                        </div>

                                        <div class="mb-3">
                                            <label for="message" class="form-label">Message *</label>
                                            <textarea class="form-control" name="message" rows="5" required placeholder="Please share your detailed feedback..."></textarea>
                                        </div>

                                        <button type="submit" class="btn btn-primary w-100">
                                            <i class="fas fa-paper-plane me-2"></i>Submit Feedback
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Previous Feedback -->
                        <div class="col-lg-6">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-info text-white">
                                    <h5 class="mb-0">
                                        <i class="fas fa-history me-2"></i>Your Previous Feedback
                                    </h5>
                                </div>
                                <div class="card-body" style="max-height: 500px; overflow-y: auto;">
                                    <?php if (empty($previous_feedback)): ?>
                                        <div class="text-center text-muted py-4">
                                            <i class="fas fa-comments fa-3x mb-3"></i>
                                            <p>No previous feedback found.</p>
                                        </div>
                                    <?php else: ?>
                                        <?php foreach ($previous_feedback as $feedback): ?>
                                            <div class="feedback-card p-3 rounded">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <h6 class="mb-0"><?php echo htmlspecialchars($feedback['subject']); ?></h6>
                                                    <span class="badge status-badge <?php 
                                                        echo $feedback['status'] == 'pending' ? 'bg-warning' : 
                                                            ($feedback['status'] == 'reviewed' ? 'bg-info' : 
                                                            ($feedback['status'] == 'resolved' ? 'bg-success' : 'bg-secondary')); 
                                                    ?>">
                                                        <?php echo ucfirst($feedback['status']); ?>
                                                    </span>
                                                </div>
                                                <div class="mb-2">
                                                    <div class="d-flex align-items-center">
                                                        <div class="me-3">
                                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                                <i class="fas fa-star <?php echo $i <= $feedback['rating'] ? 'text-warning' : 'text-muted'; ?>"></i>
                                                            <?php endfor; ?>
                                                        </div>
                                                        <small class="text-muted">
                                                            <?php echo ucfirst($feedback['feedback_type']); ?> • 
                                                            <?php echo date('M d, Y', strtotime($feedback['created_at'])); ?>
                                                        </small>
                                                    </div>
                                                </div>
                                                <p class="text-muted mb-2"><?php echo htmlspecialchars($feedback['message']); ?></p>
                                                <?php if ($feedback['admin_response']): ?>
                                                    <div class="alert alert-light mb-0">
                                                        <strong>Admin Response:</strong><br>
                                                        <?php echo htmlspecialchars($feedback['admin_response']); ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Rating system
        document.addEventListener('DOMContentLoaded', function() {
            const stars = document.querySelectorAll('.rating-stars');
            const ratingInput = document.getElementById('rating');
            
            stars.forEach((star, index) => {
                star.addEventListener('click', function() {
                    const rating = this.getAttribute('data-rating');
                    ratingInput.value = rating;
                    
                    // Update star display
                    stars.forEach((s, i) => {
                        if (i < rating) {
                            s.classList.add('active');
                        } else {
                            s.classList.remove('active');
                        }
                    });
                });
                
                star.addEventListener('mouseover', function() {
                    const rating = this.getAttribute('data-rating');
                    stars.forEach((s, i) => {
                        if (i < rating) {
                            s.style.color = '#ffc107';
                        } else {
                            s.style.color = '#ddd';
                        }
                    });
                });
            });
            
            // Reset on mouse leave
            document.querySelector('.rating-container').addEventListener('mouseleave', function() {
                const currentRating = ratingInput.value;
                stars.forEach((s, i) => {
                    if (i < currentRating) {
                        s.style.color = '#ffc107';
                    } else {
                        s.style.color = '#ddd';
                    }
                });
            });
        });
    </script>
</body>
</html>