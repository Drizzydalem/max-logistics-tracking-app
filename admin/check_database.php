<?php
/**
 * Check Database Status
 * This script shows what's currently in the database
 */

require_once '../config/database.php';

header('Content-Type: text/html; charset=utf-8');

try {
    $db = getDBConnection();
    
    echo "<h2>Database Status Check</h2>";
    
    // Check shipments table
    $stmt = $db->query("SELECT * FROM shipments LIMIT 5");
    $shipments = $stmt->fetchAll();
    
    echo "<h3>Shipments Table:</h3>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Tracking Number</th><th>Origin</th><th>Destination</th><th>Weight</th><th>Service Type</th><th>Estimated Delivery</th><th>Current Status</th></tr>";
    
    foreach ($shipments as $shipment) {
        echo "<tr>";
        echo "<td>" . $shipment['id'] . "</td>";
        echo "<td>" . $shipment['tracking_number'] . "</td>";
        echo "<td>" . $shipment['origin'] . "</td>";
        echo "<td>" . $shipment['destination'] . "</td>";
        echo "<td>" . $shipment['weight'] . "</td>";
        echo "<td>" . $shipment['service_type'] . "</td>";
        echo "<td>" . $shipment['estimated_delivery'] . "</td>";
        echo "<td>" . $shipment['current_status'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Check status history
    $stmt = $db->query("SELECT s.tracking_number, ssh.status, ssh.location, ssh.status_date FROM shipments s JOIN shipment_status_history ssh ON s.id = ssh.shipment_id ORDER BY s.tracking_number, ssh.status_date");
    $statusHistory = $stmt->fetchAll();
    
    echo "<h3>Status History:</h3>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Tracking Number</th><th>Status</th><th>Location</th><th>Date</th></tr>";
    
    foreach ($statusHistory as $status) {
        echo "<tr>";
        echo "<td>" . $status['tracking_number'] . "</td>";
        echo "<td>" . $status['status'] . "</td>";
        echo "<td>" . $status['location'] . "</td>";
        echo "<td>" . $status['status_date'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
