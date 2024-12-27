(
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type_id` tinyint(2) NOT NULL,
  `title` varchar(255) NOT NULL,
  `caption` text NULL,
  `attribution` varchar(255) NULL,
  `owner_id` int(10) unsigned NOT NULL,
  `debut` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `owner_id` (`owner_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
