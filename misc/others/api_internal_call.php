<?php
use Piwik\API\Request;
use Piwik\FrontController;

define('PIWIK_INCLUDE_PATH', realpath('../..'));
define('PIWIK_USER_PATH', realpath('../..'));
define('PIWIK_ENABLE_DISPATCH', false);
define('PIWIK_ENABLE_ERROR_HANDLER', false);
define('PIWIK_ENABLE_SESSION_START', false);

// if you prefer not to include 'index.php', you must also define here PIWIK_DOCUMENT_ROOT
// and include "libs/upgradephp/upgrade.php" and "core/Loader.php"
require_once PIWIK_INCLUDE_PATH . "/index.php";
require_once PIWIK_INCLUDE_PATH . "/core/API/Request.php";

FrontController::getInstance()->init();

// This inits the API Request with the specified parameters
$request = new Request('
			module=API
			&method=UserSettings.getResolution
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

