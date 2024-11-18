CREATE TABLE `donor_tokens` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `donor_id` bigint(20) NOT NULL DEFAULT 0,
  `token` varchar(250) NOT NULL,
  `card_holder_name` varchar(150) DEFAULT NULL,
  `token_name` varchar(45) DEFAULT NULL,
  `brand` varchar(25) DEFAULT NULL,
  `expiry_month` varchar(2) DEFAULT NULL,
  `expiry_year` varchar(2) DEFAULT NULL,
  `source_donation` bigint(20) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `modified_date` datetime DEFAULT NULL,
  `modified_by` int(11) NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `object` blob DEFAULT NULL,
  PRIMARY KEY (`id`,`modified_by`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
CREATE TABLE `api_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(512) NOT NULL,
  `expires_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
CREATE TABLE `recurring_donations` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `donor_id` bigint(20) NOT NULL DEFAULT 0,
  `card_holder_name` varchar(250) NOT NULL DEFAULT '',
  `card_brand_reference` varchar(150) NOT NULL DEFAULT '',
  `eligible_amount` double NOT NULL DEFAULT 0,
  `non_eligible_amount` double NOT NULL DEFAULT 0,
  `project_id` int(11) NOT NULL DEFAULT 0,
  `comments` varchar(255) DEFAULT NULL,
  `frequency` int(11) NOT NULL DEFAULT 30,
  `created_date` datetime DEFAULT NULL,
  `created_by` int(11) NOT NULL DEFAULT 0,
  `modified_date` datetime DEFAULT NULL,
  `modified_by` int(11) DEFAULT NULL,
  `source_donation` bigint(20) NOT NULL DEFAULT 0,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `last_run` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
ALTER TABLE `donations` 
ADD COLUMN `city` VARCHAR(255) NOT NULL DEFAULT '' AFTER `is_online`,
ADD COLUMN `state` VARCHAR(255) NOT NULL DEFAULT '' AFTER `city`,
ADD COLUMN `country` VARCHAR(255) NOT NULL DEFAULT '' AFTER `state`;

