<?php
session_start();
include '../config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit();
}

$message = '';

// Handle brand actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $brand_name = mysqli_real_escape_string($conn, $_POST['brand_name']);
                $description = mysqli_real_escape_string($conn, $_POST['description']);
                
                $query = "INSERT INTO vehicle_brands (brand_name, description) VALUES ('$brand_name', '$description')";
                if (mysqli_query($conn, $query)) {
                    $message = "Brand added successfully!";
                } else {
                    $message = "Error adding brand: " . mysqli_error($conn);
                }
                break;
                
            case 'update':
                $brand_id = (int)$_POST['brand_id'];
                $brand_name = mysqli_real_escape_string($conn, $_POST['brand_name']);
                $description = mysqli_real_escape_string($conn, $_POST['description']);
                
                $query = "UPDATE vehicle_brands SET brand_name = '$brand_name', description = '$description' WHERE id = $brand_id";
                mysqli_query($conn, $query);
                $message = "Brand updated successfully!";
                break;
                
            case 'toggle_status':
                $brand_id = (int)$_POST['brand_id'];
                $status = $_POST['status'] == 'active' ? 'inactive' : 'active';
                mysqli_query($conn, "UPDATE vehicle_brands SET status = '$status' WHERE id = $brand_id");
                $message = "Brand status updated successfully!";
                break;
                
            case 'delete':
                $brand_id = (int)$_POST['brand_id'];
                mysqli_query($conn, "DELETE FROM vehicle_brands WHERE id = $brand_id");
                $message = "Brand deleted successfully!";
                break;
        }
    }
}

// Get brands
$brands_result = mysqli_query($conn, "SELECT * FROM vehicle_brands ORDER BY brand_name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Brands - Admin Panel</title>
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
                        <a class="nav-link" href="payments.php">
                            <i class="fas fa-credit-card"></i> Payments
                        </a>
                        <a class="nav-link active" href="brands.php">
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
                    <h4 class="mb-0">Manage Brands</h4>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBrandModal">
                        <i class="fas fa-plus"></i> Add Brand
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

                    <!-- Brands Table -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Vehicle Brands</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Brand Name</th>
                                            <th>Description</th>
                                            <th>Status</th>
                                            <th>Created</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($brand = mysqli_fetch_assoc($brands_result)): ?>
                                        <tr>
                                            <td><?php echo $brand['id']; ?></td>
                                            <td><strong><?php echo htmlspecialchars($brand['brand_name']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($brand['description']); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $brand['status'] == 'active' ? 'success' : 'danger'; ?>">
                                                    <?php echo ucfirst($brand['status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('M d, Y', strtotime($brand['created_at'])); ?></td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $brand['id']; ?>">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <form method="POST" style="display: inline;">
                                                        <input type="hidden" name="brand_id" value="<?php echo $brand['id']; ?>">
                                                        <input type="hidden" name="action" value="toggle_status">
                                                        <input type="hidden" name="status" value="<?php echo $brand['status']; ?>">
                                                        <button type="submit" class="btn btn-outline-<?php echo $brand['status'] == 'active' ? 'warning' : 'success'; ?>">
                                                            <i class="fas fa-<?php echo $brand['status'] == 'active' ? 'ban' : 'check'; ?>"></i>
                                                        </button>
                                                    </form>
                                                    <form method="POST" style="display: inline;">
                                                        <input type="hidden" name="brand_id" value="<?php echo $brand['id']; ?>">
                                                        <input type="hidden" name="action" value="delete">
                                                        <button type="submit" class="btn btn-outline-danger" onclick="return confirm('Delete this brand? This will also delete all associated vehicles.')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>

                                        <!-- Edit Modal -->
                                        <div class="modal fade" id="editModal<?php echo $brand['id']; ?>" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Edit Brand</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <form method="POST">
                                                        <div class="modal-body">
                                                            <input type="hidden" name="action" value="update">
                                                            <input type="hidden" name="brand_id" value="<?php echo $brand['id']; ?>">
                                                            <div class="mb-3">
                                                                <label class="form-label">Brand Name</label>
                                                                <input type="text" class="form-control" name="brand_name" value="<?php echo htmlspecialchars($brand['brand_name']); ?>" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Description</label>
                                                                <textarea class="form-control" name="description" rows="3"><?php echo htmlspecialchars($brand['description']); ?></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-primary">Update Brand</button>
                                                        </div>
                                                    </form>
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

    <!-- Add Brand Modal -->
    <div class="modal fade" id="addBrandModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Brand</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        <div class="mb-3">
                            <label class="form-label">Brand Name</label>
                            <input type="text" class="form-control" name="brand_name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Brand</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>