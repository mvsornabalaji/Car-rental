<?php
session_start();
include '../config/database.php';

$message = '';
$error = '';
$step = isset($_GET['step']) ? $_GET['step'] : 1;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'send_reset_link':
                $email = mysqli_real_escape_string($conn, $_POST['email']);
                
                // Check if email exists
                $user_query = "SELECT * FROM users1 WHERE email = '$email' AND status = 'active'";
                $user_result = mysqli_query($conn, $user_query);
                
                if (mysqli_num_rows($user_result) == 1) {
                    $user = mysqli_fetch_assoc($user_result);
                    
                    // Generate reset token
                    $reset_token = bin2hex(random_bytes(32));
                    $reset_expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
                    
                    // Update user with reset token
                    $update_query = "UPDATE users1 SET reset_token = '$reset_token', reset_token_expiry = '$reset_expiry' WHERE email = '$email'";
                    
                    if (mysqli_query($conn, $update_query)) {
                        // In a real application, you would send an email here
                        // For demo purposes, we'll show the reset link
                        $reset_link = "http://localhost:3000/user/forgot_password.php?step=2&token=" . $reset_token;
                        $message = "Password reset link generated! <br><strong>Demo Link:</strong> <a href='$reset_link' class='alert-link'>Click here to reset password</a>";
                        $step = 1; // Stay on step 1 to show the message
                    } else {
                        $error = "Error generating reset link. Please try again.";
                    }
                } else {
                    $error = "Email address not found or account is inactive.";
                }
                break;
                
            case 'reset_password':
                $token = mysqli_real_escape_string($conn, $_POST['token']);
                $new_password = $_POST['new_password'];
                $confirm_password = $_POST['confirm_password'];
                
                if ($new_password !== $confirm_password) {
                    $error = "Passwords do not match.";
                } elseif (strlen($new_password) < 6) {
                    $error = "Password must be at least 6 characters long.";
                } else {
                    // Verify token and check expiry
                    $token_query = "SELECT * FROM users1 WHERE reset_token = '$token' AND reset_token_expiry > NOW() AND status = 'active'";
                    $token_result = mysqli_query($conn, $token_query);
                    
                    if (mysqli_num_rows($token_result) == 1) {
                        $user = mysqli_fetch_assoc($token_result);
                        
                        // Hash new password
                        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                        
                        // Update password and clear reset token
                        $update_query = "UPDATE users1 SET password = '$hashed_password', reset_token = NULL, reset_token_expiry = NULL WHERE id = " . $user['id'];
                        
                        if (mysqli_query($conn, $update_query)) {
                            $message = "Password reset successfully! You can now login with your new password.";
                            $step = 3; // Success step
                        } else {
                            $error = "Error updating password. Please try again.";
                        }
                    } else {
                        $error = "Invalid or expired reset token. Please request a new password reset.";
                    }
                }
                break;
        }
    }
}

// Handle step 2 (reset form) - check token validity
if ($step == 2 && isset($_GET['token'])) {
    $token = mysqli_real_escape_string($conn, $_GET['token']);
    $token_query = "SELECT * FROM users1 WHERE reset_token = '$token' AND reset_token_expiry > NOW() AND status = 'active'";
    $token_result = mysqli_query($conn, $token_query);
    
    if (mysqli_num_rows($token_result) == 0) {
        $error = "Invalid or expired reset token. Please request a new password reset.";
        $step = 1;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Car Rental System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .forgot-password-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            border: none;
        }
        .step-indicator {
            display: flex;
            justify-content: center;
            margin-bottom: 2rem;
        }
        .step {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 10px;
            background: #e9ecef;
            color: #6c757d;
            font-weight: bold;
            position: relative;
        }
        .step.active {
            background: #667eea;
            color: white;
        }
        .step.completed {
            background: #28a745;
            color: white;
        }
        .step:not(:last-child)::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 100%;
            width: 20px;
            height: 2px;
            background: #e9ecef;
            transform: translateY(-50%);
        }
        .step.completed:not(:last-child)::after {
            background: #28a745;
        }
        .form-control {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 12px 15px;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 12px;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card forgot-password-card">
                    <div class="card-body p-5">
                        <!-- Header -->
                        <div class="text-center mb-4">
                            <i class="fas fa-key fa-3x text-primary mb-3"></i>
                            <h3>Forgot Password</h3>
                            <p class="text-muted">
                                <?php if ($step == 1): ?>
                                    Enter your email to receive a password reset link
                                <?php elseif ($step == 2): ?>
                                    Enter your new password
                                <?php else: ?>
                                    Password reset completed
                                <?php endif; ?>
                            </p>
                        </div>

                        <!-- Step Indicator -->
                        <div class="step-indicator">
                            <div class="step <?php echo $step >= 1 ? ($step > 1 ? 'completed' : 'active') : ''; ?>">1</div>
                            <div class="step <?php echo $step >= 2 ? ($step > 2 ? 'completed' : 'active') : ''; ?>">2</div>
                            <div class="step <?php echo $step >= 3 ? 'active' : ''; ?>">3</div>
                        </div>

                        <!-- Messages -->
                        <?php if ($message): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle"></i> <?php echo $message; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <?php if ($step == 1): ?>
                            <!-- Step 1: Email Input -->
                            <form method="POST">
                                <input type="hidden" name="action" value="send_reset_link">
                                <div class="mb-4">
                                    <label for="email" class="form-label">Email Address</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-envelope"></i>
                                        </span>
                                        <input type="email" class="form-control" id="email" name="email" 
                                               placeholder="Enter your registered email" required>
                                    </div>
                                </div>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-paper-plane"></i> Send Reset Link
                                    </button>
                                </div>
                            </form>

                        <?php elseif ($step == 2): ?>
                            <!-- Step 2: New Password -->
                            <form method="POST" id="resetForm">
                                <input type="hidden" name="action" value="reset_password">
                                <input type="hidden" name="token" value="<?php echo htmlspecialchars($_GET['token']); ?>">
                                
                                <div class="mb-3">
                                    <label for="new_password" class="form-label">New Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-lock"></i>
                                        </span>
                                        <input type="password" class="form-control" id="new_password" name="new_password" 
                                               placeholder="Enter new password" minlength="6" required>
                                        <button class="btn btn-outline-secondary" type="button" id="toggleNewPassword">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <small class="text-muted">Minimum 6 characters</small>
                                </div>

                                <div class="mb-4">
                                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-lock"></i>
                                        </span>
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                               placeholder="Confirm new password" minlength="6" required>
                                        <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-save"></i> Reset Password
                                    </button>
                                </div>
                            </form>

                        <?php else: ?>
                            <!-- Step 3: Success -->
                            <div class="text-center">
                                <div class="mb-4">
                                    <i class="fas fa-check-circle fa-4x text-success"></i>
                                </div>
                                <h4 class="text-success">Password Reset Successful!</h4>
                                <p class="text-muted mb-4">Your password has been successfully reset. You can now login with your new password.</p>
                                <div class="d-grid">
                                    <a href="login.php" class="btn btn-primary btn-lg">
                                        <i class="fas fa-sign-in-alt"></i> Go to Login
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Back to Login -->
                        <?php if ($step != 3): ?>
                            <div class="text-center mt-4">
                                <a href="login.php" class="text-decoration-none">
                                    <i class="fas fa-arrow-left"></i> Back to Login
                                </a>
                            </div>
                        <?php endif; ?>

                        <!-- Demo Info -->
                        <?php if ($step == 1): ?>
                            <div class="card bg-light mt-4">
                                <div class="card-body p-3">
                                    <h6 class="card-title mb-2">Demo Email:</h6>
                                    <small>Use <strong>balaji@123</strong> to test the forgot password feature</small>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Password toggle functionality
        $('#toggleNewPassword').click(function() {
            const password = $('#new_password');
            const icon = $(this).find('i');
            
            if (password.attr('type') === 'password') {
                password.attr('type', 'text');
                icon.removeClass('fa-eye').addClass('fa-eye-slash');
            } else {
                password.attr('type', 'password');
                icon.removeClass('fa-eye-slash').addClass('fa-eye');
            }
        });

        $('#toggleConfirmPassword').click(function() {
            const password = $('#confirm_password');
            const icon = $(this).find('i');
            
            if (password.attr('type') === 'password') {
                password.attr('type', 'text');
                icon.removeClass('fa-eye').addClass('fa-eye-slash');
            } else {
                password.attr('type', 'password');
                icon.removeClass('fa-eye-slash').addClass('fa-eye');
            }
        });

        // Password confirmation validation
        $('#confirm_password').on('input', function() {
            const newPassword = $('#new_password').val();
            const confirmPassword = $(this).val();
            
            if (newPassword !== confirmPassword) {
                this.setCustomValidity('Passwords do not match');
            } else {
                this.setCustomValidity('');
            }
        });

        // Auto-fill demo email when clicked
        $(document).ready(function() {
            $('.card.bg-light').click(function() {
                $('#email').val('balaji@123');
            });
        });
    </script>
</body>
</html>