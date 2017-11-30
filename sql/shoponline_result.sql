CREATE TABLE `shoponline_result` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `domain` char(128) NOT NULL DEFAULT '',
  `status` char(4) DEFAULT '',
  `words` char(128) DEFAULT '',
  `datatime` char(16) DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `domain` (`datatime`,`domain`)
) ENGINE=MyISAM AUTO_INCREMENT=39192 DEFAULT CHARSET=latin1;

