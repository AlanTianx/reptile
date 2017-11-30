CREATE TABLE `keywords_result` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `keyword` char(128) DEFAULT '',
  `kw1` char(128) DEFAULT '',
  `vol1` char(128) DEFAULT '',
  `kw2` char(128) DEFAULT '',
  `vol2` char(128) DEFAULT '',
  `kw3` char(128) DEFAULT '',
  `vol3` char(128) DEFAULT '',
  `location` char(8) DEFAULT '',
  `datatime` char(8) DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `keyword` (`datatime`,`keyword`,`location`)
) ENGINE=MyISAM AUTO_INCREMENT=44993 DEFAULT CHARSET=latin1;

