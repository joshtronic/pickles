--
-- Table structure for table `posts`
--

CREATE TABLE IF NOT EXISTS `pickles_posts` (
	`id` int(1) unsigned NOT NULL AUTO_INCREMENT,
	`title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
	`body` text COLLATE utf8_unicode_ci NOT NULL,
	`posted_at` datetime NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Sample Blog Posts Table for PICKLES' AUTO_INCREMENT=1 ;

