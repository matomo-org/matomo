<?php


//TODO 
// - design Caching, in terms of API, piwik.php loading data for a given website (from one file)
// - design Options, how WP plugins make use of it, reuse same pattern
function Piwik_GetOption($name)
{
	try {
		return Piwik_FetchOne( 'SELECT option_value 
							FROM ' . Piwik::prefixTable('option') . ' 
							WHERE option_name = ?', 
							$name); 
	} catch(Exception $e) {
		return false;
	}
}

function Piwik_UpdateOption($name, $value)
{
	try {
		return Piwik_Query('INSERT INTO '. Piwik::prefixTable('option') . ' (option_name, option_value) 
						VALUES (?, ?) 
						ON DUPLICATE KEY UPDATE option_value = ?', 
						array($name, $value, $value));
	} catch(Exception $e) {
		return false;
	}
}

/**
 * 
 CREATE TABLE `piwik_trunk`.`option` (
`idoption` BIGINT NOT NULL AUTO_INCREMENT ,
`option_name` VARCHAR( 64 ) NOT NULL ,
`option_value` LONGTEXT NOT NULL ,
PRIMARY KEY ( `idoption` , `option_name` )
) ENGINE = MYISAM 
 *
 */
