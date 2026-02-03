-- Migration: Create website_traffic table for analytics
-- Run this SQL on your database to enable traffic tracking

CREATE TABLE IF NOT EXISTS `website_traffic` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `page_url` VARCHAR(500) NOT NULL,
    `page_title` VARCHAR(255) DEFAULT NULL,
    `referrer` VARCHAR(500) DEFAULT NULL,
    `user_agent` VARCHAR(500) DEFAULT NULL,
    `ip_address` VARCHAR(45) DEFAULT NULL,
    `session_id` VARCHAR(100) DEFAULT NULL,
    `user_id` INT DEFAULT NULL,
    `device_type` ENUM('desktop', 'tablet', 'mobile', 'unknown') DEFAULT 'unknown',
    `browser` VARCHAR(50) DEFAULT NULL,
    `os` VARCHAR(50) DEFAULT NULL,
    `country` VARCHAR(100) DEFAULT NULL,
    `city` VARCHAR(100) DEFAULT NULL,
    `visited_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_visited_at` (`visited_at`),
    INDEX `idx_page_url` (`page_url`(191)),
    INDEX `idx_session_id` (`session_id`),
    INDEX `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create a summary table for faster queries (optional, for high-traffic sites)
CREATE TABLE IF NOT EXISTS `website_traffic_daily` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `date` DATE NOT NULL,
    `page_url` VARCHAR(500) NOT NULL,
    `visits` INT DEFAULT 0,
    `unique_visitors` INT DEFAULT 0,
    UNIQUE KEY `idx_date_page` (`date`, `page_url`(191)),
    INDEX `idx_date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert some sample data for testing (optional)
-- You can remove this section in production
INSERT INTO `website_traffic` (`page_url`, `page_title`, `device_type`, `browser`, `visited_at`) VALUES
('/', 'Home', 'desktop', 'Chrome', DATE_SUB(NOW(), INTERVAL 1 HOUR)),
('/', 'Home', 'mobile', 'Safari', DATE_SUB(NOW(), INTERVAL 2 HOUR)),
('/about', 'About Us', 'desktop', 'Firefox', DATE_SUB(NOW(), INTERVAL 3 HOUR)),
('/', 'Home', 'desktop', 'Chrome', DATE_SUB(NOW(), INTERVAL 1 DAY)),
('/contact', 'Contact', 'tablet', 'Chrome', DATE_SUB(NOW(), INTERVAL 1 DAY)),
('/', 'Home', 'mobile', 'Safari', DATE_SUB(NOW(), INTERVAL 2 DAY)),
('/about', 'About Us', 'desktop', 'Edge', DATE_SUB(NOW(), INTERVAL 3 DAY)),
('/', 'Home', 'desktop', 'Chrome', DATE_SUB(NOW(), INTERVAL 4 DAY)),
('/', 'Home', 'desktop', 'Chrome', DATE_SUB(NOW(), INTERVAL 5 DAY)),
('/products', 'Products', 'mobile', 'Safari', DATE_SUB(NOW(), INTERVAL 6 DAY)),
('/', 'Home', 'desktop', 'Firefox', DATE_SUB(NOW(), INTERVAL 7 DAY)),
('/', 'Home', 'desktop', 'Chrome', DATE_SUB(NOW(), INTERVAL 8 DAY)),
('/about', 'About Us', 'tablet', 'Chrome', DATE_SUB(NOW(), INTERVAL 9 DAY)),
('/', 'Home', 'mobile', 'Safari', DATE_SUB(NOW(), INTERVAL 10 DAY)),
('/', 'Home', 'desktop', 'Chrome', DATE_SUB(NOW(), INTERVAL 14 DAY)),
('/', 'Home', 'desktop', 'Chrome', DATE_SUB(NOW(), INTERVAL 21 DAY)),
('/contact', 'Contact', 'desktop', 'Firefox', DATE_SUB(NOW(), INTERVAL 28 DAY));
