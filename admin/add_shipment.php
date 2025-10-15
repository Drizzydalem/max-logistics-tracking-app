<?php
/**
 * MAX Logistics Tracking System - Add New Shipment
 * 
 * This script allows adding new shipments to the tracking system
 */

require_once '../config/database.php';

// Set CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Validate required fields
        $requiredFields = ['tracking_number', 'origin', 'destination', 'weight', 'service_type'];
        foreach ($requiredFields as $field) {
            if (empty($input[$field])) {
                sendResponse(null, 400, "Field '$field' is required");
            }
        }
        
        // Sanitize input
        $trackingNumber = strtoupper(sanitizeInput($input['tracking_number']));
        $origin = sanitizeInput($input['origin']);
        $destination = sanitizeInput($input['destination']);
        $weight = floatval($input['weight']);
        $serviceType = sanitizeInput($input['service_type']);
        $carrier = sanitizeInput($input['carrier'] ?? 'MAX Logistics');
        $estimatedDelivery = sanitizeInput($input['estimated_delivery'] ?? null);
        $currentStatus = sanitizeInput($input['current_status'] ?? 'Processing');
        $currentStatusDescription = sanitizeInput($input['current_status_description'] ?? 'Package received and being processed');
        
        // Validate tracking number format
        if (!validateTrackingNumber($trackingNumber)) {
            sendResponse(null, 400, 'Invalid tracking number format. Please use format: MAX followed by 9 digits');
        }
        
        // Get database connection
        $db = getDBConnection();
        
        // Check if tracking number already exists
        $stmt = $db->prepare("SELECT id FROM shipments WHERE tracking_number = ?");
        $stmt->execute([$trackingNumber]);
        if ($stmt->fetch()) {
            sendResponse(null, 409, 'Tracking number already exists');
        }
        
        // Insert new shipment
        $stmt = $db->prepare("
            INSERT INTO shipments (
                tracking_number, origin, destination, weight, service_type, 
                carrier, estimated_delivery, current_status, current_status_description
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $trackingNumber, $origin, $destination, $weight, $serviceType,
            $carrier, $estimatedDelivery, $currentStatus, $currentStatusDescription
        ]);
        
        $shipmentId = $db->lastInsertId();
        
        // Add initial status to timeline
        $stmt = $db->prepare("
            INSERT INTO shipment_status_history (
                shipment_id, status, status_description, location, status_date
            ) VALUES (?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([
            $shipmentId, 
            $currentStatus, 
            $currentStatusDescription, 
            $origin
        ]);
        
        sendResponse([
            'shipment_id' => $shipmentId,
            'tracking_number' => $trackingNumber,
            'message' => 'Shipment added successfully'
        ], 201, 'Shipment created successfully');
        
    } catch (Exception $e) {
        if (DEBUG_MODE) {
            error_log("Add shipment error: " . $e->getMessage());
        }
        sendResponse(null, 500, 'Failed to add shipment: ' . $e->getMessage());
    }
}

// Handle GET request - show form or list shipments
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $db = getDBConnection();
        
        // Get list of recent shipments
        $stmt = $db->prepare("
            SELECT 
                id, tracking_number, origin, destination, 
                current_status, created_at
            FROM shipments 
            ORDER BY created_at DESC 
            LIMIT 50
        ");
        $stmt->execute();
        $shipments = $stmt->fetchAll();
        
        sendResponse($shipments, 200, 'Shipments retrieved successfully');
        
    } catch (Exception $e) {
        if (DEBUG_MODE) {
            error_log("Get shipments error: " . $e->getMessage());
        }
        sendResponse(null, 500, 'Failed to retrieve shipments');
    }
}
?>
