<?php
session_start();
include '../config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit();
}

$message = '';

// Handle query actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        $query_id = (int)$_POST['query_id'];
        
        switch ($_POST['action']) {
            case 'mark_read':
                mysqli_query($conn, "UPDATE contact_queries SET status = 'read' WHERE id = $query_id");
                $message = "Query marked as read!";
                break;
            case 'mark_replied':
                mysqli_query($conn, "UPDATE contact_queries SET status = 'replied' WHERE id = $query_id");
                $message = "Query marked as replied!";
                break;
            case 'delete':
                mysqli_query($conn, "DELETE FROM contact_queries WHERE id = $query_id");
                $message = "Query deleted successfully!";
                break;
        }
    }
}

// Get contact queries
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

$where_clause = "WHERE 1=1";
if ($status_filter) {
    $where_clause .= " AND status = '$status_filter'";
}
if ($search) {
    $where_clause .= " AND (name LIKE '%$search%' OR email LIKE '%$search%' OR subject LIKE '%$search%' OR message LIKE '%$search%')";
}

$queries_result = mysqli_query($conn, "SELECT * FROM contact_queries $where_clause ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Queries - Admin Panel</title>
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
                        <a class="nav-link" href="bookings.php">
                            <i class="fas fa-calendar-check"></i> Bookings
                        </a>
                        <a class="nav-link" href="brands.php">
                            <i class="fas fa-tags"></i> Brands
                        </a>
                        <a class="nav-link" href="testimonials.php">
                            <i class="fas fa-star"></i> Testimonials
                        </a>
                        <a class="nav-link active" href="contact_queries.php">
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
                    <h4 class="mb-0">Contact Queries</h4>
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
                                    <input type="text" class="form-control" name="search" placeholder="Search queries..." value="<?php echo htmlspecialchars($search); ?>">
                                </div>
                                <div class="col-md-3">
                                    <select class="form-select" name="status">
                                        <option value="">All Status</option>
                                        <option value="new" <?php echo $status_filter == 'new' ? 'selected' : ''; ?>>New</option>
                                        <option value="read" <?php echo $status_filter == 'read' ? 'selected' : ''; ?>>Read</option>
                                        <option value="replied" <?php echo $status_filter == 'replied' ? 'selected' : ''; ?>>Replied</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i> Filter
                                    </button>
                                </div>
                                <div class="col-md-3 text-end">
                                    <a href="contact_queries.php" class="btn btn-secondary">
                                        <i class="fas fa-refresh"></i> Reset
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Queries Table -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Contact Queries</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Subject</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($query = mysqli_fetch_assoc($queries_result)): ?>
                                        <tr class="<?php echo $query['status'] == 'new' ? 'table-warning' : ''; ?>">
                                            <td><?php echo $query['id']; ?></td>
                                            <td><?php echo htmlspecialchars($query['name']); ?></td>
                                            <td><?php echo htmlspecialchars($query['email']); ?></td>
                                            <td><?php echo htmlspecialchars($query['subject']); ?></td>
                                            <td>
                                                <span class="badge bg-<?php 
                                                    echo $query['status'] == 'new' ? 'warning' : 
                                                        ($query['status'] == 'read' ? 'info' : 'success'); 
                                                ?>">
                                                    <?php echo ucfirst($query['status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('M d, Y H:i', strtotime($query['created_at'])); ?></td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#viewModal<?php echo $query['id']; ?>">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    
                                                    <?php if ($query['status'] == 'new'): ?>
                                                        <form method="POST" style="display: inline;">
                                                            <input type="hidden" name="query_id" value="<?php echo $query['id']; ?>">
                                                            <input type="hidden" name="action" value="mark_read">
                                                            <button type="submit" class="btn btn-outline-info">
                                                                <i class="fas fa-check"></i>
                                                            </button>
                                                        </form>
                                                    <?php elseif ($query['status'] == 'read'): ?>
                                                        <form method="POST" style="display: inline;">
                                                            <input type="hidden" name="query_id" value="<?php echo $query['id']; ?>">
                                                            <input type="hidden" name="action" value="mark_replied">
                                                            <button type="submit" class="btn btn-outline-success">
                                                                <i class="fas fa-reply"></i>
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>
                                                    
                                                    <form method="POST" style="display: inline;">
                                                        <input type="hidden" name="query_id" value="<?php echo $query['id']; ?>">
                                                        <input type="hidden" name="action" value="delete">
                                                        <button type="submit" class="btn btn-outline-danger" onclick="return confirm('Delete this query?')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>

                                        <!-- View Modal -->
                                        <div class="modal fade" id="viewModal<?php echo $query['id']; ?>" tabindex="-1">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Contact Query #<?php echo $query['id']; ?></h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <p><strong>Name:</strong> <?php echo htmlspecialchars($query['name']); ?></p>
                                                                <p><strong>Email:</strong> <?php echo htmlspecialchars($query['email']); ?></p>
                                                                <p><strong>Subject:</strong> <?php echo htmlspecialchars($query['subject']); ?></p>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <p><strong>Status:</strong> 
                                                                    <span class="badge bg-<?php 
                                                                        echo $query['status'] == 'new' ? 'warning' : 
                                                                            ($query['status'] == 'read' ? 'info' : 'success'); 
                                                                    ?>">
                                                                        <?php echo ucfirst($query['status']); ?>
                                                                    </span>
                                                                </p>
                                                                <p><strong>Date:</strong> <?php echo date('F d, Y H:i', strtotime($query['created_at'])); ?></p>
                                                            </div>
                                                        </div>
                                                        <div class="mt-3">
                                                            <strong>Message:</strong>
                                                            <div class="border p-3 mt-2 bg-light">
                                                                <?php echo nl2br(htmlspecialchars($query['message'])); ?>
                                                            </div>
                                                        </div>
                                                        <div class="mt-3">
                                                            <a href="mailto:<?php echo $query['email']; ?>?subject=Re: <?php echo urlencode($query['subject']); ?>" class="btn btn-primary">
                                                                <i class="fas fa-reply"></i> Reply via Email
                                                            </a>
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