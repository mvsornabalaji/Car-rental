<?php
session_start();
include '../config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit();
}

$message = '';

// Handle testimonial actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $customer_name = mysqli_real_escape_string($conn, $_POST['customer_name']);
                $message_text = mysqli_real_escape_string($conn, $_POST['message']);
                $rating = (int)$_POST['rating'];
                
                $query = "INSERT INTO testimonials (customer_name, message, rating) VALUES ('$customer_name', '$message_text', $rating)";
                if (mysqli_query($conn, $query)) {
                    $message = "Testimonial added successfully!";
                } else {
                    $message = "Error adding testimonial: " . mysqli_error($conn);
                }
                break;
                
            case 'toggle_status':
                $testimonial_id = (int)$_POST['testimonial_id'];
                $status = $_POST['status'] == 'active' ? 'inactive' : 'active';
                mysqli_query($conn, "UPDATE testimonials SET status = '$status' WHERE id = $testimonial_id");
                $message = "Testimonial status updated successfully!";
                break;
                
            case 'delete':
                $testimonial_id = (int)$_POST['testimonial_id'];
                mysqli_query($conn, "DELETE FROM testimonials WHERE id = $testimonial_id");
                $message = "Testimonial deleted successfully!";
                break;
        }
    }
}

// Get testimonials
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

$where_clause = "WHERE 1=1";
if ($status_filter) {
    $where_clause .= " AND status = '$status_filter'";
}
if ($search) {
    $where_clause .= " AND (customer_name LIKE '%$search%' OR message LIKE '%$search%')";
}

$testimonials_result = mysqli_query($conn, "SELECT * FROM testimonials $where_clause ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Testimonials - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, #2c3e50 0%, #34495e 100%);
        }
        .sidebar .nav-link {
            color: #ecf0f1;
            padding: 12px 20px;
            border-radius: 8px;
            margin: 2px 10px;
            transition: all 0.3s;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
        }
        .rating {
            color: #ffc107;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0">
                <div class="sidebar">
                    <div class="p-3 text-center border-bottom border-secondary">
                        <h5 class="text-white mb-0">
                            <i class="fas fa-car"></i> Admin Panel
                        </h5>
                    </div>
                    <nav class="nav flex-column py-3">
                        <a class="nav-link" href="dashboard.php">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                        <a class="nav-link" href="users.php">
                            <i class="fas fa-users"></i> Users
                        </a>
                        <a class="nav-link" href="vehicles.php">
                            <i class="fas fa-car"></i> Vehicles
                        </a>
                        <a class="nav-link" href="bookings.php">
                            <i class="fas fa-calendar-check"></i> Bookings
                        </a>
                        <a class="nav-link" href="brands.php">
                            <i class="fas fa-tags"></i> Brands
                        </a>
                        <a class="nav-link active" href="testimonials.php">
                            <i class="fas fa-star"></i> Testimonials
                        </a>
                        <a class="nav-link" href="contact_queries.php">
                            <i class="fas fa-envelope"></i> Contact Queries
                        </a>
                        <a class="nav-link" href="settings.php">
                            <i class="fas fa-cog"></i> Settings
                        </a>
                        <hr class="border-secondary">
                        <a class="nav-link" href="logout.php">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center py-3 px-4 border-bottom">
                    <h4 class="mb-0">Manage Testimonials</h4>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTestimonialModal">
                        <i class="fas fa-plus"></i> Add Testimonial
                    </button>
                </div>

                <!-- Content -->
                <div class="p-4">
                    <?php if ($message): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php echo $message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Filters -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <form method="GET" class="row g-3">
                                <div class="col-md-4">
                                    <input type="text" class="form-control" name="search" placeholder="Search testimonials..." value="<?php echo htmlspecialchars($search); ?>">
                                </div>
                                <div class="col-md-3">
                                    <select class="form-select" name="status">
                                        <option value="">All Status</option>
                                        <option value="active" <?php echo $status_filter == 'active' ? 'selected' : ''; ?>>Active</option>
                                        <option value="inactive" <?php echo $status_filter == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i> Filter
                                    </button>
                                </div>
                                <div class="col-md-3 text-end">
                                    <a href="testimonials.php" class="btn btn-secondary">
                                        <i class="fas fa-refresh"></i> Reset
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Testimonials Grid -->
                    <div class="row">
                        <?php while ($testimonial = mysqli_fetch_assoc($testimonials_result)): ?>
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <h6 class="card-title mb-0"><?php echo htmlspecialchars($testimonial['customer_name']); ?></h6>
                                        <span class="badge bg-<?php echo $testimonial['status'] == 'active' ? 'success' : 'danger'; ?>">
                                            <?php echo ucfirst($testimonial['status']); ?>
                                        </span>
                                    </div>
                                    
                                    <div class="rating mb-2">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star<?php echo $i <= $testimonial['rating'] ? '' : '-o'; ?>"></i>
                                        <?php endfor; ?>
                                        <span class="ms-1">(<?php echo $testimonial['rating']; ?>/5)</span>
                                    </div>
                                    
                                    <p class="card-text"><?php echo htmlspecialchars($testimonial['message']); ?></p>
                                    
                                    <small class="text-muted">
                                        <?php echo date('M d, Y', strtotime($testimonial['created_at'])); ?>
                                    </small>
                                </div>
                                <div class="card-footer bg-transparent">
                                    <div class="btn-group btn-group-sm w-100">
                                        <form method="POST" style="display: inline; flex: 1;">
                                            <input type="hidden" name="testimonial_id" value="<?php echo $testimonial['id']; ?>">
                                            <input type="hidden" name="action" value="toggle_status">
                                            <input type="hidden" name="status" value="<?php echo $testimonial['status']; ?>">
                                            <button type="submit" class="btn btn-outline-<?php echo $testimonial['status'] == 'active' ? 'warning' : 'success'; ?> w-100">
                                                <i class="fas fa-<?php echo $testimonial['status'] == 'active' ? 'eye-slash' : 'eye'; ?>"></i>
                                                <?php echo $testimonial['status'] == 'active' ? 'Hide' : 'Show'; ?>
                                            </button>
                                        </form>
                                        <form method="POST" style="display: inline; flex: 1;">
                                            <input type="hidden" name="testimonial_id" value="<?php echo $testimonial['id']; ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <button type="submit" class="btn btn-outline-danger w-100" onclick="return confirm('Delete this testimonial?')">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Testimonial Modal -->
    <div class="modal fade" id="addTestimonialModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Testimonial</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        <div class="mb-3">
                            <label class="form-label">Customer Name</label>
                            <input type="text" class="form-control" name="customer_name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Rating</label>
                            <select class="form-select" name="rating" required>
                                <option value="">Select Rating</option>
                                <option value="5">5 Stars - Excellent</option>
                                <option value="4">4 Stars - Very Good</option>
                                <option value="3">3 Stars - Good</option>
                                <option value="2">2 Stars - Fair</option>
                                <option value="1">1 Star - Poor</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Message</label>
                            <textarea class="form-control" name="message" rows="4" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Testimonial</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>