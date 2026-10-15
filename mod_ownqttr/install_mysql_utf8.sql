CREATE TABLE IF NOT EXISTS `#__qttr_eigener` (
  `user_id` int(11) unsigned NOT NULL,
  `qttr_wert` int(11) NOT NULL DEFAULT '0',
  `mein_alter` tinyint(1) NOT NULL,
  `beginner` char(1) NOT NULL,
  `pause` char(1) NOT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

