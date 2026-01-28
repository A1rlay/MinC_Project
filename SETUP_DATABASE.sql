-- Email Verification System - SQL Setup Script
-- Paste this entire code into phpMyAdmin SQL tab and execute

-- 1. Add columns to users table if they don't exist
ALTER TABLE `users` 
ADD COLUMN `is_email_verified` TINYINT(1) NOT NULL DEFAULT 0 AFTER `user_status`,
ADD COLUMN `email_verified_at` TIMESTAMP NULL AFTER `is_email_verified`;

-- 2. Create email_verification_tokens table
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

-- 3. Create password_reset_tokens table (for future password reset feature)
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
