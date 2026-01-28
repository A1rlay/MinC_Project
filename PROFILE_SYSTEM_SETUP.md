# User Profile Management System - Setup Guide

## Overview
The user profile management system allows users to:
- View their profile information (name, email, contact number)
- Edit personal details (first name, last name, middle name, contact number)
- Upload a profile picture (JPG, PNG, WebP)
- Delete their profile picture
- View all changes in the audit trail

## Files Created

### 1. Database Migration
- **File**: `database/add_profile_picture_column.sql`
- **Purpose**: SQL script to add `profile_picture` column to users table
- **Action Required**: Execute this in phpMyAdmin

### 2. Backend API Endpoints

#### `backend/get_profile.php`
- **Method**: GET
- **Authentication**: Required (session)
- **Response**: JSON with user profile data
- **Returns**: user_id, fname, mname, lname, email, contact_num, profile_picture, profile_picture_url

#### `backend/update_profile.php`
- **Method**: POST
- **Authentication**: Required (session)
- **Body**: JSON with fname, lname, mname, contact_num
- **Validation**:
  - First name and last name are required
  - Contact number format validation
  - All changes logged to audit trail

#### `backend/upload_profile_picture.php`
- **Method**: POST (multipart/form-data)
- **Authentication**: Required (session)
- **File Input**: profile_picture
- **Validations**:
  - File types: JPG, PNG, WebP only
  - Max file size: 5MB
  - Automatically deletes old profile picture
  - Stores in: `Assets/images/profiles/`
  - Filename format: `profile_[user_id]_[timestamp].[ext]`

#### `backend/delete_profile_picture.php`
- **Method**: POST
- **Authentication**: Required (session)
- **Action**: Removes profile picture from filesystem and database

### 3. Frontend Page
- **File**: `html/profile.php`
- **Features**:
  - Responsive design (mobile & desktop)
  - Real-time form validation
  - Profile picture preview with upload
  - Edit form for personal details
  - Success/error message alerts
  - Loading states during operations

## Setup Instructions

### Step 1: Execute Database Migration
```bash
# In phpMyAdmin:
1. Select 'minc' database
2. Go to SQL tab
3. Copy and paste contents of add_profile_picture_column.sql
4. Execute query
```

OR run in terminal:
```bash
mysql -u root -p minc < database/add_profile_picture_column.sql
```

### Step 2: Verify Directory Structure
```
Assets/
  images/
    profiles/          <- Created automatically, ensure writeable
```

The system automatically creates the directory if it doesn't exist, but verify it has write permissions (755 or 777).

### Step 3: Test the System

#### Access Profile Page
```
http://localhost/pages/MinC_Project/html/profile.php
```

#### Test Scenarios
1. **View Profile**: Page loads and displays current user data
2. **Edit Details**: Update name/contact and save
3. **Upload Picture**: Select JPG/PNG/WebP image (< 5MB)
4. **Delete Picture**: Remove uploaded picture
5. **Validation**: Try uploading invalid file type

### Step 4: Verify Audit Trail
Check `admin/app/frontend/audit-trail.php` to see logged profile changes:
- `update_profile` - Profile details updated
- `upload_profile_picture` - Picture uploaded
- `delete_profile_picture` - Picture deleted

## API Usage Examples

### Get Profile
```bash
curl http://localhost/pages/MinC_Project/backend/get_profile.php \
  -b "PHPSESSID=your_session_id"
```

### Update Profile
```bash
curl -X POST http://localhost/pages/MinC_Project/backend/update_profile.php \
  -H "Content-Type: application/json" \
  -d '{"fname":"John","lname":"Doe","mname":"Michael","contact_num":"123-456-7890"}' \
  -b "PHPSESSID=your_session_id"
```

### Upload Profile Picture
```bash
curl -X POST http://localhost/pages/MinC_Project/backend/upload_profile_picture.php \
  -F "profile_picture=@/path/to/image.jpg" \
  -b "PHPSESSID=your_session_id"
```

### Delete Profile Picture
```bash
curl -X POST http://localhost/pages/MinC_Project/backend/delete_profile_picture.php \
  -b "PHPSESSID=your_session_id"
```

## Technical Details

### Security Features
1. **Session Validation**: All endpoints require authenticated session
2. **Input Sanitization**: All inputs are validated and sanitized
3. **File Type Validation**: Uses MIME type checking with finfo
4. **File Size Limits**: Maximum 5MB per image
5. **Audit Logging**: All changes logged with user, IP, and timestamp
6. **CSRF Protection**: Could be added with token validation

### Database Schema
```sql
ALTER TABLE users ADD COLUMN profile_picture VARCHAR(255) DEFAULT NULL AFTER contact_num;
```

### Error Handling
- Comprehensive error messages returned as JSON
- All errors logged to PHP error log
- Graceful fallback to default avatar if picture missing
- User-friendly frontend notifications

### Performance Considerations
- Images stored separately from database (filesystem)
- Direct database queries for profile operations
- Automatic cleanup of old profile pictures
- URL parameter cache busting for image refresh

## Troubleshooting

### Profile Picture Not Uploading
1. Check `Assets/images/profiles/` directory exists and is writable
2. Verify file size < 5MB
3. Check file type (JPG, PNG, WebP only)
4. Check PHP error logs for details

### Changes Not Saving
1. Verify session is active (login required)
2. Check browser console for API errors
3. Verify database connection in `database/connect_database.php`
4. Check PHP error logs

### Image Not Displaying
1. Verify file uploaded to `Assets/images/profiles/`
2. Check file permissions (readable by web server)
3. Try clearing browser cache
4. Check if old picture was properly deleted

## Feature Enhancements (Optional)

### Suggested Improvements
1. **Password Change**: Add password change endpoint
2. **Image Cropping**: Pre-crop profile images before upload
3. **Image Compression**: Automatically compress images
4. **Multiple Images**: Support multiple profile photos
5. **Email Verification**: Already implemented in email system
6. **Account Deletion**: Self-service account termination
7. **Export Profile**: Download profile data as PDF/JSON

## Integration with Existing Systems

### Email Verification System
The profile system works alongside the existing email verification:
- Users must be verified before accessing profile
- Email cannot be changed from profile page (prevents bypass)
- Email changes could require re-verification if implemented

### Audit Trail
All profile changes are logged in the audit_trail table:
- Action: `update_profile`, `upload_profile_picture`, `delete_profile_picture`
- User information and IP address recorded
- Timestamp and change details preserved

### User Management
- Integration with existing user levels (1=IT, 2=Owner, 3=Manager, 4=Consumer)
- Users can only edit their own profile
- Admin tools in user-management section for administrative changes

## Support & Documentation

For issues or questions:
1. Check PHP error logs: `xampp/logs/php_error.log`
2. Check database logs: MySQL error logs
3. Check browser console (F12) for JavaScript errors
4. Review audit trail for action history
