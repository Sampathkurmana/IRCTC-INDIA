-- ============================================
-- Indian Railway Reservation System Database
-- Import this into phpMyAdmin
-- ============================================

CREATE DATABASE IF NOT EXISTS railway_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE railway_system;

-- -----------------------------------------------
-- Users Table
-- -----------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(15) NOT NULL,
    role ENUM('user','admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- -----------------------------------------------
-- Trains Table
-- -----------------------------------------------
CREATE TABLE IF NOT EXISTS trains (
    id INT AUTO_INCREMENT PRIMARY KEY,
    train_number VARCHAR(10) UNIQUE NOT NULL,
    train_name VARCHAR(150) NOT NULL,
    source VARCHAR(100) NOT NULL,
    destination VARCHAR(100) NOT NULL,
    departure_time TIME NOT NULL,
    arrival_time TIME NOT NULL,
    total_seats INT NOT NULL DEFAULT 100,
    available_seats INT NOT NULL DEFAULT 100,
    price DECIMAL(10,2) NOT NULL,
    train_type ENUM('Express','Superfast','Rajdhani','Shatabdi','Local','Duronto') DEFAULT 'Express',
    days_of_operation VARCHAR(50) DEFAULT 'Mon,Tue,Wed,Thu,Fri,Sat,Sun',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- -----------------------------------------------
-- Bookings Table
-- -----------------------------------------------
CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    train_id INT NOT NULL,
    journey_date DATE NOT NULL,
    seat_count INT NOT NULL DEFAULT 1,
    total_amount DECIMAL(10,2) NOT NULL,
    pnr_number VARCHAR(20) UNIQUE NOT NULL,
    booking_status ENUM('confirmed','cancelled','waiting') DEFAULT 'confirmed',
    payment_status ENUM('paid','pending','refunded') DEFAULT 'paid',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (train_id) REFERENCES trains(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- -----------------------------------------------
-- Passengers Table
-- -----------------------------------------------
CREATE TABLE IF NOT EXISTS passengers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    age INT NOT NULL,
    gender ENUM('Male','Female','Other') NOT NULL,
    seat_number VARCHAR(10),
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- -----------------------------------------------
-- Default Admin Account
-- Password: admin123
-- -----------------------------------------------
INSERT INTO users (name, email, password, phone, role) VALUES
('Admin', 'admin@railway.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '9000000000', 'admin');

-- -----------------------------------------------
-- Sample Trains Data
-- -----------------------------------------------
INSERT INTO trains (train_number, train_name, source, destination, departure_time, arrival_time, total_seats, available_seats, price, train_type, days_of_operation) VALUES
('12301', 'Howrah Rajdhani', 'New Delhi', 'Howrah', '16:55:00', '09:55:00', 200, 200, 1200.00, 'Rajdhani', 'Mon,Tue,Wed,Thu,Fri,Sat,Sun'),
('12302', 'New Delhi Rajdhani', 'Howrah', 'New Delhi', '13:50:00', '07:55:00', 200, 200, 1200.00, 'Rajdhani', 'Mon,Tue,Wed,Thu,Fri,Sat,Sun'),
('12951', 'Mumbai Rajdhani', 'New Delhi', 'Mumbai Central', '16:25:00', '08:35:00', 250, 250, 1500.00, 'Rajdhani', 'Mon,Wed,Fri'),
('12952', 'New Delhi Rajdhani', 'Mumbai Central', 'New Delhi', '17:40:00', '09:55:00', 250, 250, 1500.00, 'Rajdhani', 'Tue,Thu,Sat'),
('12001', 'Bhopal Shatabdi', 'New Delhi', 'Bhopal', '06:00:00', '13:35:00', 150, 150, 850.00, 'Shatabdi', 'Mon,Tue,Wed,Thu,Fri,Sat,Sun'),
('12002', 'New Delhi Shatabdi', 'Bhopal', 'New Delhi', '14:30:00', '21:55:00', 150, 150, 850.00, 'Shatabdi', 'Mon,Tue,Wed,Thu,Fri,Sat,Sun'),
('12627', 'Karnataka Express', 'New Delhi', 'Bengaluru', '22:30:00', '06:30:00', 300, 300, 1100.00, 'Express', 'Mon,Tue,Wed,Thu,Fri,Sat,Sun'),
('12628', 'Karnataka Express', 'Bengaluru', 'New Delhi', '20:00:00', '04:30:00', 300, 300, 1100.00, 'Express', 'Mon,Tue,Wed,Thu,Fri,Sat,Sun'),
('12009', 'Shatabdi Express', 'Mumbai Central', 'Ahmedabad', '06:25:00', '12:45:00', 180, 180, 650.00, 'Shatabdi', 'Mon,Tue,Wed,Thu,Fri,Sat,Sun'),
('12010', 'Shatabdi Express', 'Ahmedabad', 'Mumbai Central', '14:05:00', '20:00:00', 180, 180, 650.00, 'Shatabdi', 'Mon,Tue,Wed,Thu,Fri,Sat,Sun'),
('12259', 'Duronto Express', 'New Delhi', 'Mumbai Central', '23:05:00', '17:10:00', 220, 220, 1300.00, 'Duronto', 'Mon,Wed,Fri,Sun'),
('12260', 'Duronto Express', 'Mumbai Central', 'New Delhi', '23:15:00', '17:00:00', 220, 220, 1300.00, 'Duronto', 'Tue,Thu,Sat'),
('12435', 'Rajdhani Express', 'New Delhi', 'Chennai', '22:30:00', '07:30:00', 280, 280, 1800.00, 'Rajdhani', 'Tue,Fri,Sun'),
('12436', 'Rajdhani Express', 'Chennai', 'New Delhi', '07:10:00', '10:00:00', 280, 280, 1800.00, 'Rajdhani', 'Mon,Wed,Sat'),
('12019', 'Howrah Shatabdi', 'Howrah', 'New Delhi', '05:55:00', '14:00:00', 160, 160, 950.00, 'Shatabdi', 'Mon,Tue,Wed,Thu,Fri,Sat,Sun'),
('12020', 'New Delhi Shatabdi', 'New Delhi', 'Howrah', '06:00:00', '14:05:00', 160, 160, 950.00, 'Shatabdi', 'Mon,Tue,Wed,Thu,Fri,Sat,Sun'),
('22691', 'Rajdhani Express', 'New Delhi', 'Bengaluru', '20:30:00', '06:30:00', 260, 260, 1700.00, 'Rajdhani', 'Tue,Thu,Sun'),
('22692', 'Rajdhani Express', 'Bengaluru', 'New Delhi', '20:00:00', '06:00:00', 260, 260, 1700.00, 'Rajdhani', 'Mon,Wed,Sat'),
('12223', 'Duronto Express', 'Mumbai Central', 'Howrah', '14:05:00', '11:15:00', 240, 240, 1400.00, 'Duronto', 'Mon,Wed,Fri'),
('12224', 'Duronto Express', 'Howrah', 'Mumbai Central', '14:30:00', '11:35:00', 240, 240, 1400.00, 'Duronto', 'Tue,Thu,Sat');
