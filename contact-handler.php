<?php
// Enable error reporting for debugging (remove in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set content type
header('Content-Type: application/json');

// Allow CORS if needed
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);

// If JSON decode fails, try regular POST data
if (!$input) {
    $input = $_POST;
}

// Validate required fields
$required_fields = ['name', 'email', 'message'];
$errors = [];

foreach ($required_fields as $field) {
    if (empty($input[$field])) {
        $errors[] = ucfirst($field) . ' is required';
    }
}

// Validate email format
if (!empty($input['email']) && !filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Invalid email format';
}

// Return errors if any
if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
    exit;
}

// Sanitize input data
$name = htmlspecialchars(trim($input['name']));
$email = htmlspecialchars(trim($input['email']));
$phone = htmlspecialchars(trim($input['phone'] ?? ''));
$service = htmlspecialchars(trim($input['service'] ?? ''));
$message = htmlspecialchars(trim($input['message']));

// Email configuration
$to = 'enquiry@nexyzgroup.com';
$subject = 'New Contact Form Submission - Nexyz Group';

// Create email content
$email_content = "
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #4CAF50, #66BB6A); color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background: #f9f9f9; padding: 20px; }
        .field { margin-bottom: 15px; }
        .label { font-weight: bold; color: #4CAF50; }
        .value { margin-top: 5px; padding: 10px; background: white; border-left: 3px solid #4CAF50; border-radius: 4px; }
        .footer { background: #333; color: white; padding: 15px; text-align: center; font-size: 12px; border-radius: 0 0 8px 8px; }
        .urgent { background: #ffebee; border: 1px solid #f44336; padding: 10px; border-radius: 4px; margin-top: 15px; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h2>🔔 New Contact Form Submission</h2>
            <p>Nexyz Group - Creative Media Services</p>
        </div>
        
        <div class='content'>
            <div class='field'>
                <div class='label'>👤 Customer Name:</div>
                <div class='value'>$name</div>
            </div>
            
            <div class='field'>
                <div class='label'>📧 Email Address:</div>
                <div class='value'><a href='mailto:$email'>$email</a></div>
            </div>";

if (!empty($phone)) {
    $email_content .= "
            <div class='field'>
                <div class='label'>📞 Phone Number:</div>
                <div class='value'><a href='tel:$phone'>$phone</a></div>
            </div>";
}

if (!empty($service)) {
    $email_content .= "
            <div class='field'>
                <div class='label'>🎯 Service Interested In:</div>
                <div class='value'><strong>$service</strong></div>
            </div>";
}

$email_content .= "
            <div class='field'>
                <div class='label'>💬 Message:</div>
                <div class='value'>$message</div>
            </div>
            
            <div class='field'>
                <div class='label'>⏰ Submitted On:</div>
                <div class='value'>" . date('F j, Y, g:i a T') . "</div>
            </div>
            
            <div class='urgent'>
                <strong>⚡ Action Required:</strong> Please respond to this customer inquiry within 24 hours for best customer experience.
            </div>
        </div>
        
        <div class='footer'>
            <p>This email was sent from the Nexyz Group contact form.</p>
            <p><strong>Quick Reply:</strong> <a href='mailto:$email?subject=Re: Your inquiry about Nexyz Group services' style='color: #4CAF50;'>Click here to respond directly</a></p>
        </div>
    </div>
</body>
</html>";

// Email headers
$headers = [
    'MIME-Version' => '1.0',
    'Content-type' => 'text/html; charset=UTF-8',
    'From' => "Nexyz Group Website <noreply@nexyzgroup.com>",
    'Reply-To' => $email,
    'X-Mailer' => 'PHP/' . phpversion(),
    'X-Priority' => '2' // High priority
];

// Convert headers array to string
$header_string = '';
foreach ($headers as $key => $value) {
    $header_string .= "$key: $value\r\n";
}

// Send email to enquiry@nexyzgroup.com only
if (mail($to, $subject, $email_content, $header_string)) {
    echo json_encode([
        'success' => true, 
        'message' => 'Thank you for your message! We have received your inquiry and will get back to you within 24 hours.'
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Sorry, there was an error sending your message. Please try again or contact us directly at +91 76358 70800.'
    ]);
}
?>
