<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Updates;

use Piwik\Plugin\Dimension\ActionDimension;
use Piwik\Plugin\Dimension\ConversionDimension;
use Piwik\Plugin\Dimension\VisitDimension;
use Piwik\Updater;
use Piwik\Updates;

class Updates_2_5_0_b1 extends Updates
{
    public static function update()
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
