<?php


//TODO 
// - design Caching, in terms of API, piwik.php loading data for a given website (from one file)
// - design Options, how WP plugins make use of it, reuse same pattern
function Piwik_GetOption($name)
{
	return Piwik_FetchOne( 'SELECT option_value 
							FROM ' . Piwik::prefixTable('option') . ' 
							WHERE option_name = ?', 
							$name);
}

function Piwik_UpdateOption($name, $value)
{
	return Piwik_Query('INSERT INTO '. Piwik::prefixTable('option') . ' (option_name, option_value) 
						VALUES (?, ?) 
						ON DUPLICATE KEY UPDATE option_value = ?', 
						array($name, $value, $value));
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
