CREATE TABLE `bingsearch_result` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `domain` char(128) DEFAULT '',
  `kw1` char(128) DEFAULT '',
  `title1` char(128) DEFAULT '',
  `uri1` char(128) DEFAULT '',
  `kw2` char(128) DEFAULT '',
  `title2` char(128) DEFAULT '',
  `uri2` char(128) DEFAULT '',
  `kw3` char(128) DEFAULT '',
  `title3` char(128) DEFAULT '',
  `uri3` char(128) DEFAULT '',
  `datatime` char(16) DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `domain` (`domain`,`datatime`)
) ENGINE=MyISAM AUTO_INCREMENT=702207 DEFAULT CHARSET=latin1;

