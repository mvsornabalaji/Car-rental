-- Add payments table to existing car rental database
-- Run this if you already have the database set up

USE car_rental;

-- Create payments table if it doesn't exist
CREATE TABLE IF NOT EXISTS payments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    booking_id INT NOT NULL,
    payment_method ENUM('card', 'upi', 'netbanking', 'wallet', 'cash', 'emi') NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
    transaction_id VARCHAR(100),
    gateway_response TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings1(id) ON DELETE CASCADE
);

-- Insert some sample payment data (optional - uncomment if you want sample data)
-- INSERT INTO payments (booking_id, payment_method, amount, status, transaction_id) 
-- VALUES 
-- (1, 'card', 5000.00, 'completed', 'TXN1234567890'),
-- (2, 'upi', 3500.00, 'completed', 'TXN1234567891'),
-- (3, 'cash', 2500.00, 'pending', 'TXN1234567892');

-- Show table structure
DESCRIBE payments;

SELECT 'Payments table created successfully!' as message;