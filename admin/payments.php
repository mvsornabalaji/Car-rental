<?php
session_start();
include '../config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit();
}

$message = '';

// Handle payment actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        $payment_id = (int)$_POST['payment_id'];
        
        switch ($_POST['action']) {
            case 'mark_completed':
                mysqli_query($conn, "UPDATE payments SET status = 'completed' WHERE id = $payment_id");
                $message = "Payment marked as completed!";
                break;
            case 'mark_failed':
                mysqli_query($conn, "UPDATE payments SET status = 'failed' WHERE id = $payment_id");
                $message = "Payment marked as failed!";
                break;
            case 'process_refund':
                mysqli_query($conn, "UPDATE payments SET status = 'refunded' WHERE id = $payment_id");
                $message = "Refund processed successfully!";
                break;
        }
    }
}

// Get payments with filters
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$method_filter = isset($_GET['method']) ? $_GET['method'] : '';
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

$where_clause = "WHERE 1=1";
if ($status_filter) {
    $where_clause .= " AND p.status = '$status_filter'";
}
if ($method_filter) {
    $where_clause .= " AND p.payment_method = '$method_filter'";
}
if ($search) {
    $where_clause .= " AND (u.full_name LIKE '%$search%' OR p.transaction_id LIKE '%$search%' OR CONCAT(vb.brand_name, ' ', v.model) LIKE '%$search%')";
}

// Check if payments table exists
$table_exists = mysqli_query($conn, "SHOW TABLES LIKE 'payments'");

// Get payment statistics (with error handling)
$stats = [];
if (mysqli_num_rows($table_exists) > 0) {
    $stats['total_payments'] = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM payments"))['count'];
    $stats['completed_payments'] = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM payments WHERE status = 'completed'"))['count'];
    $stats['pending_payments'] = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM payments WHERE status = 'pending'"))['count'];
    $stats['total_revenue'] = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(amount) as total FROM payments WHERE status = 'completed'"))['total'] ?? 0;
    
    // Only run payments query if table exists
    $payments_query = "
        SELECT p.*, b.pickup_date, b.return_date, b.total_amount as booking_amount,
               u.full_name, u.email, 
               v.model, vb.brand_name
        FROM payments p 
        JOIN bookings1 b ON p.booking_id = b.id
        JOIN users1 u ON b.user_id = u.id 
        JOIN vehicles v ON b.vehicle_id = v.id 
        JOIN vehicle_brands vb ON v.brand_id = vb.id 
        $where_clause 
        ORDER BY p.created_at DESC
    ";
    $payments_result = mysqli_query($conn, $payments_query);
} else {
    $stats['total_payments'] = 0;
    $stats['completed_payments'] = 0;
    $stats['pending_payments'] = 0;
    $stats['total_revenue'] = 0;
    $payments_result = false;
    if (!$message) {
        $message = "Payments table not found. Please create the payments table first.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Management - Admin Panel</title>
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
        .stat-card {
            border-radius: 15px;
            border: none;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
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
                        <a class="nav-link active" href="payments.php">
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
                        <a class="nav-link" href="../logout.php">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center py-3 px-4 border-bottom">
                    <h4 class="mb-0">Payment Management</h4>
                    <div class="d-flex align-items-center">
                        <span class="me-3">Welcome, <?php echo $_SESSION['admin_name']; ?></span>
                        <img src="https://via.placeholder.com/40" class="rounded-circle" alt="Admin">
                    </div>
                </div>

                <!-- Content -->
                <div class="p-4">
                    <?php if ($message): ?>
                        <div class="alert alert-<?php echo strpos($message, 'not found') !== false ? 'warning' : 'success'; ?> alert-dismissible fade show" role="alert">
                            <?php echo $message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3 mb-3">
                            <div class="card stat-card bg-primary text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h3 class="mb-0"><?php echo $stats['total_payments']; ?></h3>
                                            <p class="mb-0">Total Payments</p>
                                        </div>
                                        <i class="fas fa-credit-card fa-2x opacity-75"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card stat-card bg-success text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h3 class="mb-0"><?php echo $stats['completed_payments']; ?></h3>
                                            <p class="mb-0">Completed</p>
                                        </div>
                                        <i class="fas fa-check-circle fa-2x opacity-75"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card stat-card bg-warning text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h3 class="mb-0"><?php echo $stats['pending_payments']; ?></h3>
                                            <p class="mb-0">Pending</p>
                                        </div>
                                        <i class="fas fa-clock fa-2x opacity-75"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card stat-card bg-info text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h3 class="mb-0">₹<?php echo number_format($stats['total_revenue']); ?></h3>
                                            <p class="mb-0">Total Revenue</p>
                                        </div>
                                        <i class="fas fa-rupee-sign fa-2x opacity-75"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if (mysqli_num_rows($table_exists) > 0): ?>
                        <!-- Filters -->
                        <div class="card mb-4">
                            <div class="card-body">
                                <form method="GET" class="row g-3">
                                    <div class="col-md-3">
                                        <input type="text" class="form-control" name="search" placeholder="Search payments..." value="<?php echo htmlspecialchars($search); ?>">
                                    </div>
                                    <div class="col-md-2">
                                        <select class="form-select" name="status">
                                            <option value="">All Status</option>
                                            <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="completed" <?php echo $status_filter == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                            <option value="failed" <?php echo $status_filter == 'failed' ? 'selected' : ''; ?>>Failed</option>
                                            <option value="refunded" <?php echo $status_filter == 'refunded' ? 'selected' : ''; ?>>Refunded</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <select class="form-select" name="method">
                                            <option value="">All Methods</option>
                                            <option value="card" <?php echo $method_filter == 'card' ? 'selected' : ''; ?>>Card</option>
                                            <option value="upi" <?php echo $method_filter == 'upi' ? 'selected' : ''; ?>>UPI</option>
                                            <option value="netbanking" <?php echo $method_filter == 'netbanking' ? 'selected' : ''; ?>>Net Banking</option>
                                            <option value="wallet" <?php echo $method_filter == 'wallet' ? 'selected' : ''; ?>>Wallet</option>
                                            <option value="cash" <?php echo $method_filter == 'cash' ? 'selected' : ''; ?>>Cash</option>
                                            <option value="emi" <?php echo $method_filter == 'emi' ? 'selected' : ''; ?>>EMI</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-search"></i> Filter
                                        </button>
                                    </div>
                                    <div class="col-md-3 text-end">
                                        <a href="payments.php" class="btn btn-secondary">
                                            <i class="fas fa-refresh"></i> Reset
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Payments Table -->
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Payment Transactions</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Customer</th>
                                                <th>Vehicle</th>
                                                <th>Amount</th>
                                                <th>Method</th>
                                                <th>Transaction ID</th>
                                                <th>Status</th>
                                                <th>Date</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if ($payments_result && mysqli_num_rows($payments_result) > 0): ?>
                                                <?php while ($payment = mysqli_fetch_assoc($payments_result)): ?>
                                                <tr>
                                                    <td><?php echo $payment['id']; ?></td>
                                                    <td>
                                                        <strong><?php echo htmlspecialchars($payment['full_name']); ?></strong><br>
                                                        <small class="text-muted"><?php echo $payment['email']; ?></small>
                                                    </td>
                                                    <td><?php echo $payment['brand_name'] . ' ' . $payment['model']; ?></td>
                                                    <td>₹<?php echo number_format($payment['amount']); ?></td>
                                                    <td>
                                                        <span class="badge bg-secondary">
                                                            <?php echo ucfirst($payment['payment_method']); ?>
                                                        </span>
                                                    </td>
                                                    <td><?php echo $payment['transaction_id']; ?></td>
                                                    <td>
                                                        <span class="badge bg-<?php 
                                                            echo $payment['status'] == 'completed' ? 'success' : 
                                                                ($payment['status'] == 'pending' ? 'warning' : 
                                                                ($payment['status'] == 'failed' ? 'danger' : 'info')); 
                                                        ?>">
                                                            <?php echo ucfirst($payment['status']); ?>
                                                        </span>
                                                    </td>
                                                    <td><?php echo date('M d, Y H:i', strtotime($payment['created_at'])); ?></td>
                                                    <td>
                                                        <div class="btn-group btn-group-sm">
                                                            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#viewModal<?php echo $payment['id']; ?>">
                                                                <i class="fas fa-eye"></i>
                                                            </button>
                                                            
                                                            <?php if ($payment['status'] == 'pending'): ?>
                                                                <form method="POST" style="display: inline;">
                                                                    <input type="hidden" name="payment_id" value="<?php echo $payment['id']; ?>">
                                                                    <input type="hidden" name="action" value="mark_completed">
                                                                    <button type="submit" class="btn btn-outline-success" onclick="return confirm('Mark as completed?')">
                                                                        <i class="fas fa-check"></i>
                                                                    </button>
                                                                </form>
                                                                <form method="POST" style="display: inline;">
                                                                    <input type="hidden" name="payment_id" value="<?php echo $payment['id']; ?>">
                                                                    <input type="hidden" name="action" value="mark_failed">
                                                                    <button type="submit" class="btn btn-outline-danger" onclick="return confirm('Mark as failed?')">
                                                                        <i class="fas fa-times"></i>
                                                                    </button>
                                                                </form>
                                                            <?php elseif ($payment['status'] == 'completed'): ?>
                                                                <form method="POST" style="display: inline;">
                                                                    <input type="hidden" name="payment_id" value="<?php echo $payment['id']; ?>">
                                                                    <input type="hidden" name="action" value="process_refund">
                                                                    <button type="submit" class="btn btn-outline-warning" onclick="return confirm('Process refund?')">
                                                                        <i class="fas fa-undo"></i>
                                                                    </button>
                                                                </form>
                                                            <?php endif; ?>
                                                        </div>
                                                    </td>
                                                </tr>

                                                <!-- View Modal -->
                                                <div class="modal fade" id="viewModal<?php echo $payment['id']; ?>" tabindex="-1">
                                                    <div class="modal-dialog modal-lg">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Payment Details #<?php echo $payment['id']; ?></h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <div class="row">
                                                                    <div class="col-md-6">
                                                                        <h6>Payment Information</h6>
                                                                        <p><strong>Amount:</strong> ₹<?php echo number_format($payment['amount']); ?></p>
                                                                        <p><strong>Method:</strong> <?php echo ucfirst($payment['payment_method']); ?></p>
                                                                        <p><strong>Transaction ID:</strong> <?php echo $payment['transaction_id']; ?></p>
                                                                        <p><strong>Status:</strong> 
                                                                            <span class="badge bg-<?php 
                                                                                echo $payment['status'] == 'completed' ? 'success' : 
                                                                                    ($payment['status'] == 'pending' ? 'warning' : 
                                                                                    ($payment['status'] == 'failed' ? 'danger' : 'info')); 
                                                                            ?>">
                                                                                <?php echo ucfirst($payment['status']); ?>
                                                                            </span>
                                                                        </p>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <h6>Booking Information</h6>
                                                                        <p><strong>Customer:</strong> <?php echo htmlspecialchars($payment['full_name']); ?></p>
                                                                        <p><strong>Vehicle:</strong> <?php echo $payment['brand_name'] . ' ' . $payment['model']; ?></p>
                                                                        <p><strong>Pickup:</strong> <?php echo date('M d, Y', strtotime($payment['pickup_date'])); ?></p>
                                                                        <p><strong>Return:</strong> <?php echo date('M d, Y', strtotime($payment['return_date'])); ?></p>
                                                                    </div>
                                                                </div>
                                                                <div class="row">
                                                                    <div class="col-12">
                                                                        <p><strong>Payment Date:</strong> <?php echo date('F d, Y H:i:s', strtotime($payment['created_at'])); ?></p>
                                                                        <?php if ($payment['updated_at'] != $payment['created_at']): ?>
                                                                            <p><strong>Last Updated:</strong> <?php echo date('F d, Y H:i:s', strtotime($payment['updated_at'])); ?></p>
                                                                        <?php endif; ?>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <?php endwhile; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="9" class="text-center py-4">
                                                        <i class="fas fa-credit-card fa-3x text-muted mb-3"></i>
                                                        <h5 class="text-muted">No payments found</h5>
                                                        <p class="text-muted">Payment transactions will appear here once bookings are made.</p>
                                                    </td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- No Payments Table Message -->
                        <div class="card">
                            <div class="card-body text-center py-5">
                                <i class="fas fa-database fa-4x text-muted mb-4"></i>
                                <h4 class="text-muted">Payments Table Not Found</h4>
                                <p class="text-muted mb-4">The payments table needs to be created in your database to manage payment transactions.</p>
                                
                                <div class="alert alert-info text-start">
                                    <h6><i class="fas fa-info-circle"></i> To fix this issue:</h6>
                                    <ol class="mb-0">
                                        <li>Open phpMyAdmin in your browser</li>
                                        <li>Select your <code>car_rental</code> database</li>
                                        <li>Click on the "SQL" tab</li>
                                        <li>Run the SQL from <code>database/add_payments_table.sql</code></li>
                                    </ol>
                                </div>
                                
                                <div class="mt-4">
                                    <a href="dashboard.php" class="btn btn-primary">
                                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>