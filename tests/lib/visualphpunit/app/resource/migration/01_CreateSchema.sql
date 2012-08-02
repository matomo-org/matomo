CREATE DATABASE `vpu` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `vpu`;

CREATE TABLE `SuiteResult` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `run_date` datetime NOT NULL,
  `failed` int(11) unsigned NOT NULL,
  `incomplete` int(11) unsigned NOT NULL,
  `skipped` int(11) unsigned NOT NULL,
  `succeeded` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `TestResult` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `run_date` datetime NOT NULL,
  `failed` int(11) unsigned NOT NULL,
  `incomplete` int(11) unsigned NOT NULL,
  `skipped` int(11) unsigned NOT NULL,
  `succeeded` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
