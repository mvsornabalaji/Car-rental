<?php
session_start();
include '../config/database.php';

// Get filter parameters
$rating_filter = $_GET['rating'] ?? '';
$type_filter = $_GET['type'] ?? '';
$sort_order = $_GET['sort'] ?? 'newest';

// Build query with filters
$where_conditions = ["f.status IN ('reviewed', 'resolved')"];

if (!empty($rating_filter)) {
    $where_conditions[] = "f.rating = '" . mysqli_real_escape_string($conn, $rating_filter) . "'";
}

if (!empty($type_filter)) {
    $where_conditions[] = "f.feedback_type = '" . mysqli_real_escape_string($conn, $type_filter) . "'";
}

$where_clause = "WHERE " . implode(" AND ", $where_conditions);

// Sort order
$order_clause = "ORDER BY ";
switch ($sort_order) {
    case 'oldest':
        $order_clause .= "f.created_at ASC";
        break;
    case 'rating_high':
        $order_clause .= "f.rating DESC, f.created_at DESC";
        break;
    case 'rating_low':
        $order_clause .= "f.rating ASC, f.created_at DESC";
        break;
    default: // newest
        $order_clause .= "f.created_at DESC";
}

// Get all public feedback
$feedback_query = "
    SELECT f.*, 
           u.full_name as user_name,
           CASE 
               WHEN f.booking_id IS NOT NULL THEN CONCAT(vb.brand_name, ' ', v.model)
               ELSE NULL 
           END as vehicle_name
    FROM feedback f 
    JOIN users1 u ON f.user_id = u.id 
    LEFT JOIN bookings1 b ON f.booking_id = b.id
    LEFT JOIN vehicles v ON b.vehicle_id = v.id
    LEFT JOIN vehicle_brands vb ON v.brand_id = vb.id
    $where_clause
    $order_clause
";

$feedback_result = mysqli_query($conn, $feedback_query);
$all_feedback = [];
if ($feedback_result) {
    while ($row = mysqli_fetch_assoc($feedback_result)) {
        $all_feedback[] = $row;
    }
}

// Get statistics for display
$stats_query = "
    SELECT 
        COUNT(*) as total_feedback,
        AVG(rating) as avg_rating,
        COUNT(CASE WHEN rating = 5 THEN 1 END) as five_star,
        COUNT(CASE WHEN rating = 4 THEN 1 END) as four_star,
        COUNT(CASE WHEN rating = 3 THEN 1 END) as three_star,
        COUNT(CASE WHEN rating = 2 THEN 1 END) as two_star,
        COUNT(CASE WHEN rating = 1 THEN 1 END) as one_star
    FROM feedback 
    WHERE status IN ('reviewed', 'resolved')
";

$stats_result = mysqli_query($conn, $stats_query);
if ($stats_result) {
    $stats = mysqli_fetch_assoc($stats_result);
} else {
    $stats = [
        'total_feedback' => 0,
        'avg_rating' => 0,
        'five_star' => 0,
        'four_star' => 0,
        'three_star' => 0,
        'two_star' => 0,
        'one_star' => 0
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Reviews - Car Rental System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .reviews-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            margin: 2rem 0;
        }
        
        .rating-overview {
            background: linear-gradient(45deg, #007bff, #0056b3);
            color: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
        }
        
        .rating-bar {
            background: rgba(255, 255, 255, 0.3);
            border-radius: 10px;
            height: 8px;
            overflow: hidden;
        }
        
        .rating-fill {
            background: #ffc107;
            height: 100%;
            border-radius: 10px;
            transition: width 0.3s ease;
        }
        
        .feedback-card {
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
            margin-bottom: 1.5rem;
            overflow: hidden;
        }
        
        .feedback-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }
        
        .rating-stars {
            color: #ffc107;
            font-size: 1.2rem;
        }
        
        .feedback-type-badge {
            font-size: 0.8rem;
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
        }
        
        .user-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(45deg, #007bff, #0056b3);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1.2rem;
        }
        
        .filter-card {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 15px;
            backdrop-filter: blur(10px);
        }
        
        .btn-filter {
            border-radius: 20px;
            padding: 0.5rem 1.5rem;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .btn-filter:hover {
            transform: translateY(-2px);
        }
        
        .no-reviews {
            text-align: center;
            padding: 3rem;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-car me-2"></i>Trip Wheels
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">
                            <i class="fas fa-home me-1"></i>Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="vehicles.php">
                            <i class="fas fa-car me-1"></i>Vehicles
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="all_feedback.php">
                            <i class="fas fa-star me-1"></i>Reviews
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="about.php">
                            <i class="fas fa-info-circle me-1"></i>About
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.php">
                            <i class="fas fa-envelope me-1"></i>Contact
                        </a>
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
                                <li><a class="dropdown-item" href="feedback.php">My Feedback</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">
                                <i class="fas fa-sign-in-alt me-1"></i>Login
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php">
                                <i class="fas fa-user-plus me-1"></i>Register
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="reviews-container p-4">
            <!-- Header -->
            <div class="text-center mb-4">
                <h1 class="mb-3">
                    <i class="fas fa-star text-warning me-2"></i>
                    Customer Reviews
                </h1>
                <p class="text-muted">See what our customers are saying about their rental experience</p>
            </div>

            <!-- Rating Overview -->
            <div class="rating-overview">
                <div class="row align-items-center">
                    <div class="col-md-4 text-center">
                        <h2 class="display-4 mb-0"><?php echo number_format($stats['avg_rating'], 1); ?></h2>
                        <div class="rating-stars mb-2">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="fas fa-star <?php echo $i <= round($stats['avg_rating']) ? '' : 'text-muted'; ?>"></i>
                            <?php endfor; ?>
                        </div>
                        <p class="mb-0">Based on <?php echo $stats['total_feedback']; ?> reviews</p>
                    </div>
                    <div class="col-md-8">
                        <?php 
                        $total = $stats['total_feedback'];
                        if ($total > 0):
                            for ($i = 5; $i >= 1; $i--):
                                $count = $stats[($i == 5 ? 'five' : ($i == 4 ? 'four' : ($i == 3 ? 'three' : ($i == 2 ? 'two' : 'one')))) . '_star'];
                                $percentage = ($count / $total) * 100;
                        ?>
                            <div class="d-flex align-items-center mb-2">
                                <span class="me-2"><?php echo $i; ?> <i class="fas fa-star"></i></span>
                                <div class="rating-bar flex-grow-1 me-2">
                                    <div class="rating-fill" style="width: <?php echo $percentage; ?>%"></div>
                                </div>
                                <span class="text-end" style="min-width: 40px;"><?php echo $count; ?></span>
                            </div>
                        <?php 
                            endfor;
                        endif; 
                        ?>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="filter-card p-3 mb-4">
                <form method="GET" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">Filter by Rating</label>
                        <select name="rating" class="form-select">
                            <option value="">All Ratings</option>
                            <option value="5" <?php echo $rating_filter == '5' ? 'selected' : ''; ?>>5 Stars</option>
                            <option value="4" <?php echo $rating_filter == '4' ? 'selected' : ''; ?>>4 Stars</option>
                            <option value="3" <?php echo $rating_filter == '3' ? 'selected' : ''; ?>>3 Stars</option>
                            <option value="2" <?php echo $rating_filter == '2' ? 'selected' : ''; ?>>2 Stars</option>
                            <option value="1" <?php echo $rating_filter == '1' ? 'selected' : ''; ?>>1 Star</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Filter by Type</label>
                        <select name="type" class="form-select">
                            <option value="">All Types</option>
                            <option value="general" <?php echo $type_filter == 'general' ? 'selected' : ''; ?>>General</option>
                            <option value="service" <?php echo $type_filter == 'service' ? 'selected' : ''; ?>>Service</option>
                            <option value="vehicle" <?php echo $type_filter == 'vehicle' ? 'selected' : ''; ?>>Vehicle</option>
                            <option value="booking" <?php echo $type_filter == 'booking' ? 'selected' : ''; ?>>Booking</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Sort by</label>
                        <select name="sort" class="form-select">
                            <option value="newest" <?php echo $sort_order == 'newest' ? 'selected' : ''; ?>>Newest First</option>
                            <option value="oldest" <?php echo $sort_order == 'oldest' ? 'selected' : ''; ?>>Oldest First</option>
                            <option value="rating_high" <?php echo $sort_order == 'rating_high' ? 'selected' : ''; ?>>Highest Rating</option>
                            <option value="rating_low" <?php echo $sort_order == 'rating_low' ? 'selected' : ''; ?>>Lowest Rating</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary btn-filter w-100">
                            <i class="fas fa-filter me-1"></i>Apply Filters
                        </button>
                    </div>
                </form>
            </div>

            <!-- Reviews List -->
            <div class="row">
                <?php if (empty($all_feedback)): ?>
                    <div class="col-12">
                        <div class="no-reviews">
                            <i class="fas fa-comments fa-4x mb-3"></i>
                            <h4>No reviews found</h4>
                            <p>No reviews match your current filters or no reviews have been published yet.</p>
                            <a href="all_feedback.php" class="btn btn-primary">View All Reviews</a>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($all_feedback as $feedback): ?>
                        <div class="col-lg-6 mb-4">
                            <div class="feedback-card">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-start mb-3">
                                        <div class="user-avatar me-3">
                                            <?php echo strtoupper(substr($feedback['user_name'], 0, 1)); ?>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <h6 class="mb-1"><?php echo htmlspecialchars($feedback['user_name']); ?></h6>
                                                    <div class="rating-stars mb-1">
                                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                                            <i class="fas fa-star <?php echo $i <= $feedback['rating'] ? '' : 'text-muted'; ?>"></i>
                                                        <?php endfor; ?>
                                                    </div>
                                                </div>
                                                <span class="feedback-type-badge badge bg-<?php 
                                                    echo $feedback['feedback_type'] == 'service' ? 'success' : 
                                                        ($feedback['feedback_type'] == 'vehicle' ? 'info' : 
                                                        ($feedback['feedback_type'] == 'booking' ? 'warning' : 'primary')); 
                                                ?>">
                                                    <?php echo ucfirst($feedback['feedback_type']); ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <h6 class="mb-2"><?php echo htmlspecialchars($feedback['subject']); ?></h6>
                                    <p class="text-muted mb-3"><?php echo nl2br(htmlspecialchars($feedback['message'])); ?></p>
                                    
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <?php if ($feedback['vehicle_name']): ?>
                                                <small class="text-info">
                                                    <i class="fas fa-car me-1"></i>
                                                    <?php echo htmlspecialchars($feedback['vehicle_name']); ?>
                                                </small>
                                            <?php endif; ?>
                                        </div>
                                        <small class="text-muted">
                                            <?php echo date('M d, Y', strtotime($feedback['created_at'])); ?>
                                        </small>
                                    </div>
                                    
                                    <?php if ($feedback['admin_response']): ?>
                                        <div class="mt-3 p-3 bg-light rounded">
                                            <strong class="text-primary">
                                                <i class="fas fa-reply me-1"></i>Response from Management:
                                            </strong>
                                            <p class="mb-0 mt-2"><?php echo nl2br(htmlspecialchars($feedback['admin_response'])); ?></p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Call to Action -->
            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="text-center mt-4 p-4 bg-light rounded">
                    <h5>Share Your Experience</h5>
                    <p class="text-muted mb-3">Help other customers by sharing your rental experience</p>
                    <a href="feedback.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-edit me-2"></i>Write a Review
                    </a>
                </div>
            <?php else: ?>
                <div class="text-center mt-4 p-4 bg-light rounded">
                    <h5>Want to Share Your Experience?</h5>
                    <p class="text-muted mb-3">Login to write a review and help other customers</p>
                    <a href="login.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-sign-in-alt me-2"></i>Login to Review
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>