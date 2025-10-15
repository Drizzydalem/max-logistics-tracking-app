<?php
/**
 * Fix Database Data
 * This script manually fixes the database data
 */

require_once '../config/database.php';

header('Content-Type: text/html; charset=utf-8');

try {
    $db = getDBConnection();
    
    echo "<h2>Fixing Database Data</h2>";
    
    // Clear existing data
    $db->exec("DELETE FROM shipment_status_history");
    $db->exec("DELETE FROM shipments");
    
    echo "<p>✓ Cleared existing data</p>";
    
    // Insert correct sample data
    $sampleData = [
        ['MAX123456789', 'Jakarta, Indonesia', 'Surabaya, Indonesia', 2.50, 'Express Delivery', 'MAX Logistics', '2024-01-18', 'In Transit', 'Package is in transit to destination'],
        ['MAX987654321', 'Bandung, Indonesia', 'Medan, Indonesia', 1.80, 'Standard Delivery', 'MAX Logistics', '2024-01-12', 'Delivered', 'Package has been successfully delivered'],
        ['MAX555666777', 'Yogyakarta, Indonesia', 'Bali, Indonesia', 3.20, 'Priority Delivery', 'MAX Logistics', '2024-01-17', 'Processing', 'Package is being processed at origin facility'],
        ['MAX111222333', 'Semarang, Indonesia', 'Makassar, Indonesia', 4.50, 'Express Delivery', 'MAX Logistics', '2024-01-20', 'Out for Delivery', 'Package is out for delivery'],
        ['MAX444555666', 'Palembang, Indonesia', 'Pontianak, Indonesia', 1.20, 'Standard Delivery', 'MAX Logistics', '2024-01-16', 'Exception', 'Delivery attempted - recipient not available']
    ];
    
    foreach ($sampleData as $data) {
        $stmt = $db->prepare("INSERT INTO shipments (tracking_number, origin, destination, weight, service_type, carrier, estimated_delivery, current_status, current_status_description) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute($data);
        
        echo "<p>✓ Added shipment: " . $data[0] . "</p>";
        
        // Get shipment ID
        $stmt = $db->prepare("SELECT id FROM shipments WHERE tracking_number = ?");
        $stmt->execute([$data[0]]);
        $shipmentId = $stmt->fetch()['id'];
        
        // Add timeline data based on tracking number
        $timelineData = getTimelineData($data[0], $data[7], $data[1], $data[2]);
        
        foreach ($timelineData as $timelineItem) {
            $stmt = $db->prepare("INSERT INTO shipment_status_history (shipment_id, status, status_description, location, status_date) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$shipmentId, $timelineItem['status'], $timelineItem['description'], $timelineItem['location'], $timelineItem['date']]);
        }
        
        echo "<p>✓ Added timeline for: " . $data[0] . "</p>";
    }
    
    // Verify data
    $stmt = $db->query("SELECT COUNT(*) as count FROM shipments");
    $shipmentCount = $stmt->fetch()['count'];
    
    $stmt = $db->query("SELECT COUNT(*) as count FROM shipment_status_history");
    $statusCount = $stmt->fetch()['count'];
    
    echo "<h3>✓ Data Fixed Successfully!</h3>";
    echo "<p>Shipments: $shipmentCount</p>";
    echo "<p>Status Records: $statusCount</p>";
    
    echo "<p><a href='../index.html'>Go to Tracking System</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}

function getTimelineData($trackingNumber, $currentStatus, $origin, $destination) {
    $timelineData = [];
    
    switch ($trackingNumber) {
        case 'MAX123456789': // In Transit
            $timelineData = [
                ['status' => 'Package Picked Up', 'description' => 'Package has been picked up from origin', 'location' => $origin, 'date' => '2024-01-15 09:00:00'],
                ['status' => 'In Transit', 'description' => 'Package is in transit to destination', 'location' => $origin . ' Distribution Center', 'date' => '2024-01-15 14:30:00'],
                ['status' => 'Out for Delivery', 'description' => 'Package is out for delivery', 'location' => $destination, 'date' => '2024-01-18 08:00:00'],
                ['status' => 'Delivered', 'description' => 'Package has been delivered', 'location' => $destination, 'date' => '2024-01-18 16:00:00']
            ];
            break;
            
        case 'MAX987654321': // Delivered
            $timelineData = [
                ['status' => 'Package Picked Up', 'description' => 'Package has been picked up from origin', 'location' => $origin, 'date' => '2024-01-10 10:00:00'],
                ['status' => 'In Transit', 'description' => 'Package is in transit to destination', 'location' => $origin . ' Distribution Center', 'date' => '2024-01-11 08:00:00'],
                ['status' => 'Out for Delivery', 'description' => 'Package is out for delivery', 'location' => $destination, 'date' => '2024-01-12 09:00:00'],
                ['status' => 'Delivered', 'description' => 'Package has been successfully delivered', 'location' => $destination, 'date' => '2024-01-12 15:45:00']
            ];
            break;
            
        case 'MAX555666777': // Processing
            $timelineData = [
                ['status' => 'Package Received', 'description' => 'Package received at origin facility', 'location' => $origin, 'date' => '2024-01-15 11:20:00'],
                ['status' => 'In Transit', 'description' => 'Package is in transit to destination', 'location' => $origin . ' Distribution Center', 'date' => '2024-01-16 08:00:00'],
                ['status' => 'Out for Delivery', 'description' => 'Package is out for delivery', 'location' => $destination, 'date' => '2024-01-17 09:00:00'],
                ['status' => 'Delivered', 'description' => 'Package has been delivered', 'location' => $destination, 'date' => '2024-01-17 16:00:00']
            ];
            break;
            
        case 'MAX111222333': // Out for Delivery
            $timelineData = [
                ['status' => 'Package Picked Up', 'description' => 'Package has been picked up from origin', 'location' => $origin, 'date' => '2024-01-18 08:30:00'],
                ['status' => 'In Transit', 'description' => 'Package is in transit to destination', 'location' => $origin . ' Distribution Center', 'date' => '2024-01-19 10:15:00'],
                ['status' => 'Out for Delivery', 'description' => 'Package is out for delivery', 'location' => $destination, 'date' => '2024-01-20 08:00:00']
            ];
            break;
            
        case 'MAX444555666': // Exception
            $timelineData = [
                ['status' => 'Package Picked Up', 'description' => 'Package has been picked up from origin', 'location' => $origin, 'date' => '2024-01-14 14:20:00'],
                ['status' => 'In Transit', 'description' => 'Package is in transit to destination', 'location' => $origin . ' Distribution Center', 'date' => '2024-01-15 09:45:00'],
                ['status' => 'Out for Delivery', 'description' => 'Package is out for delivery', 'location' => $destination, 'date' => '2024-01-16 10:30:00'],
                ['status' => 'Exception', 'description' => 'Delivery attempted - recipient not available', 'location' => $destination, 'date' => '2024-01-16 14:20:00']
            ];
            break;
            
        default:
            $timelineData = [
                ['status' => $currentStatus, 'description' => 'Package status update', 'location' => $origin, 'date' => date('Y-m-d H:i:s')]
            ];
    }
    
    return $timelineData;
}
?>
