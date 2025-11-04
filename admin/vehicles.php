<?php
session_start();
include '../config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit();
}

$message = '';

// Handle vehicle actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $brand_id = (int)$_POST['brand_id'];
                $model = mysqli_real_escape_string($conn, $_POST['model']);
                $year = (int)$_POST['year'];
                $color = mysqli_real_escape_string($conn, $_POST['color']);
                $transmission = $_POST['transmission'];
                $fuel_type = $_POST['fuel_type'];
                $seats = (int)$_POST['seats'];
                $daily_rate = (float)$_POST['daily_rate'];
                $description = mysqli_real_escape_string($conn, $_POST['description']);
                
                $query = "INSERT INTO vehicles (brand_id, model, year, color, transmission, fuel_type, seats, daily_rate, description) 
                         VALUES ($brand_id, '$model', $year, '$color', '$transmission', '$fuel_type', $seats, $daily_rate, '$description')";
                
                if (mysqli_query($conn, $query)) {
                    $message = "Vehicle added successfully!";
                } else {
                    $message = "Error adding vehicle: " . mysqli_error($conn);
                }
                break;
                
            case 'update_status':
                $vehicle_id = (int)$_POST['vehicle_id'];
                $status = $_POST['status'];
                mysqli_query($conn, "UPDATE vehicles SET status = '$status' WHERE id = $vehicle_id");
                $message = "Vehicle status updated successfully!";
                break;
                
            case 'delete':
                $vehicle_id = (int)$_POST['vehicle_id'];
                mysqli_query($conn, "DELETE FROM vehicles WHERE id = $vehicle_id");
                $message = "Vehicle deleted successfully!";
                break;
        }
    }
}

// Get vehicles with brands
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$brand_filter = isset($_GET['brand']) ? (int)$_GET['brand'] : '';

$where_clause = "WHERE 1=1";
if ($search) {
    $where_clause .= " AND (v.model LIKE '%$search%' OR vb.brand_name LIKE '%$search%')";
}
if ($status_filter) {
    $where_clause .= " AND v.status = '$status_filter'";
}
if ($brand_filter) {
    $where_clause .= " AND v.brand_id = $brand_filter";
}

$vehicles_query = "SELECT v.*, vb.brand_name FROM vehicles v 
                   JOIN vehicle_brands vb ON v.brand_id = vb.id 
                   $where_clause ORDER BY v.created_at DESC";
$vehicles_result = mysqli_query($conn, $vehicles_query);

// Get brands for dropdown
$brands_result = mysqli_query($conn, "SELECT * FROM vehicle_brands WHERE status = 'active' ORDER BY brand_name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Vehicles - Admin Panel</title>
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
                        <a class="nav-link active" href="vehicles.php">
                            <i class="fas fa-car"></i> Vehicles
                        </a>
                        <a class="nav-link" href="bookings.php">
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
                    <h4 class="mb-0">Manage Vehicles</h4>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addVehicleModal">
                        <i class="fas fa-plus"></i> Add Vehicle
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
                                <div class="col-md-3">
                                    <input type="text" class="form-control" name="search" placeholder="Search vehicles..." value="<?php echo htmlspecialchars($search); ?>">
                                </div>
                                <div class="col-md-2">
                                    <select class="form-select" name="status">
                                        <option value="">All Status</option>
                                        <option value="available" <?php echo $status_filter == 'available' ? 'selected' : ''; ?>>Available</option>
                                        <option value="rented" <?php echo $status_filter == 'rented' ? 'selected' : ''; ?>>Rented</option>
                                        <option value="maintenance" <?php echo $status_filter == 'maintenance' ? 'selected' : ''; ?>>Maintenance</option>
                                        <option value="inactive" <?php echo $status_filter == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <select class="form-select" name="brand">
                                        <option value="">All Brands</option>
                                        <?php 
                                        mysqli_data_seek($brands_result, 0);
                                        while ($brand = mysqli_fetch_assoc($brands_result)): 
                                        ?>
                                            <option value="<?php echo $brand['id']; ?>" <?php echo $brand_filter == $brand['id'] ? 'selected' : ''; ?>>
                                                <?php echo $brand['brand_name']; ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i> Filter
                                    </button>
                                </div>
                                <div class="col-md-3 text-end">
                                    <a href="vehicles.php" class="btn btn-secondary">
                                        <i class="fas fa-refresh"></i> Reset
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Vehicles Table -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Vehicles List</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Vehicle</th>
                                            <th>Year</th>
                                            <th>Color</th>
                                            <th>Transmission</th>
                                            <th>Daily Rate</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($vehicle = mysqli_fetch_assoc($vehicles_result)): ?>
                                        <tr>
                                            <td><?php echo $vehicle['id']; ?></td>
                                            <td>
                                                <strong><?php echo $vehicle['brand_name'] . ' ' . $vehicle['model']; ?></strong><br>
                                                <small class="text-muted"><?php echo $vehicle['fuel_type'] . ' • ' . $vehicle['seats'] . ' seats'; ?></small>
                                            </td>
                                            <td><?php echo $vehicle['year']; ?></td>
                                            <td><?php echo ucfirst($vehicle['color']); ?></td>
                                            <td><?php echo ucfirst($vehicle['transmission']); ?></td>
                                            <td>₹<?php echo number_format($vehicle['daily_rate']); ?></td>
                                            <td>
                                                <span class="badge bg-<?php 
                                                    echo $vehicle['status'] == 'available' ? 'success' : 
                                                        ($vehicle['status'] == 'rented' ? 'warning' : 
                                                        ($vehicle['status'] == 'maintenance' ? 'info' : 'danger')); 
                                                ?>">
                                                    <?php echo ucfirst($vehicle['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#viewModal<?php echo $vehicle['id']; ?>">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <div class="dropdown">
                                                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                            <i class="fas fa-cog"></i>
                                                        </button>
                                                        <ul class="dropdown-menu">
                                                            <li>
                                                                <form method="POST" style="display: inline;">
                                                                    <input type="hidden" name="vehicle_id" value="<?php echo $vehicle['id']; ?>">
                                                                    <input type="hidden" name="action" value="update_status">
                                                                    <input type="hidden" name="status" value="available">
                                                                    <button type="submit" class="dropdown-item">Set Available</button>
                                                                </form>
                                                            </li>
                                                            <li>
                                                                <form method="POST" style="display: inline;">
                                                                    <input type="hidden" name="vehicle_id" value="<?php echo $vehicle['id']; ?>">
                                                                    <input type="hidden" name="action" value="update_status">
                                                                    <input type="hidden" name="status" value="maintenance">
                                                                    <button type="submit" class="dropdown-item">Set Maintenance</button>
                                                                </form>
                                                            </li>
                                                            <li>
                                                                <form method="POST" style="display: inline;">
                                                                    <input type="hidden" name="vehicle_id" value="<?php echo $vehicle['id']; ?>">
                                                                    <input type="hidden" name="action" value="update_status">
                                                                    <input type="hidden" name="status" value="inactive">
                                                                    <button type="submit" class="dropdown-item">Set Inactive</button>
                                                                </form>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                    <form method="POST" style="display: inline;">
                                                        <input type="hidden" name="vehicle_id" value="<?php echo $vehicle['id']; ?>">
                                                        <input type="hidden" name="action" value="delete">
                                                        <button type="submit" class="btn btn-outline-danger" onclick="return confirm('Delete this vehicle?')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>

                                        <!-- View Modal -->
                                        <div class="modal fade" id="viewModal<?php echo $vehicle['id']; ?>" tabindex="-1">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title"><?php echo $vehicle['brand_name'] . ' ' . $vehicle['model']; ?></h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <p><strong>Brand:</strong> <?php echo $vehicle['brand_name']; ?></p>
                                                                <p><strong>Model:</strong> <?php echo $vehicle['model']; ?></p>
                                                                <p><strong>Year:</strong> <?php echo $vehicle['year']; ?></p>
                                                                <p><strong>Color:</strong> <?php echo ucfirst($vehicle['color']); ?></p>
                                                                <p><strong>Transmission:</strong> <?php echo ucfirst($vehicle['transmission']); ?></p>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <p><strong>Fuel Type:</strong> <?php echo ucfirst($vehicle['fuel_type']); ?></p>
                                                                <p><strong>Seats:</strong> <?php echo $vehicle['seats']; ?></p>
                                                                <p><strong>Daily Rate:</strong> ₹<?php echo number_format($vehicle['daily_rate']); ?></p>
                                                                <p><strong>Status:</strong> 
                                                                    <span class="badge bg-<?php 
                                                                        echo $vehicle['status'] == 'available' ? 'success' : 
                                                                            ($vehicle['status'] == 'rented' ? 'warning' : 
                                                                            ($vehicle['status'] == 'maintenance' ? 'info' : 'danger')); 
                                                                    ?>">
                                                                        <?php echo ucfirst($vehicle['status']); ?>
                                                                    </span>
                                                                </p>
                                                                <p><strong>Added:</strong> <?php echo date('F d, Y', strtotime($vehicle['created_at'])); ?></p>
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

    <!-- Add Vehicle Modal -->
    <div class="modal fade" id="addVehicleModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Vehicle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Brand</label>
                                <select class="form-select" name="brand_id" required>
                                    <option value="">Select Brand</option>
                                    <?php 
                                    mysqli_data_seek($brands_result, 0);
                                    while ($brand = mysqli_fetch_assoc($brands_result)): 
                                    ?>
                                        <option value="<?php echo $brand['id']; ?>"><?php echo $brand['brand_name']; ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Model</label>
                                <input type="text" class="form-control" name="model" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Year</label>
                                <input type="number" class="form-control" name="year" min="2000" max="2025" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Color</label>
                                <input type="text" class="form-control" name="color" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Transmission</label>
                                <select class="form-select" name="transmission" required>
                                    <option value="automatic">Automatic</option>
                                    <option value="manual">Manual</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Fuel Type</label>
                                <select class="form-select" name="fuel_type" required>
                                    <option value="petrol">Petrol</option>
                                    <option value="diesel">Diesel</option>
                                    <option value="electric">Electric</option>
                                    <option value="hybrid">Hybrid</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Seats</label>
                                <input type="number" class="form-control" name="seats" min="2" max="8" value="5" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Daily Rate (₹)</label>
                                <input type="number" class="form-control" name="daily_rate" min="0" step="0.01" required>
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label">Description</label>
                                <textarea class="form-control" name="description" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Vehicle</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>