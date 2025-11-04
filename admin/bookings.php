<?php
session_start();
include '../config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit();
}

$message = '';

// Handle booking actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        $booking_id = (int)$_POST['booking_id'];
        
        switch ($_POST['action']) {
            case 'confirm':
                mysqli_query($conn, "UPDATE bookings1 SET status = 'confirmed' WHERE id = $booking_id");
                // Update vehicle status to rented
                mysqli_query($conn, "UPDATE vehicles SET status = 'rented' WHERE id = (SELECT vehicle_id FROM bookings1 WHERE id = $booking_id)");
                $message = "Booking confirmed successfully!";
                break;
            case 'cancel':
                mysqli_query($conn, "UPDATE bookings1 SET status = 'cancelled' WHERE id = $booking_id");
                // Update vehicle status back to available
                mysqli_query($conn, "UPDATE vehicles SET status = 'available' WHERE id = (SELECT vehicle_id FROM bookings1 WHERE id = $booking_id)");
                $message = "Booking cancelled successfully!";
                break;
            case 'complete':
                mysqli_query($conn, "UPDATE bookings1 SET status = 'completed' WHERE id = $booking_id");
                // Update vehicle status back to available
                mysqli_query($conn, "UPDATE vehicles SET status = 'available' WHERE id = (SELECT vehicle_id FROM bookings1 WHERE id = $booking_id)");
                $message = "Booking completed successfully!";
                break;
            case 'update_notes':
                $notes = mysqli_real_escape_string($conn, $_POST['admin_notes']);
                mysqli_query($conn, "UPDATE bookings1 SET admin_notes = '$notes' WHERE id = $booking_id");
                $message = "Notes updated successfully!";
                break;
        }
    }
}

// Get bookings with filters
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

$where_clause = "WHERE 1=1";
if ($status_filter) {
    $where_clause .= " AND b.status = '$status_filter'";
}
if ($search) {
    $where_clause .= " AND (u.full_name LIKE '%$search%' OR u.email LIKE '%$search%' OR CONCAT(vb.brand_name, ' ', v.model) LIKE '%$search%')";
}

$bookings_query = "
    SELECT b.*, u.full_name, u.email, u.phone, 
           v.model, vb.brand_name, v.daily_rate,
           DATEDIFF(b.return_date, b.pickup_date) as days
    FROM bookings1 b 
    JOIN users1 u ON b.user_id = u.id 
    JOIN vehicles v ON b.vehicle_id = v.id 
    JOIN vehicle_brands vb ON v.brand_id = vb.id 
    $where_clause 
    ORDER BY b.booking_date DESC
";
$bookings_result = mysqli_query($conn, $bookings_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bookings - Admin Panel</title>
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
                        <a class="nav-link active" href="bookings.php">
                            <i class="fas fa-calendar-check"></i> Bookings
                        </a>
                        <a class="nav-link" href="payments.php">
                            <i class="fas fa-credit-card"></i> Payments
                        </a>
                        <a class="nav-link" href="brands.php">
                            <i class="fas fa-tags"></i> Brands
                        </a>
                        <a class="nav-link" href="testimonials.php">
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
                    <h4 class="mb-0">Manage Bookings</h4>
                    <div class="d-flex align-items-center">
                        <span class="me-3">Welcome, <?php echo $_SESSION['admin_name']; ?></span>
                        <img src="https://via.placeholder.com/40" class="rounded-circle" alt="Admin">
                    </div>
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
                                    <input type="text" class="form-control" name="search" placeholder="Search bookings..." value="<?php echo htmlspecialchars($search); ?>">
                                </div>
                                <div class="col-md-3">
                                    <select class="form-select" name="status">
                                        <option value="">All Status</option>
                                        <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="confirmed" <?php echo $status_filter == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                        <option value="cancelled" <?php echo $status_filter == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                        <option value="completed" <?php echo $status_filter == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i> Filter
                                    </button>
                                </div>
                                <div class="col-md-3 text-end">
                                    <a href="bookings.php" class="btn btn-secondary">
                                        <i class="fas fa-refresh"></i> Reset
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Bookings Table -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Bookings List</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Customer</th>
                                            <th>Vehicle</th>
                                            <th>Dates</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                            <th>Booked On</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($booking = mysqli_fetch_assoc($bookings_result)): ?>
                                        <tr>
                                            <td><?php echo $booking['id']; ?></td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($booking['full_name']); ?></strong><br>
                                                <small class="text-muted"><?php echo $booking['email']; ?></small>
                                            </td>
                                            <td>
                                                <strong><?php echo $booking['brand_name'] . ' ' . $booking['model']; ?></strong><br>
                                                <small class="text-muted">₹<?php echo number_format($booking['daily_rate']); ?>/day</small>
                                            </td>
                                            <td>
                                                <strong>Pickup:</strong> <?php echo date('M d, Y', strtotime($booking['pickup_date'])); ?><br>
                                                <strong>Return:</strong> <?php echo date('M d, Y', strtotime($booking['return_date'])); ?><br>
                                                <small class="text-muted"><?php echo $booking['days']; ?> days</small>
                                            </td>
                                            <td>₹<?php echo number_format($booking['total_amount']); ?></td>
                                            <td>
                                                <span class="badge bg-<?php 
                                                    echo $booking['status'] == 'confirmed' ? 'success' : 
                                                        ($booking['status'] == 'pending' ? 'warning' : 
                                                        ($booking['status'] == 'completed' ? 'info' : 'danger')); 
                                                ?>">
                                                    <?php echo ucfirst($booking['status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('M d, Y', strtotime($booking['booking_date'])); ?></td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#viewModal<?php echo $booking['id']; ?>">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    
                                                    <?php if ($booking['status'] == 'pending'): ?>
                                                        <form method="POST" style="display: inline;">
                                                            <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                                            <input type="hidden" name="action" value="confirm">
                                                            <button type="submit" class="btn btn-outline-success" onclick="return confirm('Confirm this booking?')">
                                                                <i class="fas fa-check"></i>
                                                            </button>
                                                        </form>
                                                        <form method="POST" style="display: inline;">
                                                            <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                                            <input type="hidden" name="action" value="cancel">
                                                            <button type="submit" class="btn btn-outline-danger" onclick="return confirm('Cancel this booking?')">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        </form>
                                                    <?php elseif ($booking['status'] == 'confirmed'): ?>
                                                        <form method="POST" style="display: inline;">
                                                            <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                                            <input type="hidden" name="action" value="complete">
                                                            <button type="submit" class="btn btn-outline-info" onclick="return confirm('Mark as completed?')">
                                                                <i class="fas fa-flag-checkered"></i>
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>

                                        <!-- View Modal -->
                                        <div class="modal fade" id="viewModal<?php echo $booking['id']; ?>" tabindex="-1">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Booking Details #<?php echo $booking['id']; ?></h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <h6>Customer Information</h6>
                                                                <p><strong>Name:</strong> <?php echo htmlspecialchars($booking['full_name']); ?></p>
                                                                <p><strong>Email:</strong> <?php echo $booking['email']; ?></p>
                                                                <p><strong>Phone:</strong> <?php echo $booking['phone']; ?></p>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <h6>Vehicle Information</h6>
                                                                <p><strong>Vehicle:</strong> <?php echo $booking['brand_name'] . ' ' . $booking['model']; ?></p>
                                                                <p><strong>Daily Rate:</strong> ₹<?php echo number_format($booking['daily_rate']); ?></p>
                                                                <p><strong>Duration:</strong> <?php echo $booking['days']; ?> days</p>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <h6>Booking Details</h6>
                                                                <p><strong>Pickup Date:</strong> <?php echo date('F d, Y', strtotime($booking['pickup_date'])); ?></p>
                                                                <p><strong>Return Date:</strong> <?php echo date('F d, Y', strtotime($booking['return_date'])); ?></p>
                                                                <p><strong>Total Amount:</strong> ₹<?php echo number_format($booking['total_amount']); ?></p>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <p><strong>Status:</strong> 
                                                                    <span class="badge bg-<?php 
                                                                        echo $booking['status'] == 'confirmed' ? 'success' : 
                                                                            ($booking['status'] == 'pending' ? 'warning' : 
                                                                            ($booking['status'] == 'completed' ? 'info' : 'danger')); 
                                                                    ?>">
                                                                        <?php echo ucfirst($booking['status']); ?>
                                                                    </span>
                                                                </p>
                                                                <p><strong>Booked On:</strong> <?php echo date('F d, Y H:i', strtotime($booking['booking_date'])); ?></p>
                                                            </div>
                                                        </div>
                                                        
                                                        <!-- Admin Notes -->
                                                        <div class="mt-3">
                                                            <h6>Admin Notes</h6>
                                                            <form method="POST">
                                                                <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                                                <input type="hidden" name="action" value="update_notes">
                                                                <div class="mb-3">
                                                                    <textarea class="form-control" name="admin_notes" rows="3" placeholder="Add admin notes..."><?php echo htmlspecialchars($booking['admin_notes']); ?></textarea>
                                                                </div>
                                                                <button type="submit" class="btn btn-sm btn-primary">Update Notes</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>