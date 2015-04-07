<?php

namespace Piwik\Config;

use Exception;
use Piwik\Config;
use Piwik\Ini\IniReadingException;
use Piwik\Piwik;
use Piwik\SettingsServer;

class IniFileChainFactory
{
    /**
     * TODO should not be a static eventually
     * TODO: code redundancies w/ code in Config
     *
     * @return IniFileChain
     * @throws Exception
     * @throws IniReadingException
     */
    public static function get($pathGlobal = null, $pathLocal = null, $pathCommon = null)
    {
        $pathGlobal = $pathGlobal ?: Config::getGlobalConfigPath();
        $pathCommon = $pathCommon ?: Config::getCommonConfigPath();
        $pathLocal = $pathLocal ?: Config::getLocalConfigPath();

        return new IniFileChain(array($pathGlobal, $pathCommon), $pathLocal);
    }
}
