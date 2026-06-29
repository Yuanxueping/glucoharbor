CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` enum('admin','editor') DEFAULT 'editor',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text,
  `parent_id` int DEFAULT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `sort_order` int DEFAULT 0,
  `meta_title` varchar(200) DEFAULT NULL,
  `meta_description` varchar(500) DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `articles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(300) NOT NULL,
  `slug` varchar(300) NOT NULL,
  `excerpt` text,
  `content` longtext,
  `featured_image` varchar(500) DEFAULT NULL,
  `category_id` int DEFAULT NULL,
  `author_id` int DEFAULT NULL,
  `status` enum('draft','published','archived') DEFAULT 'draft',
  `views` int DEFAULT 0,
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
  KEY `idx_published` (`published_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `product_categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `products` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(300) NOT NULL,
  `slug` varchar(300) NOT NULL,
  `short_description` varchar(500) DEFAULT NULL,
  `description` longtext,
  `price` decimal(10,2) DEFAULT NULL,
  `sale_price` decimal(10,2) DEFAULT NULL,
  `currency` varchar(10) DEFAULT 'USD',
  `image` varchar(500) DEFAULT NULL,
  `gallery` text DEFAULT NULL,
  `category_id` int DEFAULT NULL,
  `affiliate_url` varchar(500) DEFAULT NULL,
  `badge` varchar(50) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `is_featured` tinyint(1) DEFAULT 0,
  `sort_order` int DEFAULT 0,
  `meta_title` varchar(300) DEFAULT NULL,
  `meta_description` varchar(500) DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `pages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(300) NOT NULL,
  `slug` varchar(300) NOT NULL,
  `content` longtext,
  `status` enum('draft','published') DEFAULT 'published',
  `in_footer` tinyint(1) DEFAULT 1,
  `sort_order` int DEFAULT 0,
  `meta_title` varchar(300) DEFAULT NULL,
  `meta_description` varchar(500) DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` longtext,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO `settings` (`setting_key`,`setting_value`) VALUES
('site_name','GlucoHarbor'),
('site_tagline','Professional Guide to Blood Sugar, Blood Pressure & Diabetes'),
('site_description','Evidence-based information on hyperglycemia, hypertension, and diabetes management.'),
('site_url','https://glucoharbor.com'),
('site_logo',''),('site_favicon',''),
('contact_email','contact@glucoharbor.com'),
('footer_text','© 2024 GlucoHarbor. All rights reserved.'),
('icp_number',''),
('adsense_enabled','0'),('adsense_publisher_id',''),
('afs_enabled','0'),('afs_publisher_id',''),('afs_channel',''),
('ad_header_code',''),('ad_article_top_code',''),
('ad_article_bottom_code',''),('ad_sidebar_code',''),
('api_token',''),('ga_tracking_id',''),
('social_facebook',''),('social_twitter',''),('social_instagram',''),
('articles_per_page','12'),('products_per_page','12'),
('medical_disclaimer','The information on this website is for educational purposes only. Always consult a qualified healthcare professional.');

INSERT IGNORE INTO `categories` (`name`,`slug`,`description`,`sort_order`) VALUES
('Hyperglycemia','hyperglycemia','Understanding high blood sugar levels',1),
('Diabetes Management','diabetes-management','Comprehensive diabetes management guide',2),
('Hypertension','hypertension','High blood pressure causes and treatment',3),
('Diet & Nutrition','diet-nutrition','Healthy eating for blood sugar control',4),
('Exercise & Lifestyle','exercise-lifestyle','Physical activity for better health',5),
('Medications','medications','Medications for diabetes and hypertension',6);

INSERT IGNORE INTO `pages` (`title`,`slug`,`content`,`status`,`in_footer`,`sort_order`) VALUES
('About Us','about','<h2>About GlucoHarbor</h2><p>GlucoHarbor is dedicated to providing accurate, evidence-based health information about hyperglycemia, hypertension, and diabetes.</p>','published',1,1),
('Privacy Policy','privacy-policy','<h2>Privacy Policy</h2><p>Your privacy is important to us. This policy outlines how we collect and use your information.</p>','published',1,2),
('Terms of Service','terms-of-service','<h2>Terms of Service</h2><p>By using GlucoHarbor, you agree to these terms and conditions.</p>','published',1,3),
('Medical Disclaimer','medical-disclaimer','<h2>Medical Disclaimer</h2><p>The information provided on GlucoHarbor is for educational purposes only and does not constitute medical advice.</p>','published',1,4),
('Contact Us','contact','<h2>Contact Us</h2><p>Have questions? Contact us at contact@glucoharbor.com</p>','published',1,5),
('Affiliate Disclosure','affiliate-disclosure','<h2>Affiliate Disclosure</h2><p>GlucoHarbor participates in affiliate marketing programs. This means we may earn a commission when you click on certain links and make a purchase, at no additional cost to you.</p><h3>How It Works</h3><p>Some of the links on this website are affiliate links. If you click on one of these links and make a purchase, we may receive a small commission from the retailer. This helps us keep the site running and continue providing free health information.</p><h3>Our Promise</h3><p>We only recommend products we believe may be beneficial for people managing blood sugar, blood pressure, or diabetes. Our editorial content is never influenced by affiliate relationships. All product reviews and recommendations are based on merit.</p><h3>Third-Party Links</h3><p>GlucoHarbor links to third-party websites. We are not responsible for the privacy practices or content of those sites. We encourage you to read the privacy policy of every website you visit.</p><p>If you have any questions about our affiliate relationships, please <a href="/page/contact">contact us</a>.</p>','published',1,6);
