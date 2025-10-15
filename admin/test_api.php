<?php
/**
 * Test API Response
 * This script tests what the API returns for a tracking number
 */

require_once '../config/database.php';

header('Content-Type: application/json');

try {
    $trackingNumber = 'MAX123456789';
    
    // Get database connection
    $db = getDBConnection();
    
    // Query shipment information
    $stmt = $db->prepare("
        SELECT 
            id,
            tracking_number,
            origin,
            destination,
            weight,
            service_type,
            carrier,
            estimated_delivery,
            current_status,
            current_status_description,
            created_at,
            updated_at
        FROM shipments 
        WHERE tracking_number = ?
    ");
    
    $stmt->execute([$trackingNumber]);
    $shipment = $stmt->fetch();
    
    if (!$shipment) {
        echo json_encode(['error' => 'Tracking number not found']);
        exit;
    }
    
    // Query status history/timeline
    $stmt = $db->prepare("
        SELECT 
            status,
            status_description,
            location,
            status_date
        FROM shipment_status_history 
        WHERE shipment_id = ? 
        ORDER BY status_date ASC
    ");
    
    $stmt->execute([$shipment['id']]);
    $timeline = $stmt->fetchAll();
    
    // Format the response data (same as API)
    $responseData = [
        'tracking_number' => $shipment['tracking_number'],
        'status' => $shipment['current_status'],
        'current_status' => $shipment['current_status_description'],
        'last_updated' => date('Y-m-d H:i', strtotime($shipment['updated_at'])),
        'origin' => $shipment['origin'],
        'destination' => $shipment['destination'],
        'estimated_delivery' => $shipment['estimated_delivery'],
        'weight' => $shipment['weight'] . ' kg',
        'service_type' => $shipment['service_type'],
        'carrier' => $shipment['carrier'],
        'timeline' => []
    ];
    
    // Format timeline data
    foreach ($timeline as $event) {
        $responseData['timeline'][] = [
            'title' => $event['status'],
            'description' => $event['status_description'],
            'location' => $event['location'],
            'date' => date('Y-m-d H:i', strtotime($event['status_date'])),
            'status' => 'completed' // Simplified for testing
        ];
    }
    
    echo json_encode($responseData, JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
