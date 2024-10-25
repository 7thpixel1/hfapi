<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

ERROR - 2024-03-07 23:35:35 --> 404 Page Not Found: /index
ERROR - 2024-03-07 23:36:00 --> Query error: Table 'erp_hfapi.sessions' doesn't exist - Invalid query: SELECT `data`
FROM `sessions`
WHERE `id` = 'nfm8ou016h9dmfilfdursvpflj9s16so'
ERROR - 2024-03-07 23:37:14 --> Query error: Table 'erp_hfapi.sessions' doesn't exist - Invalid query: SELECT `data`
FROM `sessions`
WHERE `id` = 'rommkt0rr2gvhmn61i3et0hepau4c7gp'
ERROR - 2024-03-07 23:37:14 --> Language file contains no data: language/en/db_lang.php
ERROR - 2024-03-07 23:37:14 --> Could not find the language line "db_error_heading"
ERROR - 2024-03-07 17:55:28 --> 404 Page Not Found: Authenticate/index
ERROR - 2024-03-07 18:08:09 --> Severity: error --> Exception: Call to undefined method Api::getCompany() /Users/saqibahmad/www/hfapi/application/controllers/Api.php 21
ERROR - 2024-03-07 18:09:00 --> Severity: error --> Exception: Call to undefined method Pixel::clean() /Users/saqibahmad/www/hfapi/application/controllers/Api.php 28
ERROR - 2024-03-07 18:26:24 --> Query error: Table 'erp_hfapi.country' doesn't exist - Invalid query: SELECT `id`, `title`, `first_name`, `last_name`, `middle_name`, `other_name`, `business_name`, `date_of_birth`, `address1`, `address2`, `city_id`, `state_id`, `country_id`, `postal_code`, `email`, `home_phone`, `work_phone`, `extension`, `fax`, `cell`, `email_2`, `branch_id`, `refrence_id` as `member_code`, `password_hash`, `parent_id`, (select name from cities where id=donors.city_id) as city_name, (select name from provinces where id=donors.state_id) as state_name, (select name from country where id=donors.country_id) as country_name, (select name from select_types where type='gender' and value=donors.gender) as gender
FROM `donors`
WHERE `email` = 'saqibahmaad@gmail.com'
AND `status` = 1
AND `can_login` = 1
ERROR - 2024-03-07 18:26:24 --> Language file contains no data: language/en/db_lang.php
ERROR - 2024-03-07 18:26:24 --> Could not find the language line "db_error_heading"
ERROR - 2024-03-07 18:27:38 --> Query error: Unknown column 'value' in 'where clause' - Invalid query: SELECT `id`, `title`, `first_name`, `last_name`, `middle_name`, `other_name`, `business_name`, `date_of_birth`, `address1`, `address2`, `city_id`, `state_id`, `country_id`, `postal_code`, `email`, `home_phone`, `work_phone`, `extension`, `fax`, `cell`, `email_2`, `branch_id`, `refrence_id` as `member_code`, `password_hash`, `parent_id`, (select name from cities where id=donors.city_id) as city_name, (select name from provinces where id=donors.state_id) as state_name, (select name from countries where id=donors.country_id) as country_name, (select name from branches where id=donors.branch_id) as branch_name, (select name from select_types where type='gender' and value=donors.gender) as gender
FROM `donors`
WHERE `email` = 'saqibahmaad@gmail.com'
AND `status` = 1
AND `can_login` = 1
ERROR - 2024-03-07 18:27:38 --> Language file contains no data: language/en/db_lang.php
ERROR - 2024-03-07 18:27:38 --> Could not find the language line "db_error_heading"
ERROR - 2024-03-07 18:49:59 --> Severity: error --> Exception: Object of class Api could not be converted to string /Users/saqibahmad/www/hfapi/application/controllers/Api.php 72
ERROR - 2024-03-07 18:50:30 --> Severity: Warning --> Undefined property: Api::$database /Users/saqibahmad/www/hfapi/system/core/Model.php 74
ERROR - 2024-03-07 18:50:30 --> Severity: error --> Exception: Call to a member function select() on null /Users/saqibahmad/www/hfapi/application/models/ApiModel.php 20
