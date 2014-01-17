DROP TABLE IF EXISTS pickles;

CREATE TABLE `pickles` (
  `id` int(1) unsigned NOT NULL AUTO_INCREMENT,
  `field1` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `field2` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `field3` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `field4` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `field5` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `created_id` int(1) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_id` int(1) unsigned DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_id` int(1) unsigned DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `is_deleted` tinyint(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY is_deleted (is_deleted)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS brines;

CREATE TABLE `brines` (
  `id` int(1) unsigned NOT NULL AUTO_INCREMENT,
  `pickle_id` int(1) unsigned DEFAULT NULL,
  `created_id` int(1) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_id` int(1) unsigned DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_id` int(1) unsigned DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `is_deleted` tinyint(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY pickle_id (pickle_id),
  KEY is_deleted (is_deleted)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS users;

CREATE TABLE `users` (
  `id` int(1) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `role` varchar(10) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'USER',
  `created_id` int(1) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_id` int(1) unsigned DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_id` int(1) unsigned DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `is_deleted` tinyint(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
