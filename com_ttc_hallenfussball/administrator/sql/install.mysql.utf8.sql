CREATE TABLE IF NOT EXISTS `#__ttc_hallenfussball` (
`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,

`created_by` INT(11)  NULL  DEFAULT 0,
`created_when` DATETIME NULL  DEFAULT NULL ,
`datum` DATE NOT NULL  DEFAULT NULL,
`teilnehmer` VARCHAR(255)  NOT NULL ,
`zusage` VARCHAR(255)  NOT NULL ,
PRIMARY KEY (`id`)
,KEY `idx_created_by` (`created_by`)
) DEFAULT COLLATE=utf8_general_ci;


INSERT INTO `#__content_types` (`type_title`, `type_alias`, `table`, `rules`, `field_mappings`, `content_history_options`)
SELECT * FROM ( SELECT 'Hallenfussballteilnahme','com_ttc_hallenfussball.hallenfussballteilnahme','{"special":{"dbtable":"#__ttc_hallenfussball","key":"id","type":"HallenfussballteilnahmeTable","prefix":"Joomla\\\\Component\\\\Ttc_hallenfussball\\\\Administrator\\\\Table\\\\"}}', CASE 
                                    WHEN 'rules' is null THEN ''
                                    ELSE ''
                                    END as rules, CASE 
                                    WHEN 'field_mappings' is null THEN ''
                                    ELSE ''
                                    END as field_mappings, '{"formFile":"administrator\/components\/com_ttc_hallenfussball\/forms\/hallenfussballteilnahme.xml", "hideFields":["checked_out","checked_out_time","params","language"], "ignoreChanges":["modified_by", "modified", "checked_out", "checked_out_time"], "convertToInt":["publish_up", "publish_down"], "displayLookup":[{"sourceColumn":"catid","targetTable":"#__categories","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"group_id","targetTable":"#__usergroups","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"created_by","targetTable":"#__users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"access","targetTable":"#__viewlevels","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"modified_by","targetTable":"#__users","targetColumn":"id","displayColumn":"name"}]}') AS tmp
WHERE NOT EXISTS (
	SELECT type_alias FROM `#__content_types` WHERE (`type_alias` = 'com_ttc_hallenfussball.hallenfussballteilnahme')
) LIMIT 1;
