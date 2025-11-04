-- Car Rental System Database Schema

-- Create database
CREATE DATABASE IF NOT EXISTS car_rental;
USE car_rental;

-- Admin table
CREATE TABLE admin (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Users table
CREATE TABLE users1 (
    id INT PRIMARY KEY AUTO_INCREMENT,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    license_number VARCHAR(50),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Vehicle brands table
CREATE TABLE vehicle_brands (
    id INT PRIMARY KEY AUTO_INCREMENT,
    brand_name VARCHAR(100) NOT NULL,
    description TEXT,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Vehicles table
CREATE TABLE vehicles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    brand_id INT NOT NULL,
    model VARCHAR(100) NOT NULL,
    year INT NOT NULL,
    color VARCHAR(50),
    transmission ENUM('manual', 'automatic') DEFAULT 'automatic',
    fuel_type ENUM('petrol', 'diesel', 'electric', 'hybrid') DEFAULT 'petrol',
    seats INT DEFAULT 5,
    daily_rate DECIMAL(10) NOT NULL,
    description TEXT,
    image VARCHAR(255),
    status ENUM('available', 'rented', 'maintenance', 'inactive') DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (brand_id) REFERENCES vehicle_brands(id) ON DELETE CASCADE
);

-- Bookings table
CREATE TABLE bookings1 (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    vehicle_id INT NOT NULL,
    pickup_date DATE NOT NULL,
    return_date DATE NOT NULL,
    total_amount DECIMAL(10) NOT NULL,
    status ENUM('pending', 'confirmed', 'cancelled', 'completed') DEFAULT 'pending',
    booking_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    admin_notes TEXT,
    FOREIGN KEY (user_id) REFERENCES users1(id) ON DELETE CASCADE,
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE
);

-- Testimonials table
CREATE TABLE testimonials (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    customer_name VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    rating INT DEFAULT 5,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users1(id) ON DELETE SET NULL
);

-- Contact queries table
CREATE TABLE contact_queries (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    subject VARCHAR(200),
    message TEXT NOT NULL,
    status ENUM('new', 'read', 'replied') DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Subscribers table
CREATE TABLE subscribers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(100) UNIQUE NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Payments table
CREATE TABLE payments (
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

-- Payments table
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

-- Page content table
CREATE TABLE page_content (
    id INT PRIMARY KEY AUTO_INCREMENT,
    page_name VARCHAR(100) UNIQUE NOT NULL,
    title VARCHAR(200),
    content TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default admin
INSERT INTO admin (username, email, password, full_name) VALUES 
('admin', 'sruthi23@gmail.com', '23092003', 'System Administrator');
SELECT *FROM admin;
DELETE FROM admin;
INSERT INTO users1 
(full_name, email, password, phone, address, license_number, status) 
VALUES 
('John Doe', 'balaji@123', '9585740928', '123-456-7890', '123 Main St, Cityville', 'LIC123456', 'active');
SELECT * FROM users1;
SELECT* FROM admin;

SELECT 'Payments table created successfully!' as message;

-- Insert sample vehicle brands
INSERT INTO vehicle_brands (brand_name, description) VALUES 
('Toyota', 'Reliable and fuel-efficient vehicles'),
('Honda', 'Quality cars with excellent performance'),
('Ford', 'American automotive excellence'),
('BMW', 'Luxury and performance combined'),
('Mercedes-Benz', 'Premium German engineering'),
('Audi', 'Innovation and sophistication');

-- Insert sample vehicles
INSERT INTO vehicles 
(brand_id, model, year, color, transmission, fuel_type, seats, daily_rate, image, description, status) 
VALUES 
(1, 'Camry', 2023, 'White', 'automatic', 'petrol', 5, 2000, 'Camry.jpg', 'Comfortable sedan perfect for daily use', 'available'),
(1, 'Corolla', 2022, 'Silver', 'automatic', 'petrol', 5, 2500, 'Corolla.jpg', 'Economical and reliable compact car', 'available'),
(2, 'Civic', 2023, 'Blue', 'automatic', 'petrol', 5, 3500, 'Civic.jpeg', 'Sporty and efficient compact sedan', 'available'),
(3, 'Focus', 2022, 'Red', 'manual', 'petrol', 5, 6500, 'Forces.png', 'Fun to drive compact car', 'available'),
(4, '3 Series', 2023, 'Black', 'automatic', 'petrol', 5, 8000, '3 Series.jpg', 'Luxury sedan with premium features', 'available'),
(5, 'C-Class', 2023, 'White', 'automatic', 'petrol', 5, 5500, 'C-Class.jpg', 'Elegant luxury sedan', 'available');


-- Insert sample page content
INSERT INTO page_content (page_name, title, content) VALUES 
('about', 'About Us', 'We are a leading car rental company providing quality vehicles and excellent service to our customers.'),
('terms', 'Terms and Conditions', 'Please read our terms and conditions carefully before using our services.'),
('privacy', 'Privacy Policy', 'We respect your privacy and are committed to protecting your personal information.'),
('contact', 'Contact Information', 'Get in touch with us for any queries or support.');

-- Insert sample testimonials
INSERT INTO testimonials (customer_name, message, rating, status) VALUES 
('Maharajan', 'Excellent service and very clean cars. Highly recommended!', 5, 'active'),
('Dk', 'Great experience renting from this company. Will definitely use again.', 5, 'active'),
('Ajay', 'Professional staff and quality vehicles. Very satisfied with the service.', 5, 'active');
 INSERT INTO payments (booking_id, payment_method, amount, status, transaction_id) 
 VALUES 
(1, 'card', 5000.00, 'completed', 'TXN1234567890'),
(2, 'upi', 3500.00, 'completed', 'TXN1234567891'),
(3, 'cash', 2500.00, 'pending', 'TXN1234567892');
-- ALTER TABLE users1 ADD COLUMN reset_token VARCHAR(100) DEFAULT NULL;
ALTER TABLE users1 ADD COLUMN reset_token_expiry DATETIME DEFAULT NULL;
ALTER TABLE users1 
ADD COLUMN IF NOT EXISTS reset_token VARCHAR(100) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS reset_token_expiry DATETIME DEFAULT NULL;
DESCRIBE users1;
SELECT 'Reset token columns added successfully!' as message;
DELETE FROM vehicles