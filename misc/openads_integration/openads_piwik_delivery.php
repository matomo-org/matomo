<?php
// reads the template file containing the JS tag
// and builds the JS tag using the config variable
/**
 * Function called statically by the Openads-Piwik delivery engine plugin
 * Returns the javascript to print in the HEAD 
 * 
 * @param int The publisher ID in openads
 * @return string A javascript code or the empty string
 */
function getJavascriptTag( $openadsPublisherId )
{
	// reads the $piwikUrl from the Piwik Config file
	// if not available, returns empty string as it means piwik is not installed
	
	// The mapping is applied as follows
	// Piwik_SiteId from the openads PublisherId
//	return str_replace('{PUBLISHER_ID', $id, file_get_contents())
	
}