CREATE TABLE IF NOT EXISTS `#__ttc_ferienverwaltung` (
`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,

`state` TINYINT(1)  NULL  DEFAULT 1,
`ordering` INT(11)  NULL  DEFAULT 0,
`checked_out` INT(11)  UNSIGNED,
`checked_out_time` DATETIME NULL  DEFAULT NULL ,
`created_by` INT(11)  NULL  DEFAULT 0,
`modified_by` INT(11)  NULL  DEFAULT 0,
`ferienbeginn` DATE NOT NULL ,
`ferienende` DATE NOT NULL ,
PRIMARY KEY (`id`)
,KEY `idx_state` (`state`)
,KEY `idx_checked_out` (`checked_out`)
,KEY `idx_created_by` (`created_by`)
,KEY `idx_modified_by` (`modified_by`)
) DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE INDEX `#__ttc_ferienverwaltung_ferienbeginn` ON `#__ttc_ferienverwaltung`(`ferienbeginn`);

CREATE INDEX `#__ttc_ferienverwaltung_ferienende` ON `#__ttc_ferienverwaltung`(`ferienende`);

