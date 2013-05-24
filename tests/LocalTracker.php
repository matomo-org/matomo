<?php

$GLOBALS['PIWIK_TRACKER_DEBUG'] = false;
$GLOBALS['PIWIK_TRACKER_DEBUG_FORCE_SCHEDULED_TASKS'] = false;
if (!defined('PIWIK_ENABLE_TRACKING')) {
    define('PIWIK_ENABLE_TRACKING', true);
}

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
        Piwik_Tracker_Cache::$trackerCache = null;
        Piwik_Tracker::setForceIp(null);
        Piwik_Tracker::setForceDateTime(null);
        Piwik_Tracker::setForceVisitorId(null);

        // save some values
        $plugins = Piwik_Config::getInstance()->Plugins['Plugins'];
        $plugins[] = 'DevicesDetection';
        $pluginsTracker = Piwik_Config::getInstance()->Plugins_Tracker['Plugins_Tracker'];
        $oldTrackerConfig = Piwik_Config::getInstance()->Tracker;

        Piwik_PluginsManager::getInstance()->unloadPlugins();

        // modify config
        $GLOBALS['PIWIK_TRACKER_MODE'] = true;
        $GLOBALS['PIWIK_TRACKER_LOCAL_TRACKING'] = true;
        Piwik_Tracker::$initTrackerMode = false;
        Piwik_Tracker::setTestEnvironment($testEnvironmentArgs, $method);

        // set language
        $oldLang = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : '';
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = $this->acceptLanguage;

        // set user agent
        $oldUserAgent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        $_SERVER['HTTP_USER_AGENT'] = $this->userAgent;

        // set cookie
        $oldCookie = $_COOKIE;
        parse_str(parse_url($this->requestCookie, PHP_URL_QUERY), $_COOKIE);

        // do tracking and capture output
        ob_start();

        $localTracker = new Piwik_Tracker();
        $localTracker->main($requests);

        $output = ob_get_contents();

        ob_end_clean();

        // restore vars
        Piwik_Config::getInstance()->Plugins_Tracker['Plugins_Tracker'] = $pluginsTracker;
        Piwik_Config::getInstance()->Tracker = $oldTrackerConfig;
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = $oldLang;
        $_SERVER['HTTP_USER_AGENT'] = $oldUserAgent;
        $_COOKIE = $oldCookie;
        $GLOBALS['PIWIK_TRACKER_LOCAL_TRACKING'] = false;
        $GLOBALS['PIWIK_TRACKER_MODE'] = false;
        unset($_GET['bots']);

        // reload plugins
        Piwik_PluginsManager::getInstance()->loadPlugins($plugins);

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

