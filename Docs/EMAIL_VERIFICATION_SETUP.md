# Email Verification System - Setup Guide

## Overview
This email verification system has been implemented to prevent fake emails during user registration. When users register, they must verify their email address before they can fully use their account.

## System Components

### Files Created

1. **config/email_config.php** - SMTP/Email configuration settings
2. **library/EmailService.php** - Email sending utility class
3. **library/TokenGenerator.php** - Secure token generation utility
4. **database/migration_email_verification.php** - Database migration script
5. **backend/verify_email.php** - Email verification endpoint
6. **backend/resend_verification_email.php** - Resend verification email endpoint
7. **backend/register.php** - Modified to include email verification (ALREADY UPDATED)

## Installation Steps

### Step 1: Configure Email Settings

Edit `config/email_config.php` and update your SMTP settings:

```php
define('SMTP_HOST', 'smtp.gmail.com');          // Your SMTP server
define('SMTP_PORT', 587);                        // SMTP port
define('SMTP_USERNAME', 'your-email@gmail.com'); // Your email address
define('SMTP_PASSWORD', 'your-app-password');    // Your app password
define('MAIL_FROM_ADDRESS', 'noreply@minc.com');
define('MAIL_FROM_NAME', 'MinC - Automotive Parts');
```

#### For Gmail Users:
1. Enable 2-Factor Authentication on your Google Account
2. Generate an App Password: https://myaccount.google.com/apppasswords
3. Use the 16-character app password in SMTP_PASSWORD

#### For Other Email Providers:
- Check your email provider's SMTP settings
- Update SMTP_HOST and SMTP_PORT accordingly
- Use your email credentials

### Step 2: Run Database Migration

Run the migration script to create necessary database tables:

```bash
php database/migration_email_verification.php
```

Or manually run the following SQL queries in your database:

```sql
-- Add columns to users table
ALTER TABLE `users` 
ADD COLUMN `is_email_verified` TINYINT(1) NOT NULL DEFAULT 0 AFTER `user_status`,
ADD COLUMN `email_verified_at` TIMESTAMP NULL AFTER `is_email_verified`;

-- Create email verification tokens table
CREATE TABLE `email_verification_tokens` (
    `token_id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` BIGINT(20) UNSIGNED NOT NULL,
    `token` VARCHAR(255) NOT NULL UNIQUE,
    `token_hash` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `expires_at` TIMESTAMP NOT NULL,
    `verified_at` TIMESTAMP NULL,
    `is_used` TINYINT(1) NOT NULL DEFAULT 0,
    PRIMARY KEY (`token_id`),
    UNIQUE KEY `unique_token` (`token`),
    KEY `user_id` (`user_id`),
    KEY `email` (`email`),
    KEY `expires_at` (`expires_at`),
    CONSTRAINT `fk_verification_tokens_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create password reset tokens table (for future use)
CREATE TABLE `password_reset_tokens` (
    `reset_id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` BIGINT(20) UNSIGNED NOT NULL,
    `token` VARCHAR(255) NOT NULL UNIQUE,
    `token_hash` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `expires_at` TIMESTAMP NOT NULL,
    `used_at` TIMESTAMP NULL,
    `is_used` TINYINT(1) NOT NULL DEFAULT 0,
    PRIMARY KEY (`reset_id`),
    UNIQUE KEY `unique_token` (`token`),
    KEY `user_id` (`user_id`),
    KEY `expires_at` (`expires_at`),
    CONSTRAINT `fk_reset_tokens_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Step 3: Update Login Logic (Optional)

If you want to prevent unverified users from logging in, update `backend/login.php`:

```php
// After user authentication, check if email is verified
if ($user['is_email_verified'] == 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Please verify your email address before logging in.',
        'action' => 'verify_email',
        'user_id' => $user['user_id'],
        'email' => $user['email']
    ]);
    exit;
}
```

### Step 4: Update Registration Form (Optional)

Update your registration form frontend to show verification status:

```javascript
// In your registration success handler
if (!response.email_verified) {
    showMessage('Please check your email to verify your account!', 'info');
    // Optionally redirect to verification page or show resend button
}
```

## How It Works

### Registration Flow:
1. User fills out registration form
2. User submits registration
3. System creates user account with `is_email_verified = 0`
4. System generates a secure token and stores it in the database
5. System sends verification email with a unique link
6. User clicks the link in the email
7. System verifies the token and marks the email as verified
8. User receives a welcome email and can now fully use their account

### Email Verification Flow:
1. User clicks verification link from email
2. System checks token validity (not expired, not used, matches hash)
3. System marks user email as verified
4. System marks token as used
5. User is redirected to login page
6. Welcome email is sent

### Resend Verification Email Flow:
1. User requests verification email resend
2. System checks user status (not already verified)
3. System invalidates old tokens
4. System generates new token
5. System sends new verification email
6. Old email links no longer work (security feature)

## API Endpoints

### 1. Register User
- **Endpoint**: `/backend/register.php`
- **Method**: POST
- **Body**:
  ```json
  {
    "fname": "John",
    "lname": "Doe",
    "email": "john@example.com",
    "password": "securepass123"
  }
  ```
- **Response**:
  ```json
  {
    "success": true,
    "message": "Registration successful! Please check your email to verify your account.",
    "user_id": 10,
    "email_verified": false,
    "email_sent": true
  }
  ```

### 2. Verify Email
- **Endpoint**: `/backend/verify_email.php?token=xxx`
- **Method**: GET or POST
- **Response**:
  ```json
  {
    "success": true,
    "message": "Email verified successfully! You can now login to your account.",
    "redirectToLogin": true
  }
  ```

### 3. Resend Verification Email
- **Endpoint**: `/backend/resend_verification_email.php`
- **Method**: POST
- **Body**:
  ```json
  {
    "email": "john@example.com"
  }
  ```
- **Response**:
  ```json
  {
    "success": true,
    "message": "Verification email has been sent to john@example.com. Please check your inbox.",
    "email_sent": true
  }
  ```

## Security Features

1. **Token Generation**: Uses cryptographically secure `random_bytes()` function
2. **Token Storage**: Tokens are hashed in database using SHA-256
3. **Token Expiration**: Tokens expire after 24 hours
4. **One-Time Use**: Tokens can only be used once
5. **Rate Limiting**: Prevents spam by limiting resend requests to once per 5 minutes
6. **Audit Trail**: All email verification actions are logged in the audit_trail table
7. **Email Validation**: Server-side validation of email addresses
8. **Secure Links**: Verification links include random tokens, not user IDs

## Testing

### Test Email Verification:

1. **Local Testing**:
   - Without proper SMTP configuration, emails will be logged to error_log instead of sent
   - Check `error_log()` for test messages
   - Manually copy the verification link from logs

2. **With Fake SMTP Service**:
   - Use services like Mailtrap.io or MailHog for testing
   - Update `config/email_config.php` with test SMTP credentials
   - Emails will appear in the test service dashboard

3. **Production Testing**:
   - Use a real SMTP service (Gmail, SendGrid, AWS SES, etc.)
   - Send test emails and verify delivery

## Customization

### Change Email Templates:
Edit `library/EmailService.php`:
- `getVerificationEmailTemplate()` - Verification email design
- `getWelcomeEmailTemplate()` - Welcome email after verification
- `getPasswordResetTemplate()` - Password reset email (for future use)

### Change Token Expiration:
In `config/email_config.php`:
```php
define('EMAIL_VERIFICATION_TIMEOUT', 24); // Change hours value
```

### Change Email Sender Details:
In `config/email_config.php`:
```php
define('MAIL_FROM_ADDRESS', 'custom@yourdomain.com');
define('MAIL_FROM_NAME', 'Your Business Name');
```

## Troubleshooting

### Emails Not Sending:

1. **Check SMTP Configuration**:
   - Verify `config/email_config.php` has correct credentials
   - For Gmail: Ensure App Password is used (not regular password)
   - Check PHP error logs for connection errors

2. **Check Firewall/Hosting**:
   - Some hosting providers block SMTP ports
   - Contact your hosting provider to enable SMTP
   - Try port 465 (SSL) if 587 (TLS) doesn't work

3. **Fallback to PHP mail() function**:
   - Set `MAIL_DRIVER` to 'mail' in config
   - Requires server-side mail configuration
   - Less reliable than SMTP

### Verification Link Not Working:

1. **Check Token in Database**:
   ```sql
   SELECT * FROM email_verification_tokens 
   WHERE user_id = YOUR_USER_ID 
   ORDER BY created_at DESC LIMIT 1;
   ```

2. **Check Token Expiration**:
   - Verify `expires_at` is in the future
   - Generate new token using resend endpoint

3. **Check User Status**:
   ```sql
   SELECT * FROM users WHERE user_id = YOUR_USER_ID;
   ```

### Can't Connect to SMTP:

1. **For Gmail**:
   - Enable "Less secure app access" OR use App Passwords
   - Verify you're using `smtp.gmail.com` with port 587
   - Use TLS encryption

2. **For Other Providers**:
   - Check their documentation for SMTP settings
   - Verify firewall allows outbound connections on SMTP port
   - Test connection using telnet command

## Monitoring & Maintenance

### Check Email Verification Status:
```sql
SELECT user_id, email, is_email_verified, email_verified_at, created_at 
FROM users 
WHERE user_level_id = 4 
ORDER BY created_at DESC;
```

### Clean Up Expired Tokens:
```sql
DELETE FROM email_verification_tokens 
WHERE expires_at < NOW() AND is_used = 0;
```

### View Verification Audit Trail:
```sql
SELECT * FROM audit_trail 
WHERE action IN ('email_verified', 'resend_verification_email') 
ORDER BY timestamp DESC;
```

## Future Enhancements

1. **Two-Factor Authentication**: Add SMS/authenticator app verification
2. **Password Reset**: Use the `password_reset_tokens` table for password reset functionality
3. **Email Change**: Allow verified users to change email with re-verification
4. **Newsletter Signup**: Add optional newsletter subscription during registration
5. **Social Authentication**: Add Google/Facebook login options
6. **Advanced Analytics**: Track verification rates and conversion metrics

## Support & Documentation

For more information:
- Check email provider's SMTP documentation
- Review the source code comments in each file
- Check database audit_trail for error logs
- Review PHP error_log for system messages

---

**Version**: 1.0  
**Last Updated**: 2026-01-28  
**Compatibility**: PHP 7.0+  
**Database**: MySQL 5.7+
