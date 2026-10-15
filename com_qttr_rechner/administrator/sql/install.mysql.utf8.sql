CREATE TABLE IF NOT EXISTS `#__qttr_rechner` (
`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,

`state` TINYINT(1)  NULL  DEFAULT 1,
`ordering` INT(11)  NULL  DEFAULT 0,
`checked_out` INT(11)  UNSIGNED,
`checked_out_time` DATETIME NULL  DEFAULT NULL ,
`created_by` INT(11)  NULL  DEFAULT 0,
`modified_by` INT(11)  NULL  DEFAULT 0,
`qttr_wert` INT(5)  NOT NULL ,
`sieg` VARCHAR(255)  NOT NULL  DEFAULT "ja",
`letztesspiel` VARCHAR(255)  NOT NULL  DEFAULT "ja",
PRIMARY KEY (`id`)
) DEFAULT COLLATE=utf8mb4_unicode_ci;

