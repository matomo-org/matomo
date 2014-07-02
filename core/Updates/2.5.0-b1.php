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
use Piwik\Plugin\Dimension\ActionDimension;
use Piwik\Plugin\Dimension\ConversionDimension;
use Piwik\Plugin\Dimension\VisitDimension;
use Piwik\Updater;
use Piwik\Updates;

class Updates_2_5_0_b1 extends Updates
{
    public static function update()
    {
        self::updateConfig();
        self::markDimensionsAsInstalled();
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

    private static function markDimensionsAsInstalled()
    {
        foreach (VisitDimension::getAllDimensions() as $dimension) {
            if ($dimension->getColumnName()) {
                $component = 'log_visit.' . $dimension->getColumnName();
                Updater::recordComponentSuccessfullyUpdated($component, $dimension->getVersion());
            }
        }

        foreach (ActionDimension::getAllDimensions() as $dimension) {
            if ($dimension->getColumnName()) {
                $component = 'log_link_visit_action.' . $dimension->getColumnName();
                Updater::recordComponentSuccessfullyUpdated($component, $dimension->getVersion());
            }
        }

        foreach (ConversionDimension::getAllDimensions() as $dimension) {
            if ($dimension->getColumnName()) {
                $component = 'log_conversion.' . $dimension->getColumnName();
                Updater::recordComponentSuccessfullyUpdated($component, $dimension->getVersion());
            }
        }
    }
}
