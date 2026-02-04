-- Email Verification System - SQL Setup Script
-- Paste this entire code into phpMyAdmin SQL tab and execute

-- 1. Add columns to users table if they don't exist
ALTER TABLE `users` 
ADD COLUMN IF NOT EXISTS `is_email_verified` TINYINT(1) NOT NULL DEFAULT 0 AFTER `user_status`,
ADD COLUMN IF NOT EXISTS `email_verified_at` TIMESTAMP NULL AFTER `is_email_verified`;

-- 2. Create email_verification_tokens table
CREATE TABLE IF NOT EXISTS `email_verification_tokens` (
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

-- 3. Create password_reset_tokens table (for future password reset feature)
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
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

-- 4. User profile schema updates
ALTER TABLE `users`
ADD COLUMN IF NOT EXISTS `address` TEXT NULL AFTER `contact_num`;

ALTER TABLE `users`
ADD COLUMN IF NOT EXISTS `profile_picture` VARCHAR(255) NULL AFTER `address`;

ALTER TABLE `users`
DROP COLUMN IF EXISTS `mname`;

-- 5. Ensure users primary key auto-increments correctly
ALTER TABLE `users`
MODIFY `user_id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT;

-- 6. Ensure OTP token primary key auto-increments correctly
ALTER TABLE `email_verification_tokens`
MODIFY `token_id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT;

-- 7. Ensure audit trail primary key auto-increments correctly
ALTER TABLE `audit_trail`
MODIFY `audit_trail_id` BIGINT(20) NOT NULL AUTO_INCREMENT;

-- 8. Normalize user roles to 3 categories: Admin, Employee, Customer
-- Legacy level 3 users are merged into Employee (level 2)
UPDATE `users`
SET `user_level_id` = 2
WHERE `user_level_id` = 3;

-- Keep legacy IDs for compatibility, but only 3 active categories
UPDATE `user_levels` SET `user_type_name` = 'Admin', `user_type_status` = 'active' WHERE `user_level_id` = 1;
UPDATE `user_levels` SET `user_type_name` = 'Employee', `user_type_status` = 'active' WHERE `user_level_id` = 2;
UPDATE `user_levels` SET `user_type_name` = 'Employee', `user_type_status` = 'inactive' WHERE `user_level_id` = 3;
UPDATE `user_levels` SET `user_type_name` = 'Customer', `user_type_status` = 'active' WHERE `user_level_id` = 4;

-- 9. Supplier database
CREATE TABLE IF NOT EXISTS `suppliers` (
    `supplier_id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `supplier_name` VARCHAR(255) NOT NULL,
    `contact_person` VARCHAR(255) DEFAULT NULL,
    `email` VARCHAR(255) DEFAULT NULL,
    `phone` VARCHAR(50) DEFAULT NULL,
    `address` TEXT DEFAULT NULL,
    `city` VARCHAR(100) DEFAULT NULL,
    `province` VARCHAR(100) DEFAULT 'Pampanga',
    `status` ENUM('active','inactive') NOT NULL DEFAULT 'active',
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`supplier_id`),
    UNIQUE KEY `uniq_supplier_name` (`supplier_name`),
    KEY `idx_supplier_status` (`status`),
    KEY `idx_supplier_city` (`city`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
