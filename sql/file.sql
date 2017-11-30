CREATE TABLE `files_up_down` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `type` enum('keywords','similarweb','shoponline','bingsearch','google_index','semrushD','semrushK') NOT NULL,
  `upfilename` char(128) DEFAULT '',
  `uptime` datetime NOT NULL,
  `location` char(8) DEFAULT '',
  `terms` char(128) DEFAULT '',
  `createdate` datetime DEFAULT NULL,
  `downfilename` char(128) DEFAULT '',
  `user` char(32) NOT NULL DEFAULT '',
  `senum` int(11) unsigned DEFAULT '0',
  `ernum` int(11) unsigned DEFAULT '0',
  `status` enum('NEW','PARSERED','COMPLETE','DOWN','FAILD') NOT NULL DEFAULT 'NEW',
  PRIMARY KEY (`id`),
  UNIQUE KEY `upfilename` (`upfilename`),
  KEY `type` (`type`,`status`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=1155 DEFAULT CHARSET=latin1;

