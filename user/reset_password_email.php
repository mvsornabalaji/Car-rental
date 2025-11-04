<?php
// This file simulates sending a password reset email
// In a real application, you would integrate with an email service like PHPMailer, SendGrid, etc.

function sendPasswordResetEmail($email, $reset_token, $user_name) {
    $reset_link = "http://localhost:3000/user/forgot_password.php?step=2&token=" . $reset_token;
    
    // Email content
    $subject = "Password Reset Request - CarRental System";
    $message = "
    <html>
    <head>
        <title>Password Reset Request</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 10px 10px; }
            .button { display: inline-block; background: #667eea; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
            .footer { text-align: center; margin-top: 20px; color: #666; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>🚗 CarRental System</h2>
                <h3>Password Reset Request</h3>
            </div>
            <div class='content'>
                <p>Hello " . htmlspecialchars($user_name) . ",</p>
                
                <p>We received a request to reset your password for your CarRental account.</p>
                
                <p>Click the button below to reset your password:</p>
                
                <p style='text-align: center;'>
                    <a href='" . $reset_link . "' class='button'>Reset My Password</a>
                </p>
                
                <p>Or copy and paste this link into your browser:</p>
                <p style='word-break: break-all; background: #e9ecef; padding: 10px; border-radius: 5px;'>" . $reset_link . "</p>
                
                <p><strong>Important:</strong></p>
                <ul>
                    <li>This link will expire in 1 hour</li>
                    <li>If you didn't request this reset, please ignore this email</li>
                    <li>Your password will remain unchanged until you create a new one</li>
                </ul>
                
                <p>If you have any questions, please contact our support team.</p>
                
                <p>Best regards,<br>The CarRental Team</p>
            </div>
            <div class='footer'>
                <p>This is an automated message. Please do not reply to this email.</p>
                <p>© 2024 CarRental System. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    // Headers for HTML email
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: CarRental System <noreply@carrental.com>" . "\r\n";
    $headers .= "Reply-To: support@carrental.com" . "\r\n";
    
    // In a real application, you would use mail() function or a proper email service
    // For demo purposes, we'll just log the email content
    
    // Uncomment the line below to actually send emails (requires mail server configuration)
    // return mail($email, $subject, $message, $headers);
    
    // For demo, we'll save the email to a file
    $log_file = '../logs/password_reset_emails.log';
    $log_entry = date('Y-m-d H:i:s') . " - Email sent to: $email\n";
    $log_entry .= "Reset Link: $reset_link\n";
    $log_entry .= "Token: $reset_token\n";
    $log_entry .= str_repeat('-', 50) . "\n";
    
    // Create logs directory if it doesn't exist
    if (!file_exists('../logs')) {
        mkdir('../logs', 0777, true);
    }
    
    file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
    
    return true; // Simulate successful email sending
}

// Example usage:
// sendPasswordResetEmail('user@example.com', 'abc123token', 'John Doe');
?>