CREATE TABLE IF NOT EXISTS `#__ttc_relevante_kategorien` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `category_id` INT(11) NOT NULL,
    `sort_order` INT(11) NOT NULL,
    `state` TINYINT(3) NOT NULL DEFAULT 1,
    `created` DATETIME NOT NULL,
    `created_by` INT(11) NOT NULL DEFAULT 0,
    `modified` DATETIME NULL,
    `modified_by` INT(11) NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_category_id` (`category_id`)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__ttc_mmb` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `user_id` INT(11) NOT NULL,
    `category_id` INT(11) NULL,
    `position` INT(11) NULL,
    `is_captain` TINYINT(3) NOT NULL DEFAULT 0,
    `state` TINYINT(3) NOT NULL DEFAULT 1,
    `created` DATETIME NOT NULL,
    `created_by` INT(11) NOT NULL DEFAULT 0,
    `modified` DATETIME NULL,
    `modified_by` INT(11) NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_user_id` (`user_id`),
    KEY `idx_category_id` (`category_id`),
    KEY `idx_position` (`position`)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__ttc_spielplanung` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `user_id` INT(11) NOT NULL,
    `game_id` INT(11) NOT NULL,
    `status` TINYINT(3) NULL DEFAULT NULL,
    `state` TINYINT(3) NOT NULL DEFAULT 1,
    `created` DATETIME NOT NULL,
    `created_by` INT(11) NOT NULL DEFAULT 0,
    `modified` DATETIME NULL,
    `modified_by` INT(11) NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_user_game` (`user_id`, `game_id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_game_id` (`game_id`)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
