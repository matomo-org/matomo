<?php
// if you don't include 'index.php', you must also define PIWIK_DOCUMENT_ROOT
// and include "libs/upgradephp/upgrade.php" and "core/Loader.php"
define('PIWIK_INCLUDE_PATH', realpath('..'));
define('PIWIK_USER_PATH', realpath('..'));
define('PIWIK_ENABLE_DISPATCH', false);
define('PIWIK_ENABLE_ERROR_HANDLER', false);
define('PIWIK_ENABLE_SESSION_START', false);

require_once PIWIK_INCLUDE_PATH . "/index.php";
require_once PIWIK_INCLUDE_PATH . "/core/API/Request.php";

Piwik_FrontController::getInstance()->init();

// This inits the API Request with the specified parameters
$request = new Piwik_API_Request('
			method=UserSettings.getResolution
			&idSite=7
			&date=yesterday
			&period=week
			&format=XML
			&filter_limit=3
			&token_auth=anonymous
');
// Calls the API and fetch XML data back
$result = $request->process();
echo $result;

