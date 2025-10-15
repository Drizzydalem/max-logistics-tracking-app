// Mobile Navigation Toggle
const hamburger = document.querySelector('.hamburger');
const navMenu = document.querySelector('.nav-menu');

hamburger.addEventListener('click', () => {
    hamburger.classList.toggle('active');
    navMenu.classList.toggle('active');
});

// Close mobile menu when clicking on a link
document.querySelectorAll('.nav-link').forEach(n => n.addEventListener('click', () => {
    hamburger.classList.remove('active');
    navMenu.classList.remove('active');
}));

// Tracking Form Functionality
const trackingForm = document.getElementById('trackingForm');
const trackingResults = document.getElementById('trackingResults');
const noResults = document.getElementById('noResults');

// API Configuration
const API_BASE_URL = 'api/';
const TRACKING_ENDPOINT = API_BASE_URL + 'track.php';

// Status badge colors
const statusColors = {
    'Processing': '#ffc107',
    'In Transit': '#17a2b8',
    'Out for Delivery': '#fd7e14',
    'Delivered': '#28a745',
    'Exception': '#dc3545'
};

// Handle form submission
trackingForm.addEventListener('submit', (e) => {
    e.preventDefault();
    
    const trackingNumber = document.getElementById('trackingNumber').value.trim().toUpperCase();
    
    if (!trackingNumber) {
        showError('Please enter a tracking number');
        return;
    }
    
    // Show loading state
    const trackBtn = document.querySelector('.track-btn');
    const originalText = trackBtn.innerHTML;
    trackBtn.innerHTML = '<div class="loading"></div> Tracking...';
    trackBtn.disabled = true;
    
    // Call tracking function
    trackShipment(trackingNumber).finally(() => {
        trackBtn.innerHTML = originalText;
        trackBtn.disabled = false;
    });
});

async function trackShipment(trackingNumber) {
    // Hide previous results
    trackingResults.style.display = 'none';
    noResults.style.display = 'none';
    
    try {
        // Make API call to PHP backend
        const response = await fetch(TRACKING_ENDPOINT, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                tracking_number: trackingNumber
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            displayTrackingResults(trackingNumber, data.data);
        } else {
            if (response.status === 404) {
                showNoResults();
            } else {
                showError(data.message || 'Failed to track shipment');
            }
        }
    } catch (error) {
        console.error('Tracking error:', error);
        showError('Network error. Please check your connection and try again.');
    }
}

function displayTrackingResults(trackingNumber, data) {
    // Update tracking number display
    document.getElementById('displayTrackingNumber').textContent = trackingNumber;
    
    // Update status information
    const statusBadge = document.getElementById('statusBadge');
    const statusText = document.getElementById('statusText');
    const currentStatus = document.getElementById('currentStatus');
    const lastUpdated = document.getElementById('lastUpdated');
    
    statusText.textContent = data.status;
    currentStatus.textContent = data.currentStatus;
    lastUpdated.textContent = data.lastUpdated;
    
    // Update status badge color
    statusBadge.style.background = `linear-gradient(135deg, ${statusColors[data.status] || '#1e3c72'} 0%, ${adjustColor(statusColors[data.status] || '#1e3c72', -20)} 100%)`;
    
    // Update shipment details
    document.getElementById('origin').textContent = data.origin;
    document.getElementById('destination').textContent = data.destination;
    document.getElementById('estimatedDelivery').textContent = data.estimatedDelivery;
    document.getElementById('weight').textContent = data.weight;
    document.getElementById('serviceType').textContent = data.serviceType;
    document.getElementById('carrier').textContent = data.carrier;
    
    // Update timeline
    updateTimeline(data.timeline);
    
    // Show results
    trackingResults.style.display = 'block';
    trackingResults.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function updateTimeline(timeline) {
    const timelineContainer = document.getElementById('timelineContainer');
    timelineContainer.innerHTML = '';
    
    timeline.forEach((item, index) => {
        const timelineItem = document.createElement('div');
        timelineItem.className = `timeline-item ${item.status}`;
        
        timelineItem.innerHTML = `
            <div class="timeline-content">
                <div class="timeline-title">${item.title}</div>
                <div class="timeline-date">${item.date}</div>
                <div class="timeline-location">${item.location}</div>
            </div>
        `;
        
        timelineContainer.appendChild(timelineItem);
    });
}

function showNoResults() {
    noResults.style.display = 'block';
    noResults.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function showError(message) {
    // Create error message element
    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-message';
    errorDiv.style.cssText = `
        background: #f8d7da;
        color: #721c24;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
        border: 1px solid #f5c6cb;
        text-align: center;
    `;
    errorDiv.textContent = message;
    
    // Insert error message before the form
    const formContainer = document.querySelector('.tracking-form-container');
    formContainer.insertBefore(errorDiv, formContainer.firstChild);
    
    // Remove error message after 5 seconds
    setTimeout(() => {
        if (errorDiv.parentNode) {
            errorDiv.parentNode.removeChild(errorDiv);
        }
    }, 5000);
}

// Helper function to adjust color brightness
function adjustColor(color, amount) {
    const usePound = color[0] === '#';
    const col = usePound ? color.slice(1) : color;
    const num = parseInt(col, 16);
    let r = (num >> 16) + amount;
    let g = (num >> 8 & 0x00FF) + amount;
    let b = (num & 0x0000FF) + amount;
    r = r > 255 ? 255 : r < 0 ? 0 : r;
    g = g > 255 ? 255 : g < 0 ? 0 : g;
    b = b > 255 ? 255 : b < 0 ? 0 : b;
    return (usePound ? '#' : '') + (r << 16 | g << 8 | b).toString(16).padStart(6, '0');
}

// Smooth scrolling for navigation links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// Add some sample tracking numbers for easy testing
document.addEventListener('DOMContentLoaded', function() {
    // Add clickable sample tracking numbers
    const trackingInput = document.getElementById('trackingNumber');
    const sampleNumbers = document.createElement('div');
    sampleNumbers.className = 'sample-numbers';
    sampleNumbers.style.cssText = `
        margin-top: 10px;
        font-size: 0.9rem;
        color: #666;
    `;
    sampleNumbers.innerHTML = `
        <p>Try these sample tracking numbers:</p>
        <div style="display: flex; gap: 10px; flex-wrap: wrap; margin-top: 5px;">
            <span style="cursor: pointer; color: #1e3c72; text-decoration: underline;" onclick="document.getElementById('trackingNumber').value='MAX123456789'">MAX123456789</span>
            <span style="cursor: pointer; color: #1e3c72; text-decoration: underline;" onclick="document.getElementById('trackingNumber').value='MAX987654321'">MAX987654321</span>
            <span style="cursor: pointer; color: #1e3c72; text-decoration: underline;" onclick="document.getElementById('trackingNumber').value='MAX555666777'">MAX555666777</span>
            <span style="cursor: pointer; color: #1e3c72; text-decoration: underline;" onclick="document.getElementById('trackingNumber').value='MAX111222333'">MAX111222333</span>
            <span style="cursor: pointer; color: #1e3c72; text-decoration: underline;" onclick="document.getElementById('trackingNumber').value='MAX444555666'">MAX444555666</span>
        </div>
    `;
    
    const formGroup = document.querySelector('.form-group');
    formGroup.appendChild(sampleNumbers);
});

// Add keyboard shortcut for tracking (Ctrl+Enter)
document.addEventListener('keydown', function(e) {
    if (e.ctrlKey && e.key === 'Enter') {
        const trackingNumber = document.getElementById('trackingNumber').value.trim();
        if (trackingNumber) {
            trackingForm.dispatchEvent(new Event('submit'));
        }
    }
});

// Auto-focus on tracking input when page loads
window.addEventListener('load', function() {
    document.getElementById('trackingNumber').focus();
});

// Add animation to status badge
function animateStatusBadge() {
    const statusBadge = document.getElementById('statusBadge');
    if (statusBadge) {
        statusBadge.style.animation = 'pulse 2s infinite';
        setTimeout(() => {
            statusBadge.style.animation = '';
        }, 4000);
    }
}

// Call animation when results are displayed
const originalDisplayTrackingResults = displayTrackingResults;
displayTrackingResults = function(trackingNumber, data) {
    originalDisplayTrackingResults(trackingNumber, data);
    setTimeout(animateStatusBadge, 100);
};
