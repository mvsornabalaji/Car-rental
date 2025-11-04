-- Add feedback table to the car rental database
-- This table will store user feedback and ratings

CREATE TABLE IF NOT EXISTS feedback (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    booking_id INT DEFAULT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    feedback_type ENUM('general', 'service', 'vehicle', 'booking', 'complaint', 'suggestion') DEFAULT 'general',
    status ENUM('pending', 'reviewed', 'resolved', 'closed') DEFAULT 'pending',
    admin_response TEXT DEFAULT NULL,
    admin_id INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users1(id) ON DELETE CASCADE,
    FOREIGN KEY (booking_id) REFERENCES bookings1(id) ON DELETE SET NULL,
    FOREIGN KEY (admin_id) REFERENCES users1(id) ON DELETE SET NULL
);

-- Add indexes for better performance
CREATE INDEX idx_feedback_user_id ON feedback(user_id);
CREATE INDEX idx_feedback_status ON feedback(status);
CREATE INDEX idx_feedback_type ON feedback(feedback_type);
CREATE INDEX idx_feedback_created_at ON feedback(created_at);

-- Insert sample feedback data
INSERT INTO feedback (user_id, rating, subject, message, feedback_type, status) VALUES
(2, 5, 'Excellent Service', 'The car rental service was outstanding. Clean vehicle and professional staff.', 'service', 'reviewed'),
(3, 4, 'Good Experience', 'Overall good experience, but the pickup process could be faster.', 'general', 'pending'),
(2, 3, 'Vehicle Issue', 'The car had some minor scratches that were not mentioned during booking.', 'vehicle', 'pending'),
(3, 5, 'Highly Recommended', 'Will definitely use this service again. Great value for money!', 'general', 'reviewed');