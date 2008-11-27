<?php
try {
	Piwik_Query('DROP TABLE IF EXISTS `'. Piwik::prefixTable('option') . '`');
	$tables = Piwik::getTablesCreateSql();
	$optionTable = $tables['option'];
	Piwik_Query( $optionTable );
} catch (Exception $e) {
	throw new UpdateErrorException("Error trying to re-create the option table in Mysql: " . $e->getMessage());
}
