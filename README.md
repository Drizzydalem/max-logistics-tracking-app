# MAX Logistics Tracking System

A complete web-based shipment tracking system built with HTML, CSS, JavaScript, PHP, and MySQL. This system allows users to track their shipments in real-time with a professional interface that matches MAX Logistics branding.

## Features

- 🚚 **Real-time Tracking**: Track shipments using tracking numbers
- 📱 **Responsive Design**: Works on desktop, tablet, and mobile devices
- 🎨 **Professional UI**: Matches MAX Logistics branding and design
- 📊 **Timeline Visualization**: Interactive shipment progress tracking
- 🔍 **Status Indicators**: Color-coded status badges and progress tracking
- 📋 **Detailed Information**: Shows origin, destination, weight, service type, etc.
- ⚡ **Fast API**: RESTful PHP API with MySQL database
- 🔒 **Secure**: Input validation and SQL injection protection
- 📈 **Analytics**: Request logging for tracking usage

## Technology Stack

- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Web Server**: Apache/Nginx

## Installation

### Prerequisites

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)
- Modern web browser

### Setup Instructions

1. **Clone or Download the Project**
   ```bash
   git clone <repository-url>
   # or download and extract the ZIP file
   ```

2. **Configure Database**
   - Open `config/database.php`
   - Update database credentials:
     ```php
     define('DB_HOST', 'localhost');
     define('DB_NAME', 'max_logistics_tracking');
     define('DB_USER', 'your_username');
     define('DB_PASS', 'your_password');
     ```

3. **Set Up Database**
   - Run the database setup script:
     ```bash
     # Via web browser
     http://your-domain.com/admin/setup.php
     
     # Or via command line
     php admin/setup.php
     ```

4. **Configure Web Server**
   - Point your web server document root to the project directory
   - Ensure PHP is enabled
   - Make sure the `api/` directory is accessible

5. **Test the System**
   - Open `index.html` in your web browser
   - Try the sample tracking numbers:
     - `MAX123456789` (In Transit)
     - `MAX987654321` (Delivered)
     - `MAX555666777` (Processing)
     - `MAX111222333` (Out for Delivery)
     - `MAX444555666` (Exception)

## Project Structure

```
max_logistics_prototype/
├── index.html              # Main tracking page
├── styles.css              # CSS styles
├── script.js               # JavaScript functionality
├── config/
│   └── database.php        # Database configuration
├── api/
│   └── track.php           # Tracking API endpoint
├── admin/
│   ├── setup.php           # Database setup script
│   └── add_shipment.php    # Add new shipments
├── database_schema.sql     # Database schema
└── README.md              # This file
```

## API Endpoints

### Track Shipment
- **URL**: `/api/track.php`
- **Method**: POST
- **Content-Type**: application/json
- **Request Body**:
  ```json
  {
    "tracking_number": "MAX123456789"
  }
  ```
- **Response**:
  ```json
  {
    "success": true,
    "message": "Tracking information retrieved successfully",
    "data": {
      "tracking_number": "MAX123456789",
      "status": "In Transit",
      "current_status": "Package is in transit to destination",
      "last_updated": "2024-01-15 14:30",
      "origin": "Jakarta, Indonesia",
      "destination": "Surabaya, Indonesia",
      "estimated_delivery": "2024-01-18",
      "weight": "2.5 kg",
      "service_type": "Express Delivery",
      "carrier": "MAX Logistics",
      "timeline": [...]
    }
  }
  ```

## Database Schema

### Tables

1. **shipments** - Main shipment information
2. **shipment_status_history** - Status timeline for each shipment
3. **customers** - Customer information (optional)
4. **shipment_customers** - Link shipments to customers (optional)
5. **tracking_logs** - API request logs (auto-created)

## Adding New Shipments

### Via Admin Interface
1. Access `/admin/add_shipment.php`
2. Use POST request with JSON data:
   ```json
   {
     "tracking_number": "MAX999888777",
     "origin": "Jakarta, Indonesia",
     "destination": "Bandung, Indonesia",
     "weight": 1.5,
     "service_type": "Express Delivery",
     "estimated_delivery": "2024-01-20",
     "current_status": "Processing",
     "current_status_description": "Package received and being processed"
   }
   ```

### Via Database
Insert directly into the `shipments` table and add corresponding entries to `shipment_status_history`.

## Customization

### Styling
- Modify `styles.css` to change colors, fonts, or layout
- The color scheme uses MAX Logistics branding colors
- All styles are responsive and mobile-friendly

### Functionality
- Update `script.js` to modify frontend behavior
- Modify `api/track.php` to change API responses
- Update `config/database.php` for different database settings

## Security Features

- Input validation and sanitization
- SQL injection protection using prepared statements
- CORS headers for API security
- Error logging and debugging modes
- Request logging for analytics

## Browser Support

- Chrome 60+
- Firefox 55+
- Safari 12+
- Edge 79+
- Mobile browsers (iOS Safari, Chrome Mobile)

## Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Check database credentials in `config/database.php`
   - Ensure MySQL server is running
   - Verify database exists

2. **API Not Working**
   - Check web server configuration
   - Ensure PHP is enabled
   - Verify file permissions

3. **Tracking Numbers Not Found**
   - Run the setup script to create sample data
   - Check database for existing shipments
   - Verify tracking number format (MAX + 9 digits)

### Debug Mode

Enable debug mode in `config/database.php`:
```php
define('DEBUG_MODE', true);
```

This will log errors to the PHP error log.
