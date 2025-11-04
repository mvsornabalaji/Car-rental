<?php
session_start();
include '../config/database.php';

// Get about page content from database
$query = "SELECT * FROM page_content WHERE page_name = 'about'";
$result = mysqli_query($conn, $query);
$about_content = mysqli_fetch_assoc($result);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Car Rental System</title>
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
                        <a class="nav-link" href="vehicles.php">Vehicles</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="about.php">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.php">Contact</a>
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
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="/logout.php">Logout</a></li>
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
                    <h1 class="display-4 fw-bold">About Us</h1>
                    <p class="lead">Learn more about our company and mission</p>
                </div>
            </div>
        </div>
    </section>

    <div class="container py-5">
        <!-- Company Story -->
        <div class="row mb-5">
            <div class="col-lg-6">
                <h2 class="text-primary mb-4">Our Story</h2>
                <p class="lead">Founded with a vision to provide reliable and affordable car rental services, Trip Wheels has been serving customers since 2023.</p>
                <p>We understand that every journey is unique, and that's why we offer a diverse fleet of vehicles to meet your specific needs. Whether you're traveling for business or pleasure, we have the perfect vehicle for you.</p>
                <p>Our commitment to quality, safety, and customer satisfaction has made us a trusted name in the car rental industry. We continuously strive to improve our services and provide the best experience for our customers.</p>
            </div>
            <div class="col-lg-6">
                <div class="bg-light p-4 rounded">
                    <h4 class="text-primary mb-3">Why Choose Us?</h4>
                    <ul class="list-unstyled">
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Wide selection of vehicles</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Competitive pricing</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> 24/7 customer support</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Flexible booking options</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Well-maintained vehicles</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Easy online booking</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Mission & Vision -->
        <div class="row mb-5">
            <div class="col-lg-6 mb-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                            <i class="fas fa-bullseye fa-2x"></i>
                        </div>
                        <h4 class="card-title">Our Mission</h4>
                        <p class="card-text">To provide reliable, affordable, and convenient car rental services that exceed customer expectations while maintaining the highest standards of safety and quality.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 mb-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <div class="bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                            <i class="fas fa-eye fa-2x"></i>
                        </div>
                        <h4 class="card-title">Our Vision</h4>
                        <p class="card-text">To become the leading car rental service provider, known for innovation, customer satisfaction, and sustainable business practices.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h3 class="text-center mb-4">Our Numbers</h3>
                        <div class="row text-center">
                            <div class="col-md-3 mb-3">
                                <h2 class="fw-bold">500+</h2>
                                <p>Happy Customers</p>
                            </div>
                            <div class="col-md-3 mb-3">
                                <h2 class="fw-bold">50+</h2>
                                <p>Vehicles</p>
                            </div>
                            <div class="col-md-3 mb-3">
                                <h2 class="fw-bold">1000+</h2>
                                <p>Successful Rentals</p>
                            </div>
                            <div class="col-md-3 mb-3">
                                <h2 class="fw-bold">5+</h2>
                                <p>Years Experience</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Team -->
        <div class="row mb-5">
            <div class="col-12">
                <h2 class="text-center text-primary mb-5">Our Team</h2>
                <div class="row">
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="card text-center border-0 shadow-sm">
                            <div class="card-body p-4">
                                <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 100px; height: 100px;">
                                    <i class="fas fa-user-tie fa-3x text-primary"></i>
                                </div>
                                <h5 class="card-title">John Doe</h5>
                                <p class="text-muted">CEO & Founder</p>
                                <p class="card-text">With over 10 years of experience in the automotive industry, John leads our company with vision and expertise.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="card text-center border-0 shadow-sm">
                            <div class="card-body p-4">
                                <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 100px; height: 100px;">
                                    <i class="fas fa-user-cog fa-3x text-primary"></i>
                                </div>
                                <h5 class="card-title">Jane Smith</h5>
                                <p class="text-muted">Operations Manager</p>
                                <p class="card-text">Jane ensures smooth operations and maintains our high standards of service quality.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="card text-center border-0 shadow-sm">
                            <div class="card-body p-4">
                                <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 100px; height: 100px;">
                                    <i class="fas fa-headset fa-3x text-primary"></i>
                                </div>
                                <h5 class="card-title">Mike Johnson</h5>
                                <p class="text-muted">Customer Support</p>
                                <p class="card-text">Mike and his team provide exceptional customer service and support to all our clients.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    
    

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="assets/js/script.js"></script>
</body>
</html>
