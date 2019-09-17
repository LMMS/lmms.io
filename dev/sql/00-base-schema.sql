-- Users
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `login` varchar(20) NOT NULL DEFAULT '',
  `password` varchar(40) NOT NULL DEFAULT '',
  `pw` varchar(255) NOT NULL DEFAULT '',
  `realname` varchar(255) DEFAULT NULL,
  `is_admin` tinyint(1) DEFAULT NULL,
  `loginFailureCount` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=7308 DEFAULT CHARSET=latin1;
-- Files
CREATE TABLE `files` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `filename` varchar(255) NOT NULL DEFAULT '',
  `license_id` int(10) unsigned NOT NULL DEFAULT '0',
  `category` int(10) unsigned NOT NULL DEFAULT '0',
  `subcategory` int(11) NOT NULL DEFAULT '0',
  `description` text,
  `insert_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `update_date` timestamp NULL DEFAULT NULL,
  `rate` int(10) unsigned DEFAULT NULL,
  `size` int(11) NOT NULL DEFAULT '0',
  `hash` varchar(40) NOT NULL,
  `downloads` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `Files_FKIndex1` (`category`),
  KEY `Files_FKIndex3` (`license_id`),
  KEY `subcategory` (`subcategory`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=15402 DEFAULT CHARSET=latin1;
-- Filetypes
CREATE TABLE `filetypes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `extension` varchar(20) NOT NULL DEFAULT '',
  `category` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `category` (`category`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=latin1;
CREATE TABLE `comments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `file_id` int(11) NOT NULL DEFAULT '0',
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `text` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`,`file_id`)
) ENGINE=MyISAM AUTO_INCREMENT=22918 DEFAULT CHARSET=latin1;
CREATE TABLE `categories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;
CREATE TABLE `ratings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `file_id` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  `stars` smallint(6) NOT NULL DEFAULT '3',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `file_id` (`file_id`)
) ENGINE=MyISAM AUTO_INCREMENT=31324 DEFAULT CHARSET=latin1;
CREATE TABLE `subcategories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `category` int(11) NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `category` (`category`)
) ENGINE=MyISAM AUTO_INCREMENT=63 DEFAULT CHARSET=latin1;
CREATE TABLE `licenses` (
  `id` int(10) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=latin1;
