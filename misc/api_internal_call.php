<?php
define('PIWIK_INCLUDE_PATH', '..');
define('ENABLE_DISPATCH', false);	
require_once PIWIK_INCLUDE_PATH . "/index.php";
require_once PIWIK_INCLUDE_PATH . "/modules/API/Request.php";


Piwik_FrontController::getInstance()->init();

// We call the API from a php code
// it will check that you have the necessary rights
// - either you are loggued in piwik and have a cookie in your browser
// - or you will have to add the token_auth=XXX to the request string to authenticate
//   beware that the token_auth changes every time you change your password
$request = new Piwik_API_Request('
			method=UserSettings.getResolution
			&idSite=1
			&date=yesterday
			&period=week
			&format=XML
			&filter_limit=3
');
$result = $request->process();
echo $result;

?>