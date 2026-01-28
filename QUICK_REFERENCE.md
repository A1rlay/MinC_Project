# Email Verification System - Quick Reference

## Files Created

| File | Purpose | Type |
|------|---------|------|
| `config/email_config.php` | SMTP configuration | Config |
| `library/EmailService.php` | Email sending utility | Class |
| `library/TokenGenerator.php` | Secure token generation | Class |
| `library/EmailVerificationHelper.php` | Verification helper functions | Class |
| `database/migration_email_verification.php` | Database migration | Script |
| `backend/verify_email.php` | Email verification endpoint | API |
| `backend/resend_verification_email.php` | Resend verification email | API |
| `backend/register.php` | Modified registration | API |

## File Locations

```
MinC_Project/
├── config/
│   └── email_config.php                          [NEW]
├── library/
│   ├── EmailService.php                          [NEW]
│   ├── TokenGenerator.php                        [NEW]
│   └── EmailVerificationHelper.php               [NEW]
├── database/
│   └── migration_email_verification.php          [NEW]
├── backend/
│   ├── register.php                              [MODIFIED]
│   ├── verify_email.php                          [NEW]
│   └── resend_verification_email.php             [NEW]
├── EMAIL_VERIFICATION_SETUP.md                   [NEW - Full Guide]
└── IMPLEMENTATION_SUMMARY.md                     [NEW - This Summary]
```

## Quick Start

### 1. Configuration (5 minutes)
```php
// Edit: config/email_config.php
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-app-password');
define('MAIL_FROM_ADDRESS', 'noreply@minc.com');
define('MAIL_FROM_NAME', 'MinC');
```

### 2. Database Migration (2 minutes)
```bash
php database/migration_email_verification.php
```

Or manually run the SQL in `EMAIL_VERIFICATION_SETUP.md`

### 3. Test (10 minutes)
```
1. Register a test account
2. Check email for verification link
3. Click link to verify
4. Login to account
```

## API Endpoints Reference

### POST /backend/register.php
Register new user with email verification
```json
{
  "fname": "John",
  "lname": "Doe",
  "email": "john@example.com",
  "password": "securepass"
}
```

### GET /backend/verify_email.php?token=xxx
Verify email with token from email link

### POST /backend/resend_verification_email.php
Request new verification email
```json
{
  "email": "john@example.com"
}
```

## Database Tables

### email_verification_tokens
- Stores verification tokens
- Foreign key to `users` table
- Auto-expires after 24 hours

### password_reset_tokens
- Ready for future password reset feature
- Same structure as verification tokens

### users (Modified)
- `is_email_verified` (TINYINT) - 0 or 1
- `email_verified_at` (TIMESTAMP) - When verified

## Usage Examples

### Check if email is verified
```php
require 'library/EmailVerificationHelper.php';
$helper = new EmailVerificationHelper($pdo);

if ($helper->isEmailVerified($user_id)) {
    // User email is verified
}
```

### Get verification status
```php
$status = $helper->getUserVerificationStatus($user_id);
echo $status['is_email_verified']; // 0 or 1
echo $status['email_verified_at']; // Timestamp or NULL
```

### Send verification email
```php
require 'library/EmailService.php';
$service = new EmailService();

$service->sendVerificationEmail(
    'user@example.com',
    'John Doe',
    'https://minc.com/backend/verify_email.php?token=xxx',
    'tokenvalue'
);
```

### Get statistics
```php
$stats = $helper->getVerificationStatistics();
echo $stats['verification_rate']; // 85.5
echo $stats['pending_tokens'];     // 12
```

## Security Checklist

- [x] Uses random_bytes() for token generation
- [x] Tokens hashed with SHA-256 before storage
- [x] One-time use enforcement
- [x] 24-hour expiration
- [x] HTTPS-ready
- [x] SQL injection protected (prepared statements)
- [x] Email validation
- [x] Rate limiting on resend
- [x] Audit trail logging
- [x] Hash comparison timing-attack protected

## Customization Quick Guide

### Change email templates
Edit in `library/EmailService.php`:
- `getVerificationEmailTemplate()`
- `getWelcomeEmailTemplate()`
- `getPasswordResetTemplate()`

### Change token expiration
In `config/email_config.php`:
```php
define('EMAIL_VERIFICATION_TIMEOUT', 48); // 48 hours instead of 24
```

### Change email sender
In `config/email_config.php`:
```php
define('MAIL_FROM_ADDRESS', 'support@yourdomain.com');
define('MAIL_FROM_NAME', 'Your Company');
```

### Add to login requirement
In `backend/login.php`:
```php
// After user authentication
if ($user['is_email_verified'] == 0) {
    // Reject login or allow with warning
}
```

## Troubleshooting Quick Fixes

| Issue | Solution |
|-------|----------|
| Emails not sending | Check `config/email_config.php` SMTP settings |
| Token expires immediately | Check database `expires_at` field |
| Verification link invalid | Verify token exists and isn't already used |
| SMTP connection error | Check firewall allows port 587 or 465 |
| Gmail not working | Use App Password, not regular password |

## Monitoring Commands

```sql
-- Check verification rate
SELECT 
    COUNT(*) as total,
    SUM(is_email_verified) as verified,
    ROUND(SUM(is_email_verified)/COUNT(*)*100, 2) as rate
FROM users WHERE user_level_id = 4;

-- Get unverified users
SELECT fname, lname, email, created_at 
FROM users 
WHERE is_email_verified = 0 
AND user_level_id = 4
ORDER BY created_at DESC;

-- Check pending tokens
SELECT COUNT(*) as pending 
FROM email_verification_tokens 
WHERE is_used = 0 AND expires_at > NOW();

-- View verification audit trail
SELECT action, entity_id, change_reason, timestamp 
FROM audit_trail 
WHERE action IN ('email_verified', 'resend_verification_email')
ORDER BY timestamp DESC;
```

## Common Integration Points

### Login Page
Show verification status/reminder

### User Dashboard
Display email verification status and allow resend

### Admin Panel
Manage unverified users, force verify, view stats

### Email Configuration
Update SMTP settings

### Password Reset
Use similar token system (already prepared)

## Performance Tips

1. **Index maintenance**
   ```sql
   -- Tokens table has indexes on:
   -- token, user_id, email, expires_at
   ```

2. **Regular cleanup**
   ```php
   $helper->cleanupExpiredTokens(); // Run weekly
   ```

3. **Query optimization**
   - All queries use prepared statements
   - Indexes on lookup fields
   - Consider caching verification status

## Code Quality

- ✅ Well-commented code
- ✅ Error handling throughout
- ✅ Logging of all operations
- ✅ Consistent naming conventions
- ✅ PDO prepared statements
- ✅ Transaction support
- ✅ Fallback mechanisms

## Testing Scenarios

1. **Happy Path**
   - Register → Verify email → Login ✓

2. **Resend Email**
   - Register → Wait/Delete email → Resend → Verify ✓

3. **Token Expiration**
   - Wait 24+ hours → Try old link → Should fail ✓

4. **Rate Limiting**
   - Resend immediately after 1st send → Should fail ✓

5. **Invalid Token**
   - Manually craft token → Should fail ✓

## Deployment Checklist

- [ ] Copy all files to production
- [ ] Update email_config.php
- [ ] Run database migration
- [ ] Test email sending
- [ ] Monitor first registrations
- [ ] Check audit trail
- [ ] Update documentation
- [ ] Train support team
- [ ] Monitor for errors

## Support Resources

- [EMAIL_VERIFICATION_SETUP.md](EMAIL_VERIFICATION_SETUP.md) - Full setup guide
- [IMPLEMENTATION_SUMMARY.md](IMPLEMENTATION_SUMMARY.md) - Detailed implementation
- Code comments in each file
- Audit trail for debugging

## Version Info

- **Version**: 1.0
- **Created**: 2026-01-28
- **PHP Version**: 7.0+
- **MySQL Version**: 5.7+
- **Status**: Production Ready

---

**Need help?** Check the full setup guide or review code comments!
