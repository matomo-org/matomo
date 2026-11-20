CREATE TABLE `matomo_user` (
  `login` TEXT NOT NULL COLLATE NOCASE,
  `password` TEXT NOT NULL COLLATE NOCASE,
  `email` TEXT NOT NULL COLLATE NOCASE,
  `twofactor_secret` TEXT NOT NULL DEFAULT '' COLLATE NOCASE,
  `superuser_access` INTEGER NOT NULL DEFAULT '0',
  `date_registered` TEXT,
  `ts_password_modified` TEXT,
  `ts_sessions_invalidated` TEXT,
  `idchange_last_viewed` INTEGER,
  `invited_by` TEXT COLLATE NOCASE,
  `invite_token` TEXT COLLATE NOCASE,
  `invite_link_token` TEXT COLLATE NOCASE,
  `invite_expired_at` TEXT,
  `invite_accept_at` TEXT,
  `ts_changes_shown` TEXT,
  `ts_last_seen` TEXT,
  `ts_inactivity_notified` TEXT,
  PRIMARY KEY (`login`)
);
CREATE UNIQUE INDEX `matomo_user.uniq_email` ON `matomo_user` (`email`);

CREATE TABLE `matomo_user_token_auth` (
  `idusertokenauth` INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
  `login` TEXT NOT NULL COLLATE NOCASE,
  `description` TEXT NOT NULL COLLATE NOCASE,
  `password` TEXT NOT NULL COLLATE NOCASE,
  `hash_algo` TEXT NOT NULL COLLATE NOCASE,
  `system_token` INTEGER NOT NULL DEFAULT 0,
  `last_used` TEXT,
  `date_created` TEXT NOT NULL,
  `date_expired` TEXT,
  `secure_only` INTEGER NOT NULL DEFAULT '0',
  `access_level` TEXT COLLATE NOCASE,
  `ts_rotation_notified` TEXT,
  `ts_expiration_warning_notified` TEXT
);
CREATE UNIQUE INDEX `matomo_user_token_auth.uniq_password` ON `matomo_user_token_auth` (`password`);

CREATE TABLE `matomo_twofactor_recovery_code` (
  `idrecoverycode` INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
  `login` TEXT NOT NULL COLLATE NOCASE,
  `recovery_code` TEXT NOT NULL COLLATE NOCASE
);

CREATE TABLE `matomo_access` (
  `idaccess` INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
  `login` TEXT NOT NULL COLLATE NOCASE,
  `idsite` INTEGER NOT NULL,
  `access` TEXT COLLATE NOCASE
);
CREATE INDEX `matomo_access.index_loginidsite` ON `matomo_access` (`login`, `idsite`);

CREATE TABLE `matomo_site` (
  `idsite` INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
  `name` TEXT NOT NULL COLLATE NOCASE,
  `description` TEXT NOT NULL DEFAULT '' COLLATE NOCASE,
  `main_url` TEXT NOT NULL COLLATE NOCASE,
  `ts_created` TEXT,
  `ecommerce` INTEGER DEFAULT 0,
  `sitesearch` INTEGER DEFAULT 1,
  `sitesearch_keyword_parameters` TEXT NOT NULL COLLATE NOCASE,
  `sitesearch_category_parameters` TEXT NOT NULL COLLATE NOCASE,
  `timezone` TEXT NOT NULL COLLATE NOCASE,
  `currency` TEXT NOT NULL COLLATE NOCASE,
  `exclude_unknown_urls` INTEGER DEFAULT 0,
  `excluded_ips` TEXT NOT NULL COLLATE NOCASE,
  `excluded_parameters` TEXT NOT NULL COLLATE NOCASE,
  `excluded_user_agents` TEXT NOT NULL COLLATE NOCASE,
  `excluded_referrers` TEXT NOT NULL COLLATE NOCASE,
  `group` TEXT NOT NULL COLLATE NOCASE,
  `type` TEXT NOT NULL COLLATE NOCASE,
  `keep_url_fragment` INTEGER NOT NULL DEFAULT 0,
  `creator_login` TEXT COLLATE NOCASE
);

CREATE TABLE `matomo_plugin_setting` (
  `plugin_name` TEXT NOT NULL COLLATE NOCASE,
  `setting_name` TEXT NOT NULL COLLATE NOCASE,
  `setting_value` TEXT NOT NULL COLLATE NOCASE,
  `json_encoded` INTEGER NOT NULL DEFAULT 0,
  `user_login` TEXT NOT NULL DEFAULT '' COLLATE NOCASE,
  `idplugin_setting` INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL
);
CREATE INDEX `matomo_plugin_setting.plugin_name` ON `matomo_plugin_setting` (`plugin_name`, `user_login`);

CREATE TABLE `matomo_site_setting` (
  `idsite` INTEGER NOT NULL,
  `plugin_name` TEXT NOT NULL COLLATE NOCASE,
  `setting_name` TEXT NOT NULL COLLATE NOCASE,
  `setting_value` TEXT NOT NULL COLLATE NOCASE,
  `json_encoded` INTEGER NOT NULL DEFAULT 0,
  `idsite_setting` INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL
);
CREATE INDEX `matomo_site_setting.idsite` ON `matomo_site_setting` (`idsite`, `plugin_name`);

CREATE TABLE `matomo_site_url` (
  `idsite` INTEGER NOT NULL,
  `url` TEXT NOT NULL COLLATE NOCASE,
  PRIMARY KEY (`idsite`, `url`)
);

CREATE TABLE `matomo_goal` (
  `idsite` INTEGER NOT NULL,
  `idgoal` INTEGER NOT NULL,
  `name` TEXT NOT NULL COLLATE NOCASE,
  `description` TEXT NOT NULL DEFAULT '' COLLATE NOCASE,
  `match_attribute` TEXT NOT NULL COLLATE NOCASE,
  `pattern` TEXT NOT NULL COLLATE NOCASE,
  `pattern_type` TEXT NOT NULL COLLATE NOCASE,
  `case_sensitive` INTEGER NOT NULL,
  `allow_multiple` INTEGER NOT NULL,
  `revenue` REAL NOT NULL,
  `deleted` INTEGER NOT NULL DEFAULT '0',
  `event_value_as_revenue` INTEGER NOT NULL DEFAULT '0',
  PRIMARY KEY (`idsite`, `idgoal`)
);

CREATE TABLE `matomo_logger_message` (
  `idlogger_message` INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
  `tag` TEXT COLLATE NOCASE,
  `timestamp` TEXT,
  `level` TEXT COLLATE NOCASE,
  `message` TEXT COLLATE NOCASE
);

CREATE TABLE `matomo_log_action` (
  `idaction` INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
  `name` TEXT COLLATE NOCASE,
  `hash` INTEGER NOT NULL,
  `type` INTEGER,
  `url_prefix` INTEGER
);
CREATE INDEX `matomo_log_action.index_type_hash` ON `matomo_log_action` (`type`, `hash`);

CREATE TABLE `matomo_log_visit` (
  `idvisit` INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
  `idsite` INTEGER NOT NULL,
  `idvisitor` BLOB NOT NULL,
  `visit_last_action_time` TEXT NOT NULL,
  `config_id` BLOB NOT NULL,
  `location_ip` BLOB NOT NULL
);
CREATE INDEX `matomo_log_visit.index_idsite_config_datetime` ON `matomo_log_visit` (`idsite`, `config_id`, `visit_last_action_time`);
CREATE INDEX `matomo_log_visit.index_idsite_datetime` ON `matomo_log_visit` (`idsite`, `visit_last_action_time`);
CREATE INDEX `matomo_log_visit.index_idsite_idvisitor_time` ON `matomo_log_visit` (`idsite`, `idvisitor`, `visit_last_action_time` DESC);

CREATE TABLE `matomo_log_conversion_item` (
  `idsite` INTEGER NOT NULL,
  `idvisitor` BLOB NOT NULL,
  `server_time` TEXT NOT NULL,
  `idvisit` INTEGER NOT NULL,
  `idorder` TEXT NOT NULL COLLATE NOCASE,
  `idaction_sku` INTEGER NOT NULL,
  `idaction_name` INTEGER NOT NULL,
  `idaction_category` INTEGER NOT NULL,
  `idaction_category2` INTEGER NOT NULL,
  `idaction_category3` INTEGER NOT NULL,
  `idaction_category4` INTEGER NOT NULL,
  `idaction_category5` INTEGER NOT NULL,
  `price` REAL NOT NULL,
  `quantity` INTEGER NOT NULL,
  `deleted` INTEGER NOT NULL,
  PRIMARY KEY (`idvisit`, `idorder`, `idaction_sku`)
);
CREATE INDEX `matomo_log_conversion_item.index_idsite_servertime` ON `matomo_log_conversion_item` (`idsite`, `server_time`);

CREATE TABLE `matomo_log_conversion` (
  `idvisit` INTEGER NOT NULL,
  `idsite` INTEGER NOT NULL,
  `idvisitor` BLOB NOT NULL,
  `server_time` TEXT NOT NULL,
  `idaction_url` INTEGER DEFAULT NULL,
  `idlink_va` INTEGER DEFAULT NULL,
  `idgoal` INTEGER NOT NULL,
  `buster` INTEGER NOT NULL,
  `idorder` TEXT DEFAULT NULL COLLATE NOCASE,
  `items` INTEGER DEFAULT NULL,
  `url` TEXT NOT NULL COLLATE NOCASE,
  `revenue` REAL DEFAULT NULL,
  `revenue_shipping` REAL DEFAULT NULL,
  `revenue_subtotal` REAL DEFAULT NULL,
  `revenue_tax` REAL DEFAULT NULL,
  `revenue_discount` REAL DEFAULT NULL,
  `pageviews_before` INTEGER DEFAULT NULL,
  PRIMARY KEY (`idvisit`, `idgoal`, `buster`)
);
CREATE UNIQUE INDEX `matomo_log_conversion.unique_idsite_idorder` ON `matomo_log_conversion` (`idsite`, `idorder`);
CREATE INDEX `matomo_log_conversion.index_idsite_datetime` ON `matomo_log_conversion` (`idsite`, `server_time`);

CREATE TABLE `matomo_log_link_visit_action` (
  `idlink_va` INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
  `idsite` INTEGER NOT NULL,
  `idvisitor` BLOB NOT NULL,
  `idvisit` INTEGER NOT NULL,
  `idaction_url_ref` INTEGER DEFAULT 0,
  `idaction_name_ref` INTEGER,
  `custom_float` REAL DEFAULT NULL,
  `pageview_position` INTEGER DEFAULT NULL
);
CREATE INDEX `matomo_log_link_visit_action.index_idvisit` ON `matomo_log_link_visit_action` (`idvisit`);

CREATE TABLE `matomo_log_page_view_time` (
  `idpageviewtime` INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
  `idsite` INTEGER NOT NULL,
  `idvisit` INTEGER NOT NULL,
  `idlink_va` INTEGER NOT NULL,
  `idpageview` TEXT DEFAULT NULL,
  `idaction_url` INTEGER DEFAULT NULL,
  `idaction_name` INTEGER DEFAULT NULL,
  `server_time` TEXT NOT NULL,
  `time_spent` INTEGER NOT NULL DEFAULT 0
);
CREATE UNIQUE INDEX `matomo_log_page_view_time.unique_idvisit_idlink_va` ON `matomo_log_page_view_time` (`idvisit`, `idlink_va`);
CREATE INDEX `matomo_log_page_view_time.index_idvisit_idpageview` ON `matomo_log_page_view_time` (`idvisit`, `idpageview`);
CREATE INDEX `matomo_log_page_view_time.index_idvisit_server_time` ON `matomo_log_page_view_time` (`idvisit`, `server_time`);
CREATE INDEX `matomo_log_page_view_time.index_idsite_server_time` ON `matomo_log_page_view_time` (`idsite`, `server_time`);
CREATE INDEX `matomo_log_page_view_time.index_idvisit_idaction_url_time` ON `matomo_log_page_view_time` (`idvisit`, `idaction_url`, `time_spent`);
CREATE INDEX `matomo_log_page_view_time.index_idvisit_idaction_name_time` ON `matomo_log_page_view_time` (`idvisit`, `idaction_name`, `time_spent`);

CREATE TABLE `matomo_log_profiling` (
  `query` TEXT NOT NULL COLLATE NOCASE,
  `count` INTEGER,
  `sum_time_ms` REAL,
  `idprofiling` INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL
);
CREATE UNIQUE INDEX `matomo_log_profiling.query` ON `matomo_log_profiling` (substr(`query`, 1, 100) COLLATE NOCASE);

CREATE TABLE `matomo_option` (
  `option_name` TEXT NOT NULL COLLATE NOCASE,
  `option_value` TEXT NOT NULL COLLATE NOCASE,
  `autoload` INTEGER NOT NULL DEFAULT '1',
  PRIMARY KEY (`option_name`)
);
CREATE INDEX `matomo_option.autoload` ON `matomo_option` (`autoload`);

CREATE TABLE `matomo_session` (
  `id` TEXT NOT NULL COLLATE NOCASE,
  `modified` INTEGER,
  `lifetime` INTEGER,
  `data` TEXT COLLATE NOCASE,
  PRIMARY KEY (`id`)
);

CREATE TABLE `matomo_archive_numeric` (
  `idarchive` INTEGER NOT NULL,
  `name` TEXT NOT NULL COLLATE NOCASE,
  `idsite` INTEGER,
  `date1` TEXT,
  `date2` TEXT,
  `period` INTEGER,
  `ts_archived` TEXT,
  `value` REAL,
  PRIMARY KEY (`idarchive`, `name`)
);
CREATE INDEX `matomo_archive_numeric.index_idsite_dates_period` ON `matomo_archive_numeric` (`idsite`, `date1`, `date2`, `period`, `name`);
CREATE INDEX `matomo_archive_numeric.index_period_archived` ON `matomo_archive_numeric` (`period`, `ts_archived`);

CREATE TABLE `matomo_archive_blob` (
  `idarchive` INTEGER NOT NULL,
  `name` TEXT NOT NULL COLLATE NOCASE,
  `idsite` INTEGER,
  `date1` TEXT,
  `date2` TEXT,
  `period` INTEGER,
  `ts_archived` TEXT,
  `value` BLOB,
  PRIMARY KEY (`idarchive`, `name`)
);
CREATE INDEX `matomo_archive_blob.index_period_archived` ON `matomo_archive_blob` (`period`, `ts_archived`);

CREATE TABLE `matomo_archiving_metrics` (
  `metadataid` INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
  `idarchive` INTEGER NOT NULL,
  `idsite` INTEGER NOT NULL,
  `archive_name` TEXT NOT NULL COLLATE NOCASE,
  `date1` TEXT NOT NULL,
  `date2` TEXT NOT NULL,
  `period` INTEGER NOT NULL,
  `ts_started` TEXT NOT NULL,
  `ts_finished` TEXT NOT NULL,
  `total_time` INTEGER NOT NULL,
  `total_time_exclusive` INTEGER NOT NULL,
  `is_temporary` INTEGER NOT NULL DEFAULT 0
);
CREATE INDEX `matomo_archiving_metrics.index_idarchive` ON `matomo_archiving_metrics` (`idarchive`);
CREATE INDEX `matomo_archiving_metrics.index_idsite_archive_name` ON `matomo_archiving_metrics` (`idsite`, `archive_name`);
CREATE INDEX `matomo_archiving_metrics.index_idsite_date1_period` ON `matomo_archiving_metrics` (`idsite`, `date1`, `period`);

CREATE TABLE `matomo_archive_invalidations` (
  `idinvalidation` INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
  `idarchive` INTEGER,
  `name` TEXT NOT NULL COLLATE NOCASE,
  `idsite` INTEGER NOT NULL,
  `date1` TEXT NOT NULL,
  `date2` TEXT NOT NULL,
  `period` INTEGER NOT NULL,
  `ts_invalidated` TEXT,
  `ts_started` TEXT,
  `status` INTEGER DEFAULT 0,
  `report` TEXT COLLATE NOCASE,
  `processing_host` TEXT DEFAULT NULL COLLATE NOCASE,
  `process_id` TEXT DEFAULT NULL COLLATE NOCASE
);
CREATE INDEX `matomo_archive_invalidations.index_idsite_dates_period_name` ON `matomo_archive_invalidations` (`idsite`, `date1`, `period`);
CREATE INDEX `matomo_archive_invalidations.index_idsite_status_date1_period` ON `matomo_archive_invalidations` (`idsite`, `status`, `date1`, `period`);

CREATE TABLE `matomo_archive_failures` (
  `idfailure` INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
  `idsite` INTEGER NOT NULL,
  `name` TEXT NOT NULL COLLATE NOCASE,
  `period` INTEGER NOT NULL,
  `date1` TEXT NOT NULL,
  `date2` TEXT NOT NULL,
  `report` TEXT NOT NULL DEFAULT '' COLLATE NOCASE,
  `archive_hash` TEXT NOT NULL COLLATE NOCASE,
  `attempts` INTEGER NOT NULL DEFAULT 0,
  `last_error` TEXT COLLATE NOCASE,
  `ts_first_failed` TEXT NOT NULL,
  `ts_last_failed` TEXT NOT NULL,
  `ts_retry_after` TEXT NOT NULL
);
CREATE UNIQUE INDEX `matomo_archive_failures.unique_archive` ON `matomo_archive_failures` (`idsite`, `archive_hash`, `period`, `date1`, `date2`);

CREATE TABLE `matomo_sequence` (
  `name` TEXT NOT NULL COLLATE NOCASE,
  `value` INTEGER NOT NULL,
  PRIMARY KEY (`name`)
);

CREATE TABLE `matomo_brute_force_log` (
  `id_brute_force_log` INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
  `ip_address` TEXT DEFAULT NULL COLLATE NOCASE,
  `attempted_at` TEXT NOT NULL,
  `login` TEXT COLLATE NOCASE
);
CREATE INDEX `matomo_brute_force_log.index_ip_address` ON `matomo_brute_force_log` (`ip_address`);

CREATE TABLE `matomo_tracking_failure` (
  `idsite` INTEGER NOT NULL,
  `idfailure` INTEGER NOT NULL,
  `date_first_occurred` TEXT NOT NULL,
  `request_url` TEXT NOT NULL COLLATE NOCASE,
  PRIMARY KEY (`idsite`, `idfailure`)
);

CREATE TABLE `matomo_locks` (
  `key` TEXT NOT NULL COLLATE NOCASE,
  `value` TEXT DEFAULT NULL COLLATE NOCASE,
  `expiry_time` INTEGER DEFAULT 9999999999,
  PRIMARY KEY (`key`)
);

CREATE TABLE `matomo_plugin_trial_request` (
  `idrequest` INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
  `plugin_name` TEXT NOT NULL COLLATE NOCASE,
  `login` TEXT COLLATE NOCASE,
  `ts_requested` TEXT NOT NULL
);
CREATE INDEX `matomo_plugin_trial_request.index_plugin_name_ts_requested` ON `matomo_plugin_trial_request` (`plugin_name`, `ts_requested`);
CREATE UNIQUE INDEX `matomo_plugin_trial_request.index_login_plugin_name` ON `matomo_plugin_trial_request` (`login`, `plugin_name`);

CREATE TABLE `matomo_changes` (
  `idchange` INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
  `created_time` TEXT NOT NULL,
  `plugin_name` TEXT NOT NULL COLLATE NOCASE,
  `version` TEXT NOT NULL COLLATE NOCASE,
  `title` TEXT NOT NULL COLLATE NOCASE,
  `description` TEXT COLLATE NOCASE,
  `link_name` TEXT COLLATE NOCASE,
  `link` TEXT COLLATE NOCASE
);
CREATE UNIQUE INDEX `matomo_changes.unique_plugin_version_title` ON `matomo_changes` (`plugin_name`, `version`, substr(`title`, 1, 100) COLLATE NOCASE);

CREATE TABLE `matomo_annotations` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
  `idsite` INTEGER NOT NULL,
  `date` TEXT NOT NULL,
  `note` TEXT NOT NULL COLLATE NOCASE,
  `starred` INTEGER NOT NULL DEFAULT 0,
  `user` TEXT NOT NULL COLLATE NOCASE
);
CREATE INDEX `matomo_annotations.index_idsite_date` ON `matomo_annotations` (`idsite`, `date`);

