<?php
/* 
 == Description ==
 This script allows to track statistics using Piwik, without revealing the
 Piwik Server URL. This is useful for users who track multiple websites 
 in the same Piwik server, but don't want to show in the source code of all tracked 
 websites the Piwik server URL.
 
 == Requirements ==
 To run this properly you will need
 - Piwik server latest version
 - One or several website(s) to track with this Piwik server, for example http://example.com
 - The website to track must run on a server with PHP5 support
   Also in your php.ini you must check that the following is set: "allow_url_fopen = On"

 == How to track example.com in your Piwik without revealing the Piwik server URL? ==

 1) In your Piwik server, login as Super user
 2) create a user, set the login for example: "UserTrackingAPI"
 3) Assign this user "admin" permission on all websites you wish to track without showing the Piwik URL
 4) Copy the "token_auth" for this user, and paste it below in this file, in $TOKEN_AUTH = "xyz"
 5) Edit below the $PIWIK_URL variable and put inside your Piwik server URL
 6) Upload this modified piwik.php file in the website root directory, for example at: http://example.com/piwik.php
    This file will be called by the Piwik Javascript, instead of calling directly the Piwik Server URL.
 7) Go to Piwik > Settings > Websites > Show Javascript Tracking Code.
    Copy the Javascript snippet. Then, edit this code and change the first lines to the following:
		<script type="text/javascript">
		var pkBaseURL = (("https:" == document.location.protocol) ? "https://example.com/" : "http://example.com/");
		document.write(unescape("%3Cscript src='" + pkBaseURL + "piwik.php' type='text/javascript'%3E%3C/script%3E"));
		</script><script type="text/javascript">
		try {
		var piwikTracker = Piwik.getTracker(pkBaseURL + "piwik.php", 1);
		[...]
		</script>

    What's changed in this code snippet compared to the normal Piwik code?
	    A) the Piwik URL was replaced by your website URL
	    B) the "piwik.js" became "piwik.php" because the proxy script will serve the Javascript file
	    C) the <noscript> part of the code at the end was removed, 
	       since it is not currently used and it contains the Piwik URL which you want to hide.
 8) Paste the modified Piwik Javascript code in your website "example.com" pages you wish to track.
    This modified Javascript Code will then track visits/pages/conversions by calling example.com/piwik.php
    which will then automatically call the (hidden) Piwik Server URL.
 9) Done!
    At this stage, example.com should be tracked by your Piwik without showing the Piwik server URL.
    Repeat the steps 6), 7) and 8) for each website you wish to track in Piwik.
*/

// Edit the line below, and replace http://piwik-server.com/piwik/ 
// with your Piwik URL ending with a slash.
// This URL will never be revealed to visitors or search engines.
$PIWIK_URL = 'http://piwik-server.com/piwik/';

// Edit the line below, and replace xyz by the token_auth for the user "UserTrackingAPI"
// which you created when you followed instructions above.
$TOKEN_AUTH = 'xyz';

// Maximum time, in seconds, to wait for the Piwik server to return the 1*1 GIF
$timeout = 5;



// DO NOT MODIFY BELOW
// ---------------------------
// 1) PIWIK.JS PROXY: No _GET parameter, we serve the JS file
if(empty($_GET)) 
{
	$modifiedSince = false;
	if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE']))
	{
		$modifiedSince = $_SERVER['HTTP_IF_MODIFIED_SINCE'];
		// strip any trailing data appended to header
		if (false !== ($semicolon = strpos($modifiedSince, ';')))
		{
			$modifiedSince = strtotime(substr($modifiedSince, 0, $semicolon));
		}
	}
	// Re-download the piwik.js once a day maximum
	$lastModified = time()-86400;

	// set HTTP response headers
	header('Vary: Accept-Encoding');
	
	// Returns 304 if not modified since
	if (!empty($modifiedSince) && $modifiedSince < $lastModified)
	{
		header($_SERVER['SERVER_PROTOCOL'] . ' 304 Not Modified');
	}
	else
	{
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
		@header('Content-Type: application/javascript; charset=UTF-8');
		if( $piwikJs = file_get_contents($PIWIK_URL.'piwik.js')) {
			echo $piwikJs;
		} else {
			header($_SERVER['SERVER_PROTOCOL'] . '505 Internal server error');
		}
	}
	exit;
}

// 2) PIWIK.PHP PROXY: GET parameters found, this is a tracking request, we redirect it to Piwik
$url = $PIWIK_URL."piwik.php?cip=".@$_SERVER['REMOTE_ADDR']."&token_auth=".$TOKEN_AUTH.'&';
foreach($_GET as $key=>$value) { 
	$url .= $key .'='.urlencode($value).'&'; 
}
header("Content-Type: image/gif");
$stream_options = array('http' => array(
	'user_agent' => @$_SERVER['HTTP_USER_AGENT'],
	'header' => "Accept-Language: " . @$_SERVER['HTTP_ACCEPT_LANGUAGE'] . "\r\n" ,
	'timeout' => $timeout
));
$ctx = stream_context_create($stream_options);
echo file_get_contents($url, 0, $ctx);
