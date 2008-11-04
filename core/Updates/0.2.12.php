<?php

$mandatoryUpdates = array(
	"ALTER TABLE ". Piwik::prefixTable('site') . " CHANGE ts_created ts_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL",
	"ALTER TABLE ". Piwik::prefixTable('log_visit') . " DROP config_color_depth",
);
$error = false;
$message = '';
foreach($mandatoryUpdates as $update)
{
	try {
		Piwik_Query( $update );
	} catch (Exception $e) {
		$error = true;
		$message .= "Error trying to execute the query '". $update ."'.\nThe error was: " . $e->getMessage() ;
	}
}

if($error)
{
	$message .= "\n	Please make sure you execute the queries on your mysql database. 
When you have executed these queries, you can manually edit the ". Piwik::prefixTable('option')." table in your Piwik database, ".
" and set the piwik_version value to 0.2.12. ";
	throw new UpdateErrorException($message);
}