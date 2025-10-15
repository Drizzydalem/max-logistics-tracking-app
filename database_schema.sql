-- MAX Logistics Tracking System Database Schema
-- Create database
CREATE DATABASE IF NOT EXISTS max_logistics_tracking;
USE max_logistics_tracking;

-- Table for shipments
CREATE TABLE shipments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tracking_number VARCHAR(50) UNIQUE NOT NULL,
    origin VARCHAR(255) NOT NULL,
    destination VARCHAR(255) NOT NULL,
    weight DECIMAL(5,2) NOT NULL,
    service_type VARCHAR(100) NOT NULL,
    carrier VARCHAR(100) DEFAULT 'MAX Logistics',
    estimated_delivery DATE,
    current_status VARCHAR(100) NOT NULL,
    current_status_description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tracking_number (tracking_number),
    INDEX idx_status (current_status)
);

-- Table for shipment status history/timeline
CREATE TABLE shipment_status_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    shipment_id INT NOT NULL,
    status VARCHAR(100) NOT NULL,
    status_description TEXT,
    location VARCHAR(255) NOT NULL,
    status_date TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (shipment_id) REFERENCES shipments(id) ON DELETE CASCADE,
    INDEX idx_shipment_id (shipment_id),
    INDEX idx_status_date (status_date)
);

-- Table for customers (optional - for future enhancements)
CREATE TABLE customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table to link shipments with customers (optional)
CREATE TABLE shipment_customers (
    shipment_id INT NOT NULL,
    customer_id INT NOT NULL,
    PRIMARY KEY (shipment_id, customer_id),
    FOREIGN KEY (shipment_id) REFERENCES shipments(id) ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
);

-- Insert sample data
INSERT INTO shipments (tracking_number, origin, destination, weight, service_type, carrier, estimated_delivery, current_status, current_status_description) VALUES
('MAX123456789', 'Jakarta, Indonesia', 'Surabaya, Indonesia', 2.50, 'Express Delivery', 'MAX Logistics', '2024-01-18', 'In Transit', 'Package is in transit to destination'),
('MAX987654321', 'Bandung, Indonesia', 'Medan, Indonesia', 1.80, 'Standard Delivery', 'MAX Logistics', '2024-01-12', 'Delivered', 'Package has been successfully delivered'),
('MAX555666777', 'Yogyakarta, Indonesia', 'Bali, Indonesia', 3.20, 'Priority Delivery', 'MAX Logistics', '2024-01-17', 'Processing', 'Package is being processed at origin facility'),
('MAX111222333', 'Semarang, Indonesia', 'Makassar, Indonesia', 4.50, 'Express Delivery', 'MAX Logistics', '2024-01-20', 'Out for Delivery', 'Package is out for delivery'),
('MAX444555666', 'Palembang, Indonesia', 'Pontianak, Indonesia', 1.20, 'Standard Delivery', 'MAX Logistics', '2024-01-16', 'Exception', 'Delivery attempted - recipient not available');

-- Insert status history for each shipment
-- MAX123456789 - In Transit
INSERT INTO shipment_status_history (shipment_id, status, status_description, location, status_date) VALUES
(1, 'Package Picked Up', 'Package has been picked up from origin', 'Jakarta Warehouse', '2024-01-15 09:00:00'),
(1, 'In Transit', 'Package is in transit to destination', 'Jakarta Distribution Center', '2024-01-15 14:30:00'),
(1, 'Out for Delivery', 'Package is out for delivery', 'Surabaya', '2024-01-18 08:00:00'),
(1, 'Delivered', 'Package has been delivered', 'Surabaya, Indonesia', '2024-01-18 16:00:00');

-- MAX987654321 - Delivered
INSERT INTO shipment_status_history (shipment_id, status, status_description, location, status_date) VALUES
(2, 'Package Picked Up', 'Package has been picked up from origin', 'Bandung Warehouse', '2024-01-10 10:00:00'),
(2, 'In Transit', 'Package is in transit to destination', 'Bandung Distribution Center', '2024-01-11 08:00:00'),
(2, 'Out for Delivery', 'Package is out for delivery', 'Medan', '2024-01-12 09:00:00'),
(2, 'Delivered', 'Package has been successfully delivered', 'Medan, Indonesia', '2024-01-12 15:45:00');

-- MAX555666777 - Processing
INSERT INTO shipment_status_history (shipment_id, status, status_description, location, status_date) VALUES
(3, 'Package Received', 'Package received at origin facility', 'Yogyakarta Warehouse', '2024-01-15 11:20:00'),
(3, 'In Transit', 'Package is in transit to destination', 'Yogyakarta Distribution Center', '2024-01-16 08:00:00'),
(3, 'Out for Delivery', 'Package is out for delivery', 'Bali', '2024-01-17 09:00:00'),
(3, 'Delivered', 'Package has been delivered', 'Bali, Indonesia', '2024-01-17 16:00:00');

-- MAX111222333 - Out for Delivery
INSERT INTO shipment_status_history (shipment_id, status, status_description, location, status_date) VALUES
(4, 'Package Picked Up', 'Package has been picked up from origin', 'Semarang Warehouse', '2024-01-18 08:30:00'),
(4, 'In Transit', 'Package is in transit to destination', 'Semarang Distribution Center', '2024-01-19 10:15:00'),
(4, 'Out for Delivery', 'Package is out for delivery', 'Makassar', '2024-01-20 08:00:00');

-- MAX444555666 - Exception
INSERT INTO shipment_status_history (shipment_id, status, status_description, location, status_date) VALUES
(5, 'Package Picked Up', 'Package has been picked up from origin', 'Palembang Warehouse', '2024-01-14 14:20:00'),
(5, 'In Transit', 'Package is in transit to destination', 'Palembang Distribution Center', '2024-01-15 09:45:00'),
(5, 'Out for Delivery', 'Package is out for delivery', 'Pontianak', '2024-01-16 10:30:00'),
(5, 'Exception', 'Delivery attempted - recipient not available', 'Pontianak, Indonesia', '2024-01-16 14:20:00');

-- Create a view for easy tracking queries
CREATE VIEW tracking_view AS
SELECT 
    s.id,
    s.tracking_number,
    s.origin,
    s.destination,
    s.weight,
    s.service_type,
    s.carrier,
    s.estimated_delivery,
    s.current_status,
    s.current_status_description,
    s.created_at,
    s.updated_at
FROM shipments s;

-- Create a view for status timeline
CREATE VIEW status_timeline_view AS
SELECT 
    s.tracking_number,
    ssh.status,
    ssh.status_description,
    ssh.location,
    ssh.status_date,
    ssh.created_at
FROM shipments s
JOIN shipment_status_history ssh ON s.id = ssh.shipment_id
ORDER BY s.tracking_number, ssh.status_date ASC;
