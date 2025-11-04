<?php
session_start();
include '../config/database.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Car Rental System - Find Your Perfect Ride</title>
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
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user"></i> <?php echo $_SESSION['user_name']; ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="dashboard.php">Dashboard</a></li>
                                <li><a class="dropdown-item" href="bookings.php">My Bookings</a></li>
                                <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                                <li><a class="dropdown-item" href="feedback.php">Feedback</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="logout.php">Logout</a></li>
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

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center min-vh-100">
                <div class="col-lg-6">
                    <h1 class="display-4 fw-bold text-white mb-4">
                        Find Your Perfect Ride
                    </h1>
                    <p class="lead text-white mb-4">
                        Choose from our wide selection of vehicles for your journey. 
                        Whether it's a business trip or vacation, we have the perfect car for you.
                    </p>
                    <a href="vehicles.php" class="btn btn-primary btn-lg me-3">
                        <i class="fas fa-search"></i> Browse Vehicles
                    </a>
                    <a href="register.php" class="btn btn-outline-light btn-lg">
                        <i class="fas fa-user-plus"></i> Join Now
                    </a>
                </div>
                
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row text-center mb-5">
                <div class="col-lg-8 mx-auto">
                    <h2 class="display-5 fw-bold">Why Choose Us?</h2>
                    <p class="lead text-muted">We provide the best car rental experience with quality vehicles and excellent service.</p>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <div class="feature-icon mb-3">
                                <i class="fas fa-car text-primary fa-3x"></i>
                            </div>
                            <h5 class="card-title">Wide Selection</h5>
                            <p class="card-text">Choose from economy to luxury vehicles to match your needs and budget.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <div class="feature-icon mb-3">
                                <i class="fas fa-shield-alt text-primary fa-3x"></i>
                            </div>
                            <h5 class="card-title">Safe & Reliable</h5>
                            <p class="card-text">All our vehicles are regularly maintained and insured for your safety.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <div class="feature-icon mb-3">
                                <i class="fas fa-clock text-primary fa-3x"></i>
                            </div>
                            <h5 class="card-title">24/7 Support</h5>
                            <p class="card-text">Round-the-clock customer support to assist you anytime, anywhere.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Popular Vehicles Section -->
    <section class="py-5">
        <div class="container">
            <div class="row text-center mb-5">
                <div class="col-lg-8 mx-auto">
                    <h2 class="display-5 fw-bold">Popular Vehicles</h2>
                    <p class="lead text-muted">Check out our most popular vehicles</p>
                </div>
            </div>
            <div class="row g-4">
                <?php
                $query = "SELECT v.*, vb.brand_name FROM vehicles v 
                         JOIN vehicle_brands vb ON v.brand_id = vb.id 
                         WHERE v.status = 'active' 
                         ORDER BY v.id DESC LIMIT 6";
                $result = mysqli_query($conn, $query);
                while($vehicle = mysqli_fetch_assoc($result)):
                ?>
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 border-0 shadow-sm">
                        <img src="../assets/img/<?php echo $vehicle['image']; ?>" class="card-img-top" alt="<?php echo $vehicle['model']; ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $vehicle['brand_name'] . ' ' . $vehicle['model']; ?></h5>
                            <p class="card-text text-muted"><?php echo $vehicle['description']; ?></p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="h5 text-primary mb-0">$<?php echo $vehicle['daily_rate']; ?>/day</span>
                                <a href="vehicle_details.php?id=<?php echo $vehicle['id']; ?>" class="btn btn-outline-primary">View Details</a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
            <div class="text-center mt-4">
                <a href="vehicles.php" class="btn btn-primary btn-lg">View All Vehicles</a>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row text-center mb-5">
                <div class="col-lg-8 mx-auto">
                    <h2 class="display-5 fw-bold">What Our Customers Say</h2>
                    <p class="lead text-muted">Read testimonials from our satisfied customers</p>
                </div>
            </div>
            <div class="row g-4">
                <?php
                $query = "SELECT * FROM testimonials WHERE status = 'active' ORDER BY id DESC LIMIT 3";
                $result = mysqli_query($conn, $query);
                while($testimonial = mysqli_fetch_assoc($result)):
                ?>
                <div class="col-lg-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <div class="mb-3">
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                            </div>
                            <p class="card-text">"<?php echo $testimonial['message']; ?>"</p>
                            <h6 class="card-title mb-0"><?php echo $testimonial['customer_name']; ?></h6>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4">
                    <h5><i class="fas fa-car"></i> Trip Wheels</h5>
                    <p class="text-muted">Your trusted partner for car rentals. Quality vehicles, excellent service, and competitive prices.</p>
                    <div class="social-links">
                        <a href="#" class="text-white me-3"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-linkedin"></i></a>
                    </div>
                </div>
                <div class="col-lg-2">
                    <h6>Quick Links</h6>
                    <ul class="list-unstyled">
                        <li><a href="index.php" class="text-muted">Home</a></li>
                        <li><a href="vehicles.php" class="text-muted">Vehicles</a></li>
                        <li><a href="about.php" class="text-muted">About</a></li>
                        <li><a href="contact.php" class="text-muted">Contact</a></li>
                    </ul>
                </div>
                <div class="col-lg-3">
                    <h6>Contact Info</h6>
                    <ul class="list-unstyled text-muted">
                        <li><i class="fas fa-map-marker-alt me-2"></i> 123 Main,NGO Colony,Near by New bus stand,Tirunelveli  </li>
                        <li><i class="fas fa-phone me-2"></i> +1 234 567 8900</li>
                        <li><i class="fas fa-envelope me-2"></i> info@tripwheels.com</li>
                    </ul>
                </div>
                <div class="col-lg-3">
                    <h6>Newsletter</h6>
                    <p class="text-muted">Subscribe to our newsletter for updates and offers.</p>
                    <form class="d-flex">
                        <input type="email" class="form-control me-2" placeholder="Your email">
                        <button type="submit" class="btn btn-primary">Subscribe</button>
                    </form>
                </div>
            </div>
            <hr class="my-4">
            <div class="row">
                <div class="col-md-6">
                    <p class="text-muted mb-0">&copy; 2025 Trip Wheels. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="terms.php" class="text-muted me-3">Terms & Conditions</a>
                    <a href="privacy.php" class="text-muted">Privacy Policy</a>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="assets/js/script.js"></script>
</body>
</html>
