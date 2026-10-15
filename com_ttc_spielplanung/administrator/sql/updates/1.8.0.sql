CREATE TABLE IF NOT EXISTS `#__ttc_spielplan` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `hallennr` VARCHAR(1) NULL DEFAULT '0',
    `ort_key` SMALLINT NULL DEFAULT 0,
    `mannschaft` TINYINT NOT NULL,
    `datum` DATE NOT NULL,
    `uhrzeit` TIME NOT NULL DEFAULT '00:00:00',
    `heimmannschaft` VARCHAR(100) NOT NULL,
    `h_nummer` VARCHAR(4) NOT NULL,
    `auswaertsmannschaft` VARCHAR(100) NOT NULL,
    `a_nummer` VARCHAR(4) NOT NULL,
    `ort` VARCHAR(200) NULL DEFAULT '',
    PRIMARY KEY (`id`),
    KEY `idx_mannschaft` (`mannschaft`),
    KEY `idx_datum` (`datum`)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
