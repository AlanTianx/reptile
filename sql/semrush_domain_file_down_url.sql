CREATE TABLE `domain_file_down_url` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `domain` char(126) DEFAULT '',
  `file_id` int(11) unsigned DEFAULT NULL,
  `url` text,
  `status` enum('NEW','PARSERED','DOWN','FAILD') DEFAULT 'NEW',
  PRIMARY KEY (`id`),
  UNIQUE KEY `domain` (`domain`,`file_id`) USING BTREE,
  KEY `file_id` (`file_id`,`status`)
) ENGINE=MyISAM AUTO_INCREMENT=8815 DEFAULT CHARSET=latin1;

