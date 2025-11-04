<?php
session_start();
include '../config/database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email_username = mysqli_real_escape_string($conn, $_POST['email_username']);
    $password = $_POST['password'];
    $login_type = $_POST['login_type']; // 'user' or 'admin'
    
    if ($login_type == 'admin') {
        // Admin login
        $query = "SELECT * FROM admin WHERE (username = '$email_username' OR email = '$email_username')";
        $result = mysqli_query($conn, $query);
        
        if (mysqli_num_rows($result) == 1) {
            $admin = mysqli_fetch_assoc($result);
            // Check password (assuming plain text for now, should be hashed)
            if ($password === $admin['password']) {
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_name'] = $admin['full_name'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['user_type'] = 'admin';
                
                header('Location: ../admin/dashboard.php');
                exit();
            } else {
                $error = 'Invalid credentials for admin login';
            }
        } else {
            $error = 'Invalid credentials for admin login';
        }
    } else {
        // User login
        $query = "SELECT * FROM users1 WHERE email = '$email_username' AND status = 'active'";
        $result = mysqli_query($conn, $query);
        
        if (mysqli_num_rows($result) == 1) {
            $user = mysqli_fetch_assoc($result);
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['full_name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_type'] = 'user';
                
                header('Location: dashboard.php');
                exit();
            } else {
                $error = 'Invalid email or password for user login';
            }
        } else {
            $error = 'Invalid email or password for user login';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Car Rental System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<style>
        body{
            background-image: url('../assets/img/backgro.jpg');
            min-height: 100vh;
        }
        .card {
            background: transparent;
            backdrop-filter: blur(10px);
            border-radius: 30px;
            box-shadow: 0 20px 45px rgba(208, 15, 15, 1);
            border: 2px solid white;
                   
        }
        .login-tab {
            border:none;
            background: transparent;
            color: #6c757d;
            transition: all 0.3s;
            border-radius: 8px;
        }
        .login-tab.active {
            background: #667eea;
            color:white;
            font-weight: bold;
        }
        .login-tab:hover {
            background: #e9ecef;
            color: #495057;
        }
        .login-tab.active:hover {
            background: #667eea;
            color: white;
        }
        .form-control {
            border-radius: 10px;
            border: 2px solid #e9ecef;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        </style>
<body class="bg-light">
    
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-car"></i> Trip Wheels
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="index.php">Back to Home</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-6 col-lg-4">
                <div class="card shadow">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <i class="fas fa-car fa-3x text-primary mb-3"></i>
                            <h3 class="card-title" style="color: #e9ecef;">CarRental Login</h3>
                            <p class="text-muted" style="color: #f8f9fa;">Sign in to your account</p>
                        </div>

                        <!-- Login Type Tabs -->
                        <div class="d-flex mb-3" style="background: #f8f9fa; border-radius: 10px; padding: 5px;">
                            <button type="button" class="btn btn-sm flex-fill login-tab active" onclick="switchTab('user')" id="userTab">
                                <i class="fas fa-user"></i> User Login
                            </button>
                            <button type="button" class="btn btn-sm flex-fill login-tab" onclick="switchTab('admin')" id="adminTab">
                                <i class="fas fa-shield-alt"></i> Admin Login
                            </button>
                        </div>

                        <?php if (isset($_GET['logout']) && $_GET['logout'] == 'success'): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle"></i> You have been successfully logged out.
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" class="needs-validation" novalidate>
                            <input type="hidden" name="login_type" id="loginType" value="user">
                            
                            <div class="mb-3">
                                <label for="email_username" class="form-label" id="emailLabel" style="color: #f8f9fa;">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-envelope" id="emailIcon"></i>
                                    </span>
                                    <input type="text" class="form-control" id="email_username" name="email_username" 
                                           placeholder="Enter your email" required>
                                </div>
                                <div class="invalid-feedback">
                                    Please enter your email address.
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label" style="color: #f8f9fa;">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-lock"></i>
                                    </span>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="invalid-feedback">
                                    Please enter your password.
                                </div>
                            </div>

                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="remember">
                                <label class="form-check-label" for="remember" style="color: #f8f9fa;">
                                    Remember me
                                </label>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-sign-in-alt"></i> <span id="loginButtonText">Login as User</span>
                                </button>
                            </div>
                        </form>

                        <div class="text-center mt-4" id="userLinks">
                            <a href="forgot_password.php" class="text-decoration-none me-3">
                                <i class="fas fa-key"></i> Forgot Password?
                            </a>
                            <a href="register.php" class="text-decoration-none">
                                <i class="fas fa-user-plus"></i> Create Account
                            </a>
                        </div>

                        <div class="text-center mt-4" id="adminLinks" style="display: none;">
                            <a href="index.php" class="text-decoration-none">
                                <i class="fas fa-arrow-left"></i> Back to Website
                            </a>
                        </div>

                        <hr class="my-4">

                       
                       
                          
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function switchTab(type) {
            // Update active tab
            document.querySelectorAll('.login-tab').forEach(tab => tab.classList.remove('active'));
            document.getElementById(type + 'Tab').classList.add('active');
            
            // Update form
            document.getElementById('loginType').value = type;
            
            if (type === 'admin') {
                document.getElementById('emailLabel').textContent = 'Username/Email';
                document.getElementById('email_username').placeholder = 'Enter username or email';
                document.getElementById('email_username').type = 'text';
                document.getElementById('emailIcon').className = 'fas fa-user';
                document.getElementById('loginButtonText').textContent = 'Login as Admin';
                document.getElementById('userLinks').style.display = 'none';
                document.getElementById('adminLinks').style.display = 'block';
            } else {
                document.getElementById('emailLabel').textContent = 'Email Address';
                document.getElementById('email_username').placeholder = 'Enter your email';
                document.getElementById('email_username').type = 'text';
                document.getElementById('emailIcon').className = 'fas fa-envelope';
                document.getElementById('loginButtonText').textContent = 'Login as User';
                document.getElementById('userLinks').style.display = 'block';
                document.getElementById('adminLinks').style.display = 'none';
            }
        }

        // Password toggle
        $('#togglePassword').click(function() {
            const password = $('#password');
            const icon = $(this).find('i');
            
            if (password.attr('type') === 'password') {
                password.attr('type', 'text');
                icon.removeClass('fa-eye').addClass('fa-eye-slash');
            } else {
                password.attr('type', 'password');
                icon.removeClass('fa-eye-slash').addClass('fa-eye');
            }
        });

        // Form validation
        (function() {
            'use strict';
            window.addEventListener('load', function() {
                var forms = document.getElementsByClassName('needs-validation');
                var validation = Array.prototype.filter.call(forms, function(form) {
                    form.addEventListener('submit', function(event) {
                        if (form.checkValidity() === false) {
                            event.preventDefault();
                            event.stopPropagation();
                        }
                        form.classList.add('was-validated');
                    }, false);
                });
            }, false);
        })();

        // Auto-fill demo credentials when clicked
        document.addEventListener('DOMContentLoaded', function() {
            const demoCard = document.querySelector('.card.bg-light');
            demoCard.addEventListener('click', function(e) {
                if (e.target.closest('.col-6:first-child')) {
                    // User demo credentials
                    switchTab('user');
                    document.getElementById('email_username').value = 'balaji@123';
                    document.getElementById('password').value = '9585740928';
                } else if (e.target.closest('.col-6:last-child')) {
                    // Admin demo credentials
                    switchTab('admin');
                    document.getElementById('email_username').value = 'admin';
                    document.getElementById('password').value = '12022003';
                }
            });
        });
    </script>
</body>
</html>
