<?php
session_start();
include '../config/database.php';

// Get search and filter parameters
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$brand_filter = isset($_GET['brand']) ? (int)$_GET['brand'] : '';
$price_min = isset($_GET['price_min']) ? (float)$_GET['price_min'] : '';
$price_max = isset($_GET['price_max']) ? (float)$_GET['price_max'] : '';
$transmission = isset($_GET['transmission']) ? mysqli_real_escape_string($conn, $_GET['transmission']) : '';
$fuel_type = isset($_GET['fuel_type']) ? mysqli_real_escape_string($conn, $_GET['fuel_type']) : '';

// Build query
$query = "SELECT v.*, vb.brand_name FROM vehicles v 
          JOIN vehicle_brands vb ON v.brand_id = vb.id 
          WHERE v.status = 'available'";

if ($search) {
    $query .= " AND (vb.brand_name LIKE '%$search%' OR v.model LIKE '%$search%' OR v.description LIKE '%$search%')";
}

if ($brand_filter) {
    $query .= " AND v.brand_id = $brand_filter";
}

if ($price_min !== '') {
    $query .= " AND v.daily_rate >= $price_min";
}

if ($price_max !== '') {
    $query .= " AND v.daily_rate <= $price_max";
}

if ($transmission) {
    $query .= " AND v.transmission = '$transmission'";
}

if ($fuel_type) {
    $query .= " AND v.fuel_type = '$fuel_type'";
}

$query .= " ORDER BY v.daily_rate ASC";

$result = mysqli_query($conn, $query);

// Get brands for filter
$brands_query = "SELECT * FROM vehicle_brands WHERE status = 'active' ORDER BY brand_name";
$brands_result = mysqli_query($conn, $brands_query);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vehicles - Car Rental System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-car"></i> Trip Wheels
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="vehicles.php">Vehicles</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="all_feedback.php">Reviews</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="about.php">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.php">Contact</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user"></i> <?php echo $_SESSION['user_name']; ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="dashboard.php">Dashboard</a></li>
                                <li><a class="dropdown-item" href="bookings.php">My Bookings</a></li>
                                <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                                <li><a class="dropdown-item" href="feedback.php">Feedback</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item" href="../logout.php">Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php">Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Page Header -->
    <section class="bg-primary text-white py-5" style="margin-top: 76px;">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto text-center">
                    <h1 class="display-4 fw-bold">Our Vehicles</h1>
                    <p class="lead">Choose from our wide selection of quality vehicles</p>
                </div>
            </div>
        </div>
    </section>

    <div class="container py-5">
        <!-- Search and Filter Section -->
        <div class="row mb-4">
            <div class="col-lg-3">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-filter"></i> Filters</h5>
                    </div>
                    <div class="card-body">
                        <form method="GET" id="filter_form">
                            <!-- Search -->
                            <div class="mb-3">
                                <label for="search" class="form-label">Search</label>
                                <input type="text" class="form-control" id="search" name="search"
                                    value="<?php echo htmlspecialchars($search); ?>" placeholder="Search vehicles...">
                            </div>

                            <!-- Brand Filter -->
                            <div class="mb-3">
                                <label for="brand" class="form-label">Brand</label>
                                <select class="form-select filter-option" id="brand" name="brand">
                                    <option value="">All Brands</option>
                                    <?php while ($brand = mysqli_fetch_assoc($brands_result)): ?>
                                        <option value="<?php echo $brand['id']; ?>"
                                            <?php echo $brand_filter == $brand['id'] ? 'selected' : ''; ?>>
                                            <?php echo $brand['brand_name']; ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <!-- Price Range -->
                            <div class="mb-3">
                                <label for="price_min" class="form-label">Min Price</label>
                                <input type="number" class="form-control filter-option" id="price_min" name="price_min"
                                    value="<?php echo $price_min; ?>" min="0" step="0.01">
                            </div>

                            <div class="mb-3">
                                <label for="price_max" class="form-label">Max Price</label>
                                <input type="number" class="form-control filter-option" id="price_max" name="price_max"
                                    value="<?php echo $price_max; ?>" min="0" step="0.01">
                            </div>

                            <!-- Transmission -->
                            <div class="mb-3">
                                <label for="transmission" class="form-label">Transmission</label>
                                <select class="form-select filter-option" id="transmission" name="transmission">
                                    <option value="">All</option>
                                    <option value="automatic" <?php echo $transmission == 'automatic' ? 'selected' : ''; ?>>Automatic</option>
                                    <option value="manual" <?php echo $transmission == 'manual' ? 'selected' : ''; ?>>Manual</option>
                                </select>
                            </div>

                            <!-- Fuel Type -->
                            <div class="mb-3">
                                <label for="fuel_type" class="form-label">Fuel Type</label>
                                <select class="form-select filter-option" id="fuel_type" name="fuel_type">
                                    <option value="">All</option>
                                    <option value="petrol" <?php echo $fuel_type == 'petrol' ? 'selected' : ''; ?>>Petrol</option>
                                    <option value="diesel" <?php echo $fuel_type == 'diesel' ? 'selected' : ''; ?>>Diesel</option>
                                    <option value="electric" <?php echo $fuel_type == 'electric' ? 'selected' : ''; ?>>Electric</option>
                                    <option value="hybrid" <?php echo $fuel_type == 'hybrid' ? 'selected' : ''; ?>>Hybrid</option>
                                </select>
                            </div>

                            <!-- Clear Filters -->
                            <div class="d-grid">
                                <a href="vehicles.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-times"></i> Clear Filters
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Vehicles Grid -->
            <div class="col-lg-9">
                <div class="row g-4">
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php while ($vehicle = mysqli_fetch_assoc($result)): ?>
                            <div class="col-lg-4 col-md-6">
                                <div class="card h-100 shadow-sm">
                                    <img src="../assets/img/<?php echo $vehicle['image']; ?>"
                                        class="card-img-top" alt="<?php echo $vehicle['image']; ?>">
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo $vehicle['brand_name'] . ' ' . $vehicle['model']; ?></h5>
                                        <p class="card-text text-muted"><?php echo substr($vehicle['description'], 0, 100) . '...'; ?></p>

                                        <div class="row mb-3">
                                            <div class="col-6">
                                                <small class="text-muted">
                                                    <i class="fas fa-calendar"></i> <?php echo $vehicle['year']; ?>
                                                </small>
                                            </div>
                                            <div class="col-6">
                                                <small class="text-muted">
                                                    <i class="fas fa-palette"></i> <?php echo ucfirst($vehicle['color']); ?>
                                                </small>
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-6">
                                                <small class="text-muted">
                                                    <i class="fas fa-cog"></i> <?php echo ucfirst($vehicle['transmission']); ?>
                                                </small>
                                            </div>
                                            <div class="col-6">
                                                <small class="text-muted">
                                                    <i class="fas fa-gas-pump"></i> <?php echo ucfirst($vehicle['fuel_type']); ?>
                                                </small>
                                            </div>
                                        </div>

                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <span class="h5 text-primary mb-0">₹<?php echo $vehicle['daily_rate']; ?></span>
                                                <small class="text-muted">/day</small>
                                            </div>
                                            <div>
                                                <a href="vehicle_details.php?id=<?php echo $vehicle['id']; ?>"
                                                    class="btn btn-outline-primary btn-sm">
                                                    <i class="fas fa-eye"></i> View Details
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="col-12">
                            <div class="text-center py-5">
                                <i class="fas fa-car fa-3x text-muted mb-3"></i>
                                <h4 class="text-muted">No vehicles found</h4>
                                <p class="text-muted">Try adjusting your search criteria or filters.</p>
                                <a href="vehicles.php" class="btn btn-primary">
                                    <i class="fas fa-refresh"></i> Clear All Filters
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>



    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="assets/js/script.js"></script>
</body>

</html>