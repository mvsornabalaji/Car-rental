<?php
session_start();
include '../config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

$admin_id = $_SESSION['admin_id'];
$success_message = '';
$error_message = '';

// Handle admin response submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['respond_feedback'])) {
    $feedback_id = mysqli_real_escape_string($conn, $_POST['feedback_id']);
    $admin_response = mysqli_real_escape_string($conn, trim($_POST['admin_response']));
    $new_status = mysqli_real_escape_string($conn, $_POST['status']);
    
    if (!empty($admin_response)) {
        $query = "UPDATE feedback SET admin_response = '$admin_response', status = '$new_status', admin_id = '$admin_id', updated_at = NOW() WHERE id = '$feedback_id'";
        
        if (mysqli_query($conn, $query)) {
            $success_message = "Response submitted successfully!";
        } else {
            $error_message = "Error submitting response. Please try again.";
        }
    } else {
        $error_message = "Please enter a response message.";
    }
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $feedback_id = mysqli_real_escape_string($conn, $_POST['feedback_id']);
    $new_status = mysqli_real_escape_string($conn, $_POST['status']);
    
    $query = "UPDATE feedback SET status = '$new_status', updated_at = NOW() WHERE id = '$feedback_id'";
    
    if (mysqli_query($conn, $query)) {
        $success_message = "Status updated successfully!";
    } else {
        $error_message = "Error updating status. Please try again.";
    }
}

// Get filter parameters
$status_filter = $_GET['status'] ?? '';
$type_filter = $_GET['type'] ?? '';
$rating_filter = $_GET['rating'] ?? '';

// Build query with filters
$where_conditions = [];

if (!empty($status_filter)) {
    $where_conditions[] = "f.status = '" . mysqli_real_escape_string($conn, $status_filter) . "'";
}

if (!empty($type_filter)) {
    $where_conditions[] = "f.feedback_type = '" . mysqli_real_escape_string($conn, $type_filter) . "'";
}

if (!empty($rating_filter)) {
    $where_conditions[] = "f.rating = '" . mysqli_real_escape_string($conn, $rating_filter) . "'";
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Get feedback with user details
$feedback_query = "
    SELECT f.*, 
           u.full_name as user_name, 
           u.email as user_email,
           CASE 
               WHEN f.booking_id IS NOT NULL THEN CONCAT(vb.brand_name, ' ', v.model)
               ELSE NULL 
           END as vehicle_name,
           admin.full_name as admin_name
    FROM feedback f 
    JOIN users1 u ON f.user_id = u.id 
    LEFT JOIN bookings1 b ON f.booking_id = b.id
    LEFT JOIN vehicles v ON b.vehicle_id = v.id
    LEFT JOIN vehicle_brands vb ON v.brand_id = vb.id
    LEFT JOIN users1 admin ON f.admin_id = admin.id
    $where_clause
    ORDER BY f.created_at DESC
";

$feedback_result = mysqli_query($conn, $feedback_query);
$feedback_list = [];
if ($feedback_result) {
    while ($row = mysqli_fetch_assoc($feedback_result)) {
        $feedback_list[] = $row;
    }
} else {
    $error_message = "Error loading feedback data.";
}

// Get statistics
$stats_query = "
    SELECT 
        COUNT(*) as total_feedback,
        AVG(rating) as avg_rating,
        COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_count,
        COUNT(CASE WHEN status = 'reviewed' THEN 1 END) as reviewed_count,
        COUNT(CASE WHEN status = 'resolved' THEN 1 END) as resolved_count,
        COUNT(CASE WHEN rating >= 4 THEN 1 END) as positive_feedback,
        COUNT(CASE WHEN rating <= 2 THEN 1 END) as negative_feedback
    FROM feedback
";

$stats_result = mysqli_query($conn, $stats_query);
if ($stats_result) {
    $stats = mysqli_fetch_assoc($stats_result);
} else {
    $stats = [
        'total_feedback' => 0,
        'avg_rating' => 0,
        'pending_count' => 0,
        'reviewed_count' => 0,
        'resolved_count' => 0,
        'positive_feedback' => 0,
        'negative_feedback' => 0
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback Management - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .admin-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            margin: 2rem 0;
        }
        
        .stats-card {
            background: linear-gradient(45deg, #007bff, #0056b3);
            color: white;
            border-radius: 15px;
            transition: transform 0.3s;
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
        }
        
        .feedback-card {
            border-left: 4px solid #007bff;
            transition: all 0.3s;
        }
        
        .feedback-card:hover {
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .rating-display {
            color: #ffc107;
        }
        
        .status-pending { border-left-color: #ffc107 !important; }
        .status-reviewed { border-left-color: #17a2b8 !important; }
        .status-resolved { border-left-color: #28a745 !important; }
        .status-closed { border-left-color: #6c757d !important; }
        
        .btn-respond {
            background: linear-gradient(45deg, #28a745, #20c997);
            border: none;
            border-radius: 20px;
        }
        
        .btn-respond:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.4);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <!-- Navigation -->
        <nav class="navbar navbar-expand-lg navbar-dark mb-4">
            <div class="container-fluid">
                <a class="navbar-brand" href="dashboard.php">
                    <i class="fas fa-car me-2"></i>Admin Panel
                </a>
                <div class="navbar-nav ms-auto">
                    <a class="nav-link" href="dashboard.php">
                        <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                    </a>
                    <a class="nav-link" href="../logout.php">
                        <i class="fas fa-sign-out-alt me-1"></i>Logout
                    </a>
                </div>
            </div>
        </nav>

        <div class="admin-container p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>
                    <i class="fas fa-comments text-primary me-2"></i>
                    Feedback Management
                </h2>
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

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card stats-card">
                        <div class="card-body text-center">
                            <i class="fas fa-comments fa-2x mb-2"></i>
                            <h4><?php echo $stats['total_feedback']; ?></h4>
                            <p class="mb-0">Total Feedback</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stats-card">
                        <div class="card-body text-center">
                            <i class="fas fa-star fa-2x mb-2"></i>
                            <h4><?php echo number_format($stats['avg_rating'], 1); ?></h4>
                            <p class="mb-0">Average Rating</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stats-card">
                        <div class="card-body text-center">
                            <i class="fas fa-clock fa-2x mb-2"></i>
                            <h4><?php echo $stats['pending_count']; ?></h4>
                            <p class="mb-0">Pending</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stats-card">
                        <div class="card-body text-center">
                            <i class="fas fa-thumbs-up fa-2x mb-2"></i>
                            <h4><?php echo $stats['positive_feedback']; ?></h4>
                            <p class="mb-0">Positive (4-5★)</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-filter me-2"></i>Filters
                    </h5>
                </div>
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="">All Status</option>
                                <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="reviewed" <?php echo $status_filter == 'reviewed' ? 'selected' : ''; ?>>Reviewed</option>
                                <option value="resolved" <?php echo $status_filter == 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                                <option value="closed" <?php echo $status_filter == 'closed' ? 'selected' : ''; ?>>Closed</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Type</label>
                            <select name="type" class="form-select">
                                <option value="">All Types</option>
                                <option value="general" <?php echo $type_filter == 'general' ? 'selected' : ''; ?>>General</option>
                                <option value="service" <?php echo $type_filter == 'service' ? 'selected' : ''; ?>>Service</option>
                                <option value="vehicle" <?php echo $type_filter == 'vehicle' ? 'selected' : ''; ?>>Vehicle</option>
                                <option value="booking" <?php echo $type_filter == 'booking' ? 'selected' : ''; ?>>Booking</option>
                                <option value="complaint" <?php echo $type_filter == 'complaint' ? 'selected' : ''; ?>>Complaint</option>
                                <option value="suggestion" <?php echo $type_filter == 'suggestion' ? 'selected' : ''; ?>>Suggestion</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Rating</label>
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
                            <label class="form-label">&nbsp;</label>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search me-1"></i>Filter
                                </button>
                                <a href="feedback.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-1"></i>Clear
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Feedback List -->
            <div class="row">
                <?php if (empty($feedback_list)): ?>
                    <div class="col-12">
                        <div class="text-center py-5">
                            <i class="fas fa-comments fa-4x text-muted mb-3"></i>
                            <h4 class="text-muted">No feedback found</h4>
                            <p class="text-muted">No feedback matches your current filters.</p>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($feedback_list as $feedback): ?>
                        <div class="col-lg-6 mb-4">
                            <div class="card feedback-card status-<?php echo $feedback['status']; ?>">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0"><?php echo htmlspecialchars($feedback['subject']); ?></h6>
                                        <small class="text-muted">
                                            by <?php echo htmlspecialchars($feedback['user_name']); ?> 
                                            (<?php echo htmlspecialchars($feedback['user_email']); ?>)
                                        </small>
                                    </div>
                                    <span class="badge <?php 
                                        echo $feedback['status'] == 'pending' ? 'bg-warning' : 
                                            ($feedback['status'] == 'reviewed' ? 'bg-info' : 
                                            ($feedback['status'] == 'resolved' ? 'bg-success' : 'bg-secondary')); 
                                    ?>">
                                        <?php echo ucfirst($feedback['status']); ?>
                                    </span>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-6">
                                            <div class="rating-display">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <i class="fas fa-star <?php echo $i <= $feedback['rating'] ? '' : 'text-muted'; ?>"></i>
                                                <?php endfor; ?>
                                                <span class="ms-2"><?php echo $feedback['rating']; ?>/5</span>
                                            </div>
                                        </div>
                                        <div class="col-6 text-end">
                                            <small class="text-muted">
                                                <?php echo ucfirst($feedback['feedback_type']); ?> • 
                                                <?php echo date('M d, Y H:i', strtotime($feedback['created_at'])); ?>
                                            </small>
                                        </div>
                                    </div>
                                    
                                    <?php if ($feedback['vehicle_name']): ?>
                                        <div class="mb-2">
                                            <small class="text-info">
                                                <i class="fas fa-car me-1"></i>
                                                Related to: <?php echo htmlspecialchars($feedback['vehicle_name']); ?>
                                            </small>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <p class="mb-3"><?php echo nl2br(htmlspecialchars($feedback['message'])); ?></p>
                                    
                                    <?php if ($feedback['admin_response']): ?>
                                        <div class="alert alert-light">
                                            <strong>Admin Response:</strong><br>
                                            <?php echo nl2br(htmlspecialchars($feedback['admin_response'])); ?>
                                            <?php if ($feedback['admin_name']): ?>
                                                <br><small class="text-muted">- <?php echo htmlspecialchars($feedback['admin_name']); ?></small>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-respond btn-sm" data-bs-toggle="modal" data-bs-target="#responseModal<?php echo $feedback['id']; ?>">
                                            <i class="fas fa-reply me-1"></i>
                                            <?php echo $feedback['admin_response'] ? 'Update Response' : 'Respond'; ?>
                                        </button>
                                        
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="feedback_id" value="<?php echo $feedback['id']; ?>">
                                            <select name="status" class="form-select form-select-sm d-inline-block w-auto me-2" onchange="this.form.submit()">
                                                <option value="pending" <?php echo $feedback['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                <option value="reviewed" <?php echo $feedback['status'] == 'reviewed' ? 'selected' : ''; ?>>Reviewed</option>
                                                <option value="resolved" <?php echo $feedback['status'] == 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                                                <option value="closed" <?php echo $feedback['status'] == 'closed' ? 'selected' : ''; ?>>Closed</option>
                                            </select>
                                            <input type="hidden" name="update_status" value="1">
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Response Modal -->
                        <div class="modal fade" id="responseModal<?php echo $feedback['id']; ?>" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Respond to Feedback</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form method="POST">
                                        <div class="modal-body">
                                            <input type="hidden" name="feedback_id" value="<?php echo $feedback['id']; ?>">
                                            <div class="mb-3">
                                                <label class="form-label">Response Message</label>
                                                <textarea name="admin_response" class="form-control" rows="4" required placeholder="Enter your response..."><?php echo htmlspecialchars($feedback['admin_response'] ?? ''); ?></textarea>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Update Status</label>
                                                <select name="status" class="form-select" required>
                                                    <option value="reviewed" <?php echo $feedback['status'] == 'reviewed' ? 'selected' : ''; ?>>Reviewed</option>
                                                    <option value="resolved" <?php echo $feedback['status'] == 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                                                    <option value="closed" <?php echo $feedback['status'] == 'closed' ? 'selected' : ''; ?>>Closed</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" name="respond_feedback" class="btn btn-primary">
                                                <i class="fas fa-paper-plane me-1"></i>Submit Response
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>