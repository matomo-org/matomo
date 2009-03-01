<?php
// login field now consistenly restricted to maximum 100 length
$tablesToAlter = array(
	'user_language',
	'access',
	'user',
	'user_dashboard',
);
foreach($tablesToAlter as $table) {
	Piwik_Query("ALTER TABLE ". Piwik::prefixTable($table) . " 
				CHANGE `login` `login` VARCHAR( 100 ) NOT NULL");
}
