CREATE TABLE IF NOT EXISTS `#__ttc_spielplan` (
`mannschaft` TINYINT(1)  NOT NULL ,
`datum` DATE NOT NULL ,
`uhrzeit` TIME NOT NULL  DEFAULT "00:00:00",
`heimmannschaft` VARCHAR(100)  NOT NULL ,
`h_nummer` VARCHAR(4)  NOT NULL ,
`auswaertsmannschaft` VARCHAR(100)  NOT NULL ,
`a_nummer` VARCHAR(4)  NOT NULL ,
`ort` VARCHAR(200)  NULL  DEFAULT "",
`ort_key` SMALLINT NULL  DEFAULT 0,
`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
`hallennr` VARCHAR(1)  NULL  DEFAULT "0",

PRIMARY KEY (`id`)
) DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE INDEX `#__ttc_spielplan_mannschaft` ON `#__ttc_spielplan`(`mannschaft`);

CREATE INDEX `#__ttc_spielplan_datum` ON `#__ttc_spielplan`(`datum`);

