<?php
/**
 * MAX Logistics Tracking System - Reset Database
 * 
 * This script clears the existing database and recreates it with proper data
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

// Check if script is being run from command line or web
$isCLI = php_sapi_name() === 'cli';

if (!$isCLI) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>MAX Logistics - Reset Database</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
            .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
            .header { text-align: center; color: #1e3c72; margin-bottom: 30px; }
            .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0; }
            .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0; }
            .info { background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 5px; margin: 10px 0; }
            .btn { background: #1e3c72; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; display: inline-block; }
            .btn:hover { background: #2a5298; }
            .warning { background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; margin: 10px 0; border: 1px solid #ffeaa7; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>MAX Logistics Tracking System</h1>
                <h2>Reset Database</h2>
            </div>
    <?php
}

try {
    echo $isCLI ? "Resetting MAX Logistics Tracking Database...\n" : '<div class="warning">⚠️ This will delete all existing data and recreate the database!</div>';
    
    // Connect to MySQL
    $dsn = "mysql:host=" . DB_HOST . ";charset=" . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => true,
    ];
    
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    
    // Drop and recreate database
    $pdo->exec("DROP DATABASE IF EXISTS " . DB_NAME);
    $pdo->exec("CREATE DATABASE " . DB_NAME);
    $pdo->exec("USE " . DB_NAME);
    
    echo $isCLI ? "✓ Database reset\n" : '<div class="info">✓ Database reset</div>';
    
    // Create tables
    $pdo->exec("CREATE TABLE shipments (
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
    )");
    echo $isCLI ? "✓ Created shipments table\n" : '<div class="info">✓ Created shipments table</div>';
    
    $pdo->exec("CREATE TABLE shipment_status_history (
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
    )");
    echo $isCLI ? "✓ Created shipment_status_history table\n" : '<div class="info">✓ Created shipment_status_history table</div>';
    
    $pdo->exec("CREATE TABLE customers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) UNIQUE NOT NULL,
        phone VARCHAR(20),
        address TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    echo $isCLI ? "✓ Created customers table\n" : '<div class="info">✓ Created customers table</div>';
    
    $pdo->exec("CREATE TABLE shipment_customers (
        shipment_id INT NOT NULL,
        customer_id INT NOT NULL,
        PRIMARY KEY (shipment_id, customer_id),
        FOREIGN KEY (shipment_id) REFERENCES shipments(id) ON DELETE CASCADE,
        FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
    )");
    echo $isCLI ? "✓ Created shipment_customers table\n" : '<div class="info">✓ Created shipment_customers table</div>';
    
    // Insert sample data with proper timeline
    $sampleData = [
        ['MAX123456789', 'Jakarta, Indonesia', 'Surabaya, Indonesia', 2.50, 'Express Delivery', 'MAX Logistics', '2024-01-18', 'In Transit', 'Package is in transit to destination'],
        ['MAX987654321', 'Bandung, Indonesia', 'Medan, Indonesia', 1.80, 'Standard Delivery', 'MAX Logistics', '2024-01-12', 'Delivered', 'Package has been successfully delivered'],
        ['MAX555666777', 'Yogyakarta, Indonesia', 'Bali, Indonesia', 3.20, 'Priority Delivery', 'MAX Logistics', '2024-01-17', 'Processing', 'Package is being processed at origin facility'],
        ['MAX111222333', 'Semarang, Indonesia', 'Makassar, Indonesia', 4.50, 'Express Delivery', 'MAX Logistics', '2024-01-20', 'Out for Delivery', 'Package is out for delivery'],
        ['MAX444555666', 'Palembang, Indonesia', 'Pontianak, Indonesia', 1.20, 'Standard Delivery', 'MAX Logistics', '2024-01-16', 'Exception', 'Delivery attempted - recipient not available']
    ];
    
    foreach ($sampleData as $data) {
        $stmt = $pdo->prepare("INSERT INTO shipments (tracking_number, origin, destination, weight, service_type, carrier, estimated_delivery, current_status, current_status_description) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute($data);
        
        echo $isCLI ? "✓ Added sample data: " . $data[0] . "\n" : '<div class="info">✓ Added sample data: ' . $data[0] . '</div>';
        
        // Get shipment ID
        $stmt = $pdo->prepare("SELECT id FROM shipments WHERE tracking_number = ?");
        $stmt->execute([$data[0]]);
        $shipmentId = $stmt->fetch()['id'];
        
        // Add complete timeline for each shipment
        $timelineData = getTimelineData($data[0], $data[7], $data[1], $data[2]);
        
        foreach ($timelineData as $timelineItem) {
            $stmt = $pdo->prepare("INSERT INTO shipment_status_history (shipment_id, status, status_description, location, status_date) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$shipmentId, $timelineItem['status'], $timelineItem['description'], $timelineItem['location'], $timelineItem['date']]);
        }
    }
    
    // Final verification
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM shipments");
    $shipmentCount = $stmt->fetch()['count'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM shipment_status_history");
    $statusCount = $stmt->fetch()['count'];
    
    if ($isCLI) {
        echo "\n=== Database Reset Complete ===\n";
        echo "Sample shipments: $shipmentCount\n";
        echo "Sample status records: $statusCount\n";
        echo "\nSample tracking numbers:\n";
        echo "- MAX123456789 (In Transit)\n";
        echo "- MAX987654321 (Delivered)\n";
        echo "- MAX555666777 (Processing)\n";
        echo "- MAX111222333 (Out for Delivery)\n";
        echo "- MAX444555666 (Exception)\n";
        echo "\nDatabase reset completed successfully!\n";
    } else {
        echo '<div class="success">';
        echo '<h3>✓ Database Reset Complete!</h3>';
        echo '<p><strong>Sample shipments:</strong> ' . $shipmentCount . '</p>';
        echo '<p><strong>Sample status records:</strong> ' . $statusCount . '</p>';
        echo '</div>';
        
        echo '<div class="info">';
        echo '<h4>Sample Tracking Numbers for Testing:</h4>';
        echo '<ul>';
        echo '<li><strong>MAX123456789</strong> - In Transit</li>';
        echo '<li><strong>MAX987654321</strong> - Delivered</li>';
        echo '<li><strong>MAX555666777</strong> - Processing</li>';
        echo '<li><strong>MAX111222333</strong> - Out for Delivery</li>';
        echo '<li><strong>MAX444555666</strong> - Exception</li>';
        echo '</ul>';
        echo '</div>';
        
        echo '<div style="text-align: center; margin-top: 30px;">';
        echo '<a href="../index.html" class="btn">Go to Tracking System</a>';
        echo '</div>';
    }
    
} catch (Exception $e) {
    if ($isCLI) {
        echo "Reset failed: " . $e->getMessage() . "\n";
        exit(1);
    } else {
        echo '<div class="error">';
        echo '<h3>✗ Reset Failed</h3>';
        echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
        echo '</div>';
    }
}

if (!$isCLI) {
    echo '</div></body></html>';
}

/**
 * Generate timeline data for a shipment
 */
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
            // Default timeline
            $timelineData = [
                ['status' => $currentStatus, 'description' => 'Package status update', 'location' => $origin, 'date' => date('Y-m-d H:i:s')]
            ];
    }
    
    return $timelineData;
}
?>
