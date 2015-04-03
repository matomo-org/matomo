<?php

namespace Piwik\Config;

use Exception;
use Piwik\Config;
use Piwik\Ini\IniReadingException;
use Piwik\Piwik;
use Piwik\SettingsServer;

class IniFileChainFactory
{
    static $instance = null;

    /**
     * TODO should not be a static eventually
     *
     * @return IniFileChain
     * @throws Exception
     * @throws IniReadingException
     */
    public static function get($pathGlobal = null, $pathLocal = null, $pathCommon = null)
    {
        if (self::$instance) {
            return self::$instance;
        }

        self::$instance = new IniFileChain();

        $inTrackerRequest = SettingsServer::isTrackerApiRequest();

        $pathGlobal = $pathGlobal ?: Config::getGlobalConfigPath();
        $pathCommon = $pathCommon ?: Config::getCommonConfigPath();
        $pathLocal = $pathLocal ?: Config::getLocalConfigPath();

        // read defaults from global.ini.php
        if (!is_readable($pathGlobal) && $inTrackerRequest) {
            // TODO should we throw an exception here? and what about the translation that will not work?
            throw new Exception(Piwik::translate('General_ExceptionConfigurationFileNotFound', array($pathGlobal)));
        }

        try {
            self::$instance->reload(array($pathGlobal, $pathCommon), $pathLocal);
        } catch (IniReadingException $e) {
            // TODO why a different behavior here? This needs a comment
            if ($inTrackerRequest) {
                throw $e;
            }
        }

        return self::$instance;
    }

    public static function unsetInstance()
    {
        self::$instance = null;
    }
}
