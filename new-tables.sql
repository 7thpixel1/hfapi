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
ADD COLUMN `country` VARCHAR(3) NOT NULL DEFAULT '' AFTER `state`;


ALTER TABLE `hf_dms`.`donations` 
ADD COLUMN `is_recurring` TINYINT NOT NULL DEFAULT 0 AFTER `country`,
ADD COLUMN `dedication_type` VARCHAR(10) NULL AFTER `is_recurring`,
ADD COLUMN `honoree_first_name` VARCHAR(255) NULL AFTER `dedication_type`,
ADD COLUMN `honoree_last_name` VARCHAR(255) NULL AFTER `honoree_first_name`;

ALTER TABLE `hf_dms`.`donors` 
ADD COLUMN `opt_in` TINYINT NOT NULL DEFAULT 0 AFTER `meta_info`;

ALTER TABLE `hf_dms`.`donors` 
ADD COLUMN `city` VARCHAR(255) NULL AFTER `opt_in`,
ADD COLUMN `state` VARCHAR(255) NULL AFTER `city`,
ADD COLUMN `country` VARCHAR(3) NULL AFTER `state`;


ALTER TABLE `hf_dms`.`branches` 
ADD COLUMN `city` VARCHAR(255) NULL AFTER `cordinator_id`;

ALTER TABLE `hf_dms`.`recurring_donations` 
DROP COLUMN `card_brand_reference`,
DROP COLUMN `card_holder_name`,
ADD COLUMN `token_id` BIGINT NOT NULL DEFAULT 0 AFTER `donor_id`;
ALTER TABLE `hf_dms`.`donations` 
ADD COLUMN `online_batch_id` VARCHAR(20) NULL AFTER `honoree_last_name`;



update donations d 
join cities c on d.city_id=c.id 
join provinces p on d.state_id=p.id
join countries ct on d.country_id=ct.id
set d.city=c.name,
d.state=p.name,
d.country=ct.iso_code;


update donors d 
join cities c on d.city_id=c.id 
join provinces p on d.state_id=p.id
join countries ct on d.country_id=ct.id
set d.city=c.name,
d.state=p.name,
d.country=ct.iso_code;

update branches d 
join cities c on d.city_id=c.id 
set d.city=c.name;


ALTER TABLE `donors` 
ADD COLUMN `username` VARCHAR(255) NOT NULL AFTER `country`,
ADD COLUMN `provider` VARCHAR(50) NULL AFTER `username`,
ADD COLUMN `provider_id` VARCHAR(100) NULL AFTER `provider`,
ADD COLUMN `access_token` TEXT NULL AFTER `provider_id`,
ADD COLUMN `refresh_token` TEXT NULL AFTER `access_token`;


-- copying unique emails in there
-- Step 1: Update `username` with `email` if it exists and is unique
UPDATE donors
SET username = email
WHERE email IS NOT NULL
  AND email NOT IN (
      SELECT DISTINCT email_2 FROM donors WHERE email_2 IS NOT NULL
  );

-- Step 2: Update `username` with `email_2` if `email` is NULL or already used
UPDATE donors
SET username = email_2
WHERE username =''
  AND email_2 IS NOT NULL;

-- Step 3: Ensure `username` is unique by appending the `id` for duplicates
UPDATE donors
SET username = CONCAT(SUBSTRING_INDEX(username, '@', 1), '_', id, '@', SUBSTRING_INDEX(username, '@', -1))
WHERE username IN (
    SELECT username
    FROM (
        SELECT username, COUNT(*) AS c
        FROM donors
        GROUP BY username
        HAVING c > 1
    ) AS duplicates
);
ALTER TABLE donors ADD UNIQUE INDEX username_unique (username);
ALTER TABLE `donors` 
ADD COLUMN `last_meta_info` VARCHAR(500) NULL AFTER `refresh_token`;

CREATE TABLE `password_resets` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `expires_at` datetime NOT NULL DEFAULT current_timestamp(),
  `meta_info` varchar(500) DEFAULT NULL,
  `ip` varchar(20) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 0,
  `reset_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



-- new db
CREATE TABLE `ci_sessions` (
    `id` varchar(128) NOT NULL,
    `ip_address` varchar(45) NOT NULL,
    `timestamp` int(10) unsigned DEFAULT 0 NOT NULL,
    `data` blob NOT NULL,
    PRIMARY KEY (`id`),
    KEY `ci_sessions_timestamp` (`timestamp`)
);



