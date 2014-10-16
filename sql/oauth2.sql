CREATE TABLE `oauth_clients` (
  `id` CHAR(40) NOT NULL,
  `secret` CHAR(40) NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `auto_approve` TINYINT(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `u_oacl_clse_clid` (`secret`,`id`)
) ENGINE=INNODB DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci;

CREATE TABLE `oauth_endpoints` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `client_id` char(40) NOT NULL,
  `redirect_uri` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `i_oaclen_clid` (`client_id`),
  CONSTRAINT `f_oaclen_clid`
      FOREIGN KEY (`client_id`)
      REFERENCES `oauth_clients` (`id`)
      ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci;

CREATE TABLE `oauth_sessions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `client_id` char(40) NOT NULL,
  `owner_type` enum('user','client') NOT NULL DEFAULT 'user',
  `owner_id` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `i_uase_clid_owty_owid` (`client_id`,`owner_type`,`owner_id`),
  CONSTRAINT `f_oase_clid`
      FOREIGN KEY (`client_id`)
      REFERENCES `oauth_clients` (`id`)
      ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci;

CREATE TABLE `oauth_access_tokens` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `session_id` int(10) unsigned NOT NULL,
  `access_token` char(40) NOT NULL,
  `expires_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `u_oaseacto_acto_seid` (`access_token`,`session_id`),
  KEY `f_oaseto_seid` (`session_id`),
  CONSTRAINT `f_oaseto_seid`
      FOREIGN KEY (`session_id`)
      REFERENCES `oauth_sessions` (`id`)
      ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci;

CREATE TABLE `oauth_authorization_codes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `session_id` int(10) unsigned NOT NULL,
  `authorization_code` char(40) NOT NULL,
  `expires_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `session_id` (`session_id`),
  CONSTRAINT `oauth_authorization_codes_ibfk_1`
      FOREIGN KEY (`session_id`)
      REFERENCES `oauth_sessions` (`id`)
      ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci;

CREATE TABLE `oauth_redirect_uris` (
  `session_id` int(10) unsigned NOT NULL,
  `redirect_uri` varchar(255) NOT NULL,
  PRIMARY KEY (`session_id`),
  CONSTRAINT `f_oasere_seid`
      FOREIGN KEY (`session_id`)
      REFERENCES `oauth_sessions` (`id`)
      ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci;

CREATE TABLE `oauth_refresh_tokens` (
  `access_token_id` int(10) unsigned NOT NULL,
  `refresh_token` char(40) NOT NULL,
  `expires_at` int(10) unsigned NOT NULL,
  `client_id` char(40) NOT NULL,
  PRIMARY KEY (`access_token_id`),
  KEY `client_id` (`client_id`),
  CONSTRAINT `oauth_refresh_tokens_ibfk_1`
      FOREIGN KEY (`client_id`)
      REFERENCES `oauth_clients` (`id`)
      ON DELETE CASCADE,
  CONSTRAINT `f_oasetore_setoid`
      FOREIGN KEY (`access_token_id`)
      REFERENCES `oauth_access_tokens` (`id`)
      ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci;

CREATE TABLE `oauth_scopes` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `scope` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `u_oasc_sc` (`scope`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci;

CREATE TABLE `oauth_access_token_scopes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `access_token_id` int(10) unsigned DEFAULT NULL,
  `scope_id` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `u_setosc_setoid_scid` (`access_token_id`,`scope_id`),
  KEY `f_oasetosc_scid` (`scope_id`),
  CONSTRAINT `f_oasetosc_scid`
      FOREIGN KEY (`scope_id`)
      REFERENCES `oauth_scopes` (`id`)
      ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `f_oasetosc_setoid`
      FOREIGN KEY (`access_token_id`)
      REFERENCES `oauth_access_tokens` (`id`)
      ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci;

CREATE TABLE `oauth_authorization_code_scopes` (
  `authorization_code_id` int(10) unsigned NOT NULL,
  `scope_id` smallint(5) unsigned NOT NULL,
  KEY `authorization_code_id` (`authorization_code_id`),
  KEY `scope_id` (`scope_id`),
  CONSTRAINT `oauth_authorization_code_scopes_ibfk_2`
      FOREIGN KEY (`scope_id`)
      REFERENCES `oauth_scopes` (`id`)
      ON DELETE CASCADE,
  CONSTRAINT `oauth_authorization_code_scopes_ibfk_1`
      FOREIGN KEY (`authorization_code_id`)
      REFERENCES `oauth_authorization_codes` (`id`)
      ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci;

