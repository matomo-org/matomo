<?php

// restore previously deleted columns
$optionalUpdates = array(
	"ALTER TABLE `".Piwik::prefixTable('log_visit')."` 
		ADD `config_java` TINYINT(1) NOT NULL AFTER `config_flash` ;",
	"ALTER TABLE `".Piwik::prefixTable('log_visit')."` 
		ADD `config_quicktime` TINYINT(1) NOT NULL AFTER `config_director` ;",
);

foreach($optionalUpdates as $update)
{
	try {
		Piwik_Query( $update );
	} catch(Zend_Db_Statement_Exception $e) {
		// ignore mysql code error 1060: duplicate column
		if(!preg_match('/1060/', $e->getMessage()))
		{
			throw $e;
		}
	}
}

Piwik_Query( "ALTER TABLE `".Piwik::prefixTable('log_visit')."` 
		ADD `config_gears` TINYINT(1) NOT NULL AFTER  `config_windowsmedia`,
		ADD `config_silverlight` TINYINT(1) NOT NULL AFTER `config_gears` ;");
