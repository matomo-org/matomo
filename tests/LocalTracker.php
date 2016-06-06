<?php

use Piwik\Config;
use Piwik\Tracker;
use Piwik\Tracker\Cache;

$GLOBALS['PIWIK_TRACKER_DEBUG'] = false;

require_once PIWIK_INCLUDE_PATH . '/core/Tracker.php';
require_once PIWIK_INCLUDE_PATH . '/core/Tracker/Db.php';
require_once PIWIK_INCLUDE_PATH . '/core/Tracker/IgnoreCookie.php';
require_once PIWIK_INCLUDE_PATH . '/core/Tracker/Visit.php';
require_once PIWIK_INCLUDE_PATH . '/core/Tracker/GoalManager.php';
require_once PIWIK_INCLUDE_PATH . '/core/Tracker/Action.php';
require_once PIWIK_INCLUDE_PATH . '/libs/PiwikTracker/PiwikTracker.php';

/**
 * Tracker that uses core/Tracker.php directly.
 */
class Piwik_LocalTracker extends PiwikTracker
{
    protected function sendRequest($url, $method = 'GET', $data = null, $force = false)
    {
        if ($this->DEBUG_APPEND_URL) {
            $url .= $this->DEBUG_APPEND_URL;
        }

        // if doing a bulk request, store the url
        if ($this->doBulkRequests && !$force) {
            $this->storedTrackingActions[] = $url;
            return true;
        }

        if ($method == 'POST') {
            $requests = array();
            foreach ($this->storedTrackingActions as $action) {
                $requests[] = $this->parseUrl($action);
            }

            $testEnvironmentArgs = array();
        } else {
            $testEnvironmentArgs = $this->parseUrl($url);
            $requests = array($testEnvironmentArgs);
        }

        // unset cached values
        Cache::$cache = null;
        Tracker\Visit::$dimensions = null;

        // save some values
        $plugins = Config::getInstance()->Plugins['Plugins'];
        $oldTrackerConfig = Config::getInstance()->Tracker;

        \Piwik\Plugin\Manager::getInstance()->unloadPlugins();

        // modify config
        \Piwik\SettingsServer::setIsTrackerApiRequest();
        $GLOBALS['PIWIK_TRACKER_LOCAL_TRACKING'] = true;
        Tracker::$initTrackerMode = false;
        Tracker::setTestEnvironment($testEnvironmentArgs, $method);

        // set language
        $oldLang = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : '';
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = $this->acceptLanguage;

        // set user agent
        $oldUserAgent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        $_SERVER['HTTP_USER_AGENT'] = $this->userAgent;

        // set cookie
        $oldCookie = $_COOKIE;
//        parse_str(parse_url($this->requestCookie, PHP_URL_QUERY), $_COOKIE);

        // do tracking and capture output
        ob_start();

        $localTracker = new Tracker();
        $request = new Tracker\RequestSet();
        $request->setRequests($requests);

        \Piwik\Plugin\Manager::getInstance()->loadTrackerPlugins();
        $handler = Tracker\Handler\Factory::make();

        $response = $localTracker->main($handler, $request);

        if (!is_null($response)) {
            echo $response;
        }

        $output = ob_get_contents();

        ob_end_clean();

        // restore vars
        Config::getInstance()->Tracker = $oldTrackerConfig;
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = $oldLang;
        $_SERVER['HTTP_USER_AGENT'] = $oldUserAgent;
        $_COOKIE = $oldCookie;
        $GLOBALS['PIWIK_TRACKER_LOCAL_TRACKING'] = false;
        \Piwik\SettingsServer::setIsNotTrackerApiRequest();
        unset($_GET['bots']);

        // reload plugins
        \Piwik\Plugin\Manager::getInstance()->loadPlugins($plugins);

        return $output;
    }

    private function parseUrl($url)
    {
        // parse url
        $query = parse_url($url, PHP_URL_QUERY);
        if ($query === false) {
            return;
        }

        parse_str($query, $args);

        // make sure bots is set if needed
        if (isset($args['bots'])) {
            $_GET['bots'] = true;
        }

        return $args;
    }
}

