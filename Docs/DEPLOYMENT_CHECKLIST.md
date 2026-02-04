# Email Verification System - Deployment Checklist

## Pre-Deployment Phase

### Code Review
- [ ] All files created successfully
- [ ] No syntax errors in PHP files
- [ ] Code follows project standards
- [ ] Security best practices implemented
- [ ] Error handling implemented

### Testing Environment
- [ ] System tested locally
- [ ] Email configuration working
- [ ] Database migration successful
- [ ] All API endpoints tested
- [ ] Audit trail working

---

## Deployment Phase

### Step 1: File Deployment (5 minutes)

Upload the following files to your server:

**Configuration:**
- [ ] `config/email_config.php` - Email settings

**Libraries:**
- [ ] `library/EmailService.php` - Email sending
- [ ] `library/TokenGenerator.php` - Token generation
- [ ] `library/EmailVerificationHelper.php` - Helper functions

**Database:**
- [ ] `database/migration_email_verification.php` - Migration script

**Backend APIs:**
- [ ] `backend/verify_email.php` - Email verification
- [ ] `backend/resend_verification_email.php` - Resend verification
- [ ] `backend/register.php` - Updated registration

**Documentation:**
- [ ] `EMAIL_VERIFICATION_SETUP.md` - Setup guide
- [ ] `IMPLEMENTATION_SUMMARY.md` - Implementation details
- [ ] `QUICK_REFERENCE.md` - Quick reference
- [ ] `DEPLOYMENT_CHECKLIST.md` - This file

### Step 2: Configuration (10 minutes)

**Email Configuration:**
- [ ] Open `config/email_config.php`
- [ ] Update `SMTP_USERNAME` with your email
- [ ] Update `SMTP_PASSWORD` with your app password
- [ ] Update `MAIL_FROM_ADDRESS` 
- [ ] Update `MAIL_FROM_NAME`
- [ ] Test SMTP connection if possible
- [ ] Verify `MAIL_DRIVER` is set correctly

**Gmail Setup (if using Gmail):**
- [ ] Enable 2-Factor Authentication
- [ ] Generate App Password
- [ ] Copy 16-character password to config
- [ ] Test email sending

**Other Email Providers:**
- [ ] Find SMTP settings for your provider
- [ ] Update SMTP_HOST and SMTP_PORT
- [ ] Test connection

### Step 3: Database Migration (5 minutes)

**Run Migration:**
```bash
php database/migration_email_verification.php
```

**Or Run SQL Manually:**
- [ ] Connect to database
- [ ] Copy SQL from EMAIL_VERIFICATION_SETUP.md
- [ ] Execute all SQL statements
- [ ] Verify tables created: `email_verification_tokens`, `password_reset_tokens`
- [ ] Verify columns added to `users`: `is_email_verified`, `email_verified_at`
- [ ] Check indexes created correctly

**Verify Database:**
```sql
-- Check new tables exist
SHOW TABLES LIKE 'email_verification%';
SHOW TABLES LIKE 'password_reset%';

-- Check user table modifications
DESCRIBE users;

-- Should see:
-- is_email_verified | tinyint(1)
-- email_verified_at | timestamp
```

### Step 4: File Permissions

- [ ] Set correct file permissions (755 for directories, 644 for files)
- [ ] Ensure web server can read/write to necessary directories
- [ ] Check error_log directory is writable

### Step 5: Testing (15 minutes)

**Test Email Configuration:**
- [ ] Check SMTP settings are valid
- [ ] Test email sending capability
- [ ] Verify emails are received in inbox
- [ ] Check for spam/junk folder

**Test Registration Flow:**
- [ ] Create test user account
- [ ] Verify user created in database with `is_email_verified = 0`
- [ ] Check verification email received
- [ ] Verify email contains correct link
- [ ] Verify token stored in database

**Test Email Verification:**
- [ ] Click verification link in email
- [ ] Confirm success message displayed
- [ ] Verify user marked as verified in database (`is_email_verified = 1`)
- [ ] Check `email_verified_at` timestamp set
- [ ] Check token marked as used

**Test Resend Email:**
- [ ] Try clicking old verification link (should fail)
- [ ] Request new verification email
- [ ] Verify new email received
- [ ] Verify new token works
- [ ] Check old token is invalidated

**Test Error Handling:**
- [ ] Try accessing with invalid token (should fail gracefully)
- [ ] Try accessing with expired token (should fail gracefully)
- [ ] Try using same token twice (should fail second time)
- [ ] Check error messages are user-friendly

**Test Audit Trail:**
- [ ] Verify registration logged
- [ ] Verify verification logged
- [ ] Verify resend request logged
- [ ] Check IP address captured
- [ ] Check user agent captured

---

## Post-Deployment Phase

### Step 1: Monitoring (Day 1)

**Monitor for Errors:**
- [ ] Check PHP error logs
- [ ] Check database error logs
- [ ] Check SMTP connection errors
- [ ] Monitor audit trail for issues

**Monitor Registration Activity:**
- [ ] Track number of registrations
- [ ] Check email delivery rate
- [ ] Monitor verification completion rate
- [ ] Watch for unusual patterns

**Monitor Email Sending:**
- [ ] Check if emails are being delivered
- [ ] Monitor SMTP errors
- [ ] Check email queue (if applicable)
- [ ] Verify no duplicate emails sent

### Step 2: Database Maintenance (Weekly)

**Clean Up Expired Tokens:**
```php
require 'database/connect_database.php';
require 'library/EmailVerificationHelper.php';
$helper = new EmailVerificationHelper($pdo);
$deleted = $helper->cleanupExpiredTokens();
echo "Deleted $deleted expired tokens";
```

**Check Statistics:**
```sql
SELECT 
    COUNT(*) as total_users,
    SUM(is_email_verified) as verified,
    ROUND(SUM(is_email_verified)/COUNT(*)*100, 2) as verification_rate
FROM users WHERE user_level_id = 4;
```

### Step 3: Performance Tuning (After 1 week)

- [ ] Monitor database query performance
- [ ] Check if indexes are being used
- [ ] Monitor email sending speed
- [ ] Check server resource usage
- [ ] Optimize slow queries if needed

### Step 4: Security Review (After 1 week)

- [ ] Review audit trail for suspicious activity
- [ ] Check for token manipulation attempts
- [ ] Monitor for email spoofing attempts
- [ ] Review rate limiting effectiveness
- [ ] Check access logs for unusual patterns

---

## Optional Integration Updates

### Update Login Page (Recommended)
In `backend/login.php`, add verification check:
- [ ] Check if user's email is verified
- [ ] Show appropriate message if not verified
- [ ] Optionally prevent unverified login
- [ ] Provide resend link if helpful

### Update Registration Form (Recommended)
In your registration form, add:
- [ ] Success message about email verification
- [ ] Link to resend email if needed
- [ ] Instructions to check email
- [ ] Link to verification status page

### Update User Dashboard (Optional)
Add to user profile/dashboard:
- [ ] Display email verification status
- [ ] Show email verified timestamp
- [ ] Provide resend button if needed
- [ ] Allow email change with re-verification

### Create Admin Panel Features (Optional)
In admin area, add:
- [ ] View verification statistics
- [ ] List unverified users
- [ ] Force verify user button
- [ ] View email verification audit log
- [ ] Manual token generation tool

---

## Troubleshooting During Deployment

### Emails Not Sending

**Check SMTP Configuration:**
```bash
# Test SMTP connection
telnet smtp.gmail.com 587
# Should show connection response
```

**Check Error Logs:**
```bash
# View PHP errors
tail -f /var/log/php-errors.log

# View mail logs
tail -f /var/log/mail.log
```

**Solutions:**
- [ ] Verify SMTP credentials
- [ ] Check firewall allows port 587 or 465
- [ ] Try different SMTP port
- [ ] Enable less secure app access (if Gmail)
- [ ] Use App Password (if Gmail)
- [ ] Check server can resolve SMTP hostname

### Database Migration Fails

**Check Database Connection:**
```php
try {
    echo $pdo->getAttribute(PDO::ATTR_SERVER_VERSION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
```

**Solutions:**
- [ ] Verify database credentials
- [ ] Check MySQL is running
- [ ] Verify user has CREATE TABLE permissions
- [ ] Check disk space
- [ ] Review SQL syntax in migration

### Verification Link Not Working

**Troubleshooting Steps:**
- [ ] Check token exists in database
- [ ] Check token hasn't expired
- [ ] Check token hash matches
- [ ] Verify SQL query working
- [ ] Check for URL encoding issues

---

## Rollback Plan

If issues occur, you can rollback:

### Option 1: Disable Email Verification
In `backend/register.php`, temporarily:
```php
// Comment out email verification code
// Users will be marked as verified immediately
// $stmt->execute([... 'is_email_verified' => 1 ...]);
```

### Option 2: Revert Database
```sql
-- Drop new tables
DROP TABLE email_verification_tokens;
DROP TABLE password_reset_tokens;

-- Remove new columns from users
ALTER TABLE users 
DROP COLUMN is_email_verified,
DROP COLUMN email_verified_at;
```

### Option 3: Restore Backup
If you have backups, restore the database from before migration.

---

## Success Criteria

Project is successfully deployed when:

- [x] All files deployed successfully
- [x] SMTP configuration working
- [x] Database migration completed
- [x] Test user registration works
- [x] Verification email sent and received
- [x] Email verification successful
- [x] User can login after verification
- [x] Resend email works
- [x] Token expiration works
- [x] Audit trail logging works
- [x] No errors in logs
- [x] All tests passing
- [x] Performance acceptable
- [x] Security features working

---

## Communication Plan

### For Users
- [ ] Update registration page with verification instructions
- [ ] Send notification about new verification requirement
- [ ] Provide help/FAQ about email verification
- [ ] Create support ticket template for verification issues

### For Support Team
- [ ] Train support on email verification system
- [ ] Provide troubleshooting guide
- [ ] Document common issues and solutions
- [ ] Set up monitoring alerts

### For Developers
- [ ] Review implementation with team
- [ ] Provide access to documentation
- [ ] Set up monitoring dashboards
- [ ] Schedule follow-up review

---

## Sign-Off

When deployment is complete, get approval from:

| Role | Name | Date | Signature |
|------|------|------|-----------|
| Developer | | | |
| QA Lead | | | |
| Project Manager | | | |
| System Admin | | | |

---

## Post-Deployment Review (1 week later)

Schedule a review meeting to discuss:

- [ ] How many users have registered?
- [ ] What's the verification completion rate?
- [ ] Have there been any issues?
- [ ] Email delivery rate?
- [ ] User feedback?
- [ ] Performance metrics?
- [ ] Any security concerns?
- [ ] Optimization opportunities?

---

**Document Completed**: ____________  
**Deployed By**: ____________  
**Deployment Date**: ____________  
**Go-Live Date**: ____________  
**Status**: ☐ Successful  ☐ Needs Review  ☐ Rollback Needed

---

For questions or issues, refer to:
- [EMAIL_VERIFICATION_SETUP.md](EMAIL_VERIFICATION_SETUP.md) - Full setup guide
- [QUICK_REFERENCE.md](QUICK_REFERENCE.md) - Quick commands
- Code comments in source files
