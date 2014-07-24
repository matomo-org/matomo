<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Updates;

use Piwik\Config;
use Piwik\Updates;

class Updates_2_5_0_b1 extends Updates
{
    public static function update()
    {
        self::updateConfig();
    }

    private static function updateConfig()
    {
        $config = Config::getInstance();
        $debug  = $config->Debug;

        if (array_key_exists('disable_merged_assets', $debug)) {
            $development = $config->Development;
            $development['disable_merged_assets'] = $debug['disable_merged_assets'];
            unset($debug['disable_merged_assets']);

            $config->Debug       = $debug;
            $config->Development = $development;
            $config->forceSave();
        }
    }

}
