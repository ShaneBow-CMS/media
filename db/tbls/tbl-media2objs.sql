(
 `otype` smallint(1) unsigned NOT NULL DEFAULT 1 COMMENT 'obj type',
 `oid` int(10) unsigned NOT NULL COMMENT 'obj id',
 `seq` tinyint(1) unsigned NOT NULL DEFAULT 1 COMMENT 'obj sequence',
 `mid` int(10) unsigned NOT NULL,
  PRIMARY KEY (`otype`,`oid`,`seq`,`mid`),
  KEY `mid` (`mid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
