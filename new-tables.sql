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

CREATE TABLE `global_payment_history` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `donor_id` bigint(20) DEFAULT NULL,
  `donation_id` bigint(20) DEFAULT NULL,
  `auth_amount` decimal(10,2) DEFAULT NULL,
  `avail_balance` decimal(10,2) DEFAULT NULL,
  `avs_code` varchar(10) DEFAULT NULL,
  `balance_amt` decimal(10,2) DEFAULT NULL,
  `batch_ref` varchar(50) DEFAULT NULL,
  `card_type` varchar(10) DEFAULT NULL,
  `card_last4` varchar(4) DEFAULT NULL,
  `trans_type` varchar(10) DEFAULT NULL,
  `ref_num` varchar(50) DEFAULT NULL,
  `resp_code` varchar(10) DEFAULT NULL,
  `resp_msg` varchar(255) DEFAULT NULL,
  `date_created` datetime DEFAULT NULL,
  `trans_auth_code` varchar(20) DEFAULT NULL,
  `trans_id` varchar(50) DEFAULT NULL,
  `fraud_mode` varchar(10) DEFAULT NULL,
  `fraud_result` varchar(10) DEFAULT NULL,
  `fraud_rule_1_key` varchar(50) DEFAULT NULL,
  `fraud_rule_1_desc` varchar(100) DEFAULT NULL,
  `fraud_rule_1_result` varchar(10) DEFAULT NULL,
  `card_result` varchar(10) DEFAULT NULL,
  `card_cvv_result` varchar(10) DEFAULT NULL,
  `card_last4_detail` varchar(4) DEFAULT NULL,
  `card_brand` varchar(10) DEFAULT NULL,
  `card_avs_code` varchar(10) DEFAULT NULL,
  `meta_info` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=78 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
ALTER TABLE `global_payment_history` 
CHANGE COLUMN `card_last4` `card_last4` VARCHAR(20) NULL DEFAULT NULL ;
ALTER TABLE `global_payment_history` 
CHANGE COLUMN `card_last4_detail` `card_last4_detail` VARCHAR(20) NULL DEFAULT NULL ;

CREATE TABLE email_subscriptions (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE, 
    first_name VARCHAR(255) NULL, 
    last_name VARCHAR(255) NULL, 
    subscribed_at DATETIME DEFAULT CURRENT_TIMESTAMP, 
    unsubscribed_at DATETIME NULL,
    status TINYINT(1) DEFAULT 0, 
    meta_info VARCHAR(500) NULL,
    unsub_meta_info VARCHAR(500) NULL,
    verification_token VARCHAR(255) NULL, 
    verified_at DATETIME NULL
);

CREATE TABLE `email_queue` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `to_email` varchar(2500) NOT NULL,
  `reply_to_email` varchar(256) DEFAULT NULL,
  `sender_name` varchar(100) NOT NULL DEFAULT 'Humanity First Canada',
  `subject` varchar(150) NOT NULL,
  `message` blob NOT NULL,
  `attempts` tinyint(1) NOT NULL DEFAULT 0,
  `success` tinyint(1) NOT NULL DEFAULT 0,
  `date_published` datetime NOT NULL,
  `hash` varchar(60) NOT NULL,
  `date_added` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_attempt` datetime DEFAULT NULL,
  `date_sent` datetime DEFAULT NULL,
  `attachment` varchar(256) NOT NULL,
  `sent_by` int(11) NOT NULL DEFAULT 0,
  `record_id` bigint(11) NOT NULL DEFAULT 0,
  `error_message` varchar(500) DEFAULT NULL,
  `queue_type` varchar(10) NOT NULL DEFAULT 'smtp',
  PRIMARY KEY (`id`),
  KEY `eml_suc_indx` (`attempts`,`success`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;




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
ALTER TABLE `donors` 
ADD COLUMN `activation_token` VARCHAR(45) NULL AFTER `last_meta_info`;


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

ALTER TABLE `donors` 
CHANGE COLUMN `city_id` `city_id` INT(11) NOT NULL DEFAULT 0 ,
CHANGE COLUMN `state_id` `state_id` INT(11) NOT NULL DEFAULT 0 ,
CHANGE COLUMN `country_id` `country_id` INT(11) NOT NULL DEFAULT 0 ;

ALTER TABLE `donations` 
CHANGE COLUMN `modified_date` `modified_date` DATETIME NULL ,
CHANGE COLUMN `modified_by` `modified_by` INT(11) NULL ;

ALTER TABLE `donations` 
CHANGE COLUMN `cheque_trans_no` `cheque_trans_no` VARCHAR(64) NULL DEFAULT NULL ;



-- new db
CREATE TABLE `ci_sessions` (
    `id` varchar(128) NOT NULL,
    `ip_address` varchar(45) NOT NULL,
    `timestamp` int(10) unsigned DEFAULT 0 NOT NULL,
    `data` blob NOT NULL,
    PRIMARY KEY (`id`),
    KEY `ci_sessions_timestamp` (`timestamp`)
);



