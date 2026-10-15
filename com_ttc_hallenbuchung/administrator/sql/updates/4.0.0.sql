CREATE TABLE IF NOT EXISTS `#__ttc_coronaorga_` (
`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,

`ordering` INT(11)  NOT NULL ,
`state` TINYINT(1)  NOT NULL DEFAULT 1,
`created_by` INT(11)  NOT NULL ,
`modified_by` INT(11)  NOT NULL ,
`datum` DATETIME NOT NULL ,
`slot` VARCHAR(255)  NOT NULL ,
`paarung` VARCHAR(100)  NOT NULL ,
PRIMARY KEY (`id`)
) DEFAULT COLLATE=utf8mb4_unicode_ci;
