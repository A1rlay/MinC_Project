<?php
/**
 * Email Configuration
 * Path: config/email_config.php
 * Handles SMTP settings for sending emails
 */

// ===== EMAIL CONFIGURATION =====
// Using PHPMailer - make sure it's installed via composer or included

define('MAIL_DRIVER', 'smtp'); // Using Mailtrap for email testing

// SMTP Configuration
define('SMTP_HOST', 'sandbox.smtp.mailtrap.io');
define('SMTP_PORT', 2525);
define('SMTP_USERNAME', '64575e73c6d443');
define('SMTP_PASSWORD', '267427c7fe0ec4');
define('SMTP_ENCRYPTION', 'tls'); // Mailtrap uses TLS

// Email From Details
define('MAIL_FROM_ADDRESS', 'noreply@minc.com');
define('MAIL_FROM_NAME', 'MinC - Automotive Parts');

// Email Verification Settings
define('EMAIL_VERIFICATION_TIMEOUT', 24); // Hours before verification token expires
define('EMAIL_VERIFICATION_LINK_TIMEOUT', 24); // Hours before the link expires

// Fallback to PHP's mail() function if using 'mail' driver
if (MAIL_DRIVER === 'mail') {
    ini_set('sendmail_from', MAIL_FROM_ADDRESS);
}

// Function to check if email configuration is properly set
function isEmailConfigured() {
    return (
        defined('SMTP_USERNAME') && 
        defined('SMTP_PASSWORD') && 
        SMTP_USERNAME !== 'your-email@gmail.com' && 
        SMTP_PASSWORD !== 'your-app-password'
    );
}
?>
