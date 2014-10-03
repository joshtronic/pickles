DROP TABLE IF EXISTS pickles;

CREATE TABLE `pickles` (
  `id` int(1) unsigned NOT NULL AUTO_INCREMENT,
  `field1` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `field2` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `field3` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `field4` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `field5` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_id` int(1) unsigned DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_id` int(1) unsigned DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_id` int(1) unsigned DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `is_deleted` tinyint(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY is_deleted (is_deleted)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS mypickles;

CREATE TABLE `mypickles` (
  `id` int(1) unsigned NOT NULL AUTO_INCREMENT,
  `field1` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `field2` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `field3` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `field4` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `field5` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_id` int(1) unsigned DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_id` int(1) unsigned DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_id` int(1) unsigned DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `is_deleted` tinyint(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY is_deleted (is_deleted)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS users;

CREATE TABLE `users` (
  `id` int(1) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `level` varchar(10) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'USER',
  `created_id` int(1) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_id` int(1) unsigned DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_id` int(1) unsigned DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `is_deleted` tinyint(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO users (id, username, created_at) VALUES (1000000, 'test', NOW());
