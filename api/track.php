<?php
/**
 * MAX Logistics Tracking API
 * 
 * This endpoint handles shipment tracking requests
 * 
 * Usage: GET /api/track.php?tracking_number=MAX123456789
 *        POST /api/track.php with JSON body: {"tracking_number": "MAX123456789"}
 */

require_once '../config/database.php';

// Set CORS headers for cross-origin requests
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    // Get tracking number from request
    $trackingNumber = null;
    
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $trackingNumber = isset($_GET['tracking_number']) ? sanitizeInput($_GET['tracking_number']) : null;
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        $trackingNumber = isset($input['tracking_number']) ? sanitizeInput($input['tracking_number']) : null;
    }
    
    // Validate tracking number
    if (empty($trackingNumber)) {
        sendResponse(null, 400, 'Tracking number is required');
    }
    
    // Convert to uppercase for consistency
    $trackingNumber = strtoupper($trackingNumber);
    
    // Validate tracking number format
    if (!validateTrackingNumber($trackingNumber)) {
        sendResponse(null, 400, 'Invalid tracking number format. Please use format: MAX followed by 9 digits');
    }
    
    // Log the tracking request
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    logTrackingRequest($trackingNumber, $ipAddress, $userAgent);
    
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
        sendResponse(null, 404, 'Tracking number not found');
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
    
    // Format the response data
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
            'status' => determineTimelineStatus($event['status'], $shipment['current_status'])
        ];
    }
    
    sendResponse($responseData, 200, 'Tracking information retrieved successfully');
    
} catch (Exception $e) {
    if (DEBUG_MODE) {
        error_log("Tracking API Error: " . $e->getMessage());
    }
    sendResponse(null, 500, 'Internal server error. Please try again later.');
}

/**
 * Determine timeline item status based on current shipment status
 * @param string $eventStatus
 * @param string $currentStatus
 * @return string
 */
function determineTimelineStatus($eventStatus, $currentStatus) {
    $statusMap = [
        'Package Received' => 'completed',
        'Package Picked Up' => 'completed',
        'In Transit' => 'completed',
        'Out for Delivery' => 'completed',
        'Delivered' => 'completed',
        'Exception' => 'completed'
    ];
    
    // If this is the current status, mark as current
    if ($eventStatus === $currentStatus) {
        return 'current';
    }
    
    // If this event has passed, mark as completed
    if (isset($statusMap[$eventStatus])) {
        return $statusMap[$eventStatus];
    }
    
    // Default to pending for future events
    return 'pending';
}
?>
