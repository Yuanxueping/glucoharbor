-- GlucoHarbor Database Schema
-- Run: mysql -u root -p glucoharbor < install.sql

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` enum('admin','editor') DEFAULT 'editor',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `icon` varchar(100) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `meta_title` varchar(200) DEFAULT NULL,
  `meta_description` varchar(500) DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `articles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(300) NOT NULL,
  `slug` varchar(300) NOT NULL,
  `excerpt` text DEFAULT NULL,
  `content` longtext DEFAULT NULL,
  `featured_image` varchar(500) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `author_id` int(11) DEFAULT NULL,
  `status` enum('draft','published','archived') DEFAULT 'draft',
  `views` int(11) DEFAULT 0,
  `is_featured` tinyint(1) DEFAULT 0,
  `source` enum('manual','api','ai') DEFAULT 'manual',
  `meta_title` varchar(300) DEFAULT NULL,
  `meta_description` varchar(500) DEFAULT NULL,
  `meta_keywords` varchar(300) DEFAULT NULL,
  `published_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_category` (`category_id`),
  KEY `idx_status` (`status`),
  KEY `idx_published` (`published_at`),
  FULLTEXT KEY `ft_search` (`title`,`excerpt`,`content`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `article_tags` (
  `article_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL,
  PRIMARY KEY (`article_id`,`tag_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `product_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(300) NOT NULL,
  `slug` varchar(300) NOT NULL,
  `short_desc` varchar(500) DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `original_price` decimal(10,2) DEFAULT NULL,
  `currency` varchar(10) DEFAULT 'USD',
  `image` varchar(500) DEFAULT NULL,
  `gallery` text DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `affiliate_url` varchar(500) DEFAULT NULL,
  `badge` varchar(50) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `sort_order` int(11) DEFAULT 0,
  `meta_title` varchar(300) DEFAULT NULL,
  `meta_description` varchar(500) DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_category` (`category_id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `pages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(300) NOT NULL,
  `slug` varchar(300) NOT NULL,
  `content` longtext DEFAULT NULL,
  `status` enum('draft','published') DEFAULT 'published',
  `in_footer` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `meta_title` varchar(300) DEFAULT NULL,
  `meta_description` varchar(500) DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` longtext DEFAULT NULL,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default settings
INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
('site_name', 'GlucoHarbor'),
('site_tagline', 'Professional Guide to Blood Sugar, Blood Pressure & Diabetes'),
('site_description', 'GlucoHarbor provides evidence-based information on hyperglycemia, hypertension, and diabetes management.'),
('site_logo', ''),
('site_favicon', ''),
('contact_email', 'contact@glucoharbor.com'),
('footer_text', '© 2024 GlucoHarbor. All rights reserved.'),
('icp_number', ''),
('adsense_publisher_id', ''),
('adsense_enabled', '0'),
('afs_publisher_id', ''),
('afs_channel', ''),
('afs_enabled', '0'),
('ad_header_code', ''),
('ad_article_top_code', ''),
('ad_article_bottom_code', ''),
('ad_sidebar_code', ''),
('api_token', ''),
('ga_tracking_id', ''),
('social_facebook', ''),
('social_twitter', ''),
('social_instagram', ''),
('articles_per_page', '12'),
('products_per_page', '12'),
('medical_disclaimer', 'The information on this website is for educational purposes only and is not intended as medical advice. Always consult with a qualified healthcare professional before making any health decisions.');

-- Default categories
INSERT INTO `categories` (`name`, `slug`, `description`, `sort_order`) VALUES
('Hyperglycemia', 'hyperglycemia', 'Understanding and managing high blood sugar levels', 1),
('Diabetes Management', 'diabetes-management', 'Comprehensive guide to managing diabetes', 2),
('Hypertension', 'hypertension', 'High blood pressure causes, symptoms and treatment', 3),
('Diet & Nutrition', 'diet-nutrition', 'Healthy eating for blood sugar and pressure control', 4),
('Exercise & Lifestyle', 'exercise-lifestyle', 'Physical activity and lifestyle changes for better health', 5),
('Medications', 'medications', 'Medications for diabetes and hypertension', 6);

-- Default pages
INSERT INTO `pages` (`title`, `slug`, `content`, `status`, `in_footer`, `sort_order`) VALUES
('About Us', 'about', '<h2>About GlucoHarbor</h2><p>GlucoHarbor is dedicated to providing accurate, evidence-based health information about hyperglycemia, hypertension, and diabetes.</p>', 'published', 1, 1),
('Privacy Policy', 'privacy-policy', '<h2>Privacy Policy</h2><p>Your privacy is important to us. This policy outlines how we collect and use your information.</p>', 'published', 1, 2),
('Terms of Service', 'terms-of-service', '<h2>Terms of Service</h2><p>By using GlucoHarbor, you agree to these terms.</p>', 'published', 1, 3),
('Medical Disclaimer', 'medical-disclaimer', '<h2>Medical Disclaimer</h2><p>The information provided on GlucoHarbor is for educational purposes only and does not constitute medical advice.</p>', 'published', 1, 4),
('Contact Us', 'contact', '<h2>Contact Us</h2><p>Have questions? Contact us at contact@glucoharbor.com</p>', 'published', 1, 5);

SET FOREIGN_KEY_CHECKS = 1;
