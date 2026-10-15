CREATE TABLE IF NOT EXISTS `#__ttc_hallenschliesszeiten` (
`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,

`state` TINYINT(1)  NULL  DEFAULT 1,
`ordering` INT(11)  NULL  DEFAULT 0,
`checked_out` INT(11)  UNSIGNED,
`checked_out_time` DATETIME NULL  DEFAULT NULL ,
`created_by` INT(11)  NULL  DEFAULT 0,
`modified_by` INT(11)  NULL  DEFAULT 0,
`beginn` DATE NOT NULL ,
`ende` DATE NOT NULL ,
`schliessinfo` VARCHAR(75)  NULL  DEFAULT "",
PRIMARY KEY (`id`)
,KEY `idx_state` (`state`)
,KEY `idx_checked_out` (`checked_out`)
,KEY `idx_created_by` (`created_by`)
,KEY `idx_modified_by` (`modified_by`)
) DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE INDEX `#__ttc_hallenschliesszeiten_beginn` ON `#__ttc_hallenschliesszeiten`(`beginn`);

CREATE INDEX `#__ttc_hallenschliesszeiten_ende` ON `#__ttc_hallenschliesszeiten`(`ende`);

