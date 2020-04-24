<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Annotations;

use Piwik\Date;
use Piwik\Period;
use Piwik\Plugins\CoreVisualizations\Visualizations\JqplotGraph\Evolution as EvolutionViz;

/**
 * Annotations plugins. Provides the ability to attach text notes to
 * dates for each sites. Notes can be viewed, modified, deleted or starred.
 *
 */
class Annotations extends \Piwik\Plugin
{
    /**
     * @see \Piwik\Plugin::registerEvents
     */
    public function registerEvents()
    {
        return array(
            'AssetManager.getStylesheetFiles' => 'getStylesheetFiles',
            'AssetManager.getJavaScriptFiles' => 'getJsFiles',
            'Translate.getClientSideTranslationKeys' => 'getClientSideTranslationKeys',
        );
    }

    public function getClientSideTranslationKeys(&$translationKeys)
    {
        $translationKeys[] = 'Intl_Today';
    }

    /**
     * Adds css files for this plugin to the list in the event notification.
     */
    public function getStylesheetFiles(&$stylesheets)
    {
        $stylesheets[] = "plugins/Annotations/stylesheets/annotations.less";
    }

    /**
     * Adds js files for this plugin to the list in the event notification.
     */
    public function getJsFiles(&$jsFiles)
    {
        $jsFiles[] = "plugins/Annotations/javascripts/annotations.js";
    }


    /**
     * Returns start & end dates for the range described by a period and optional lastN
     * argument.
     *
     * @param string|bool $date The start date of the period (or the date range of a range
     *                           period).
     * @param string $period The period type ('day', 'week', 'month', 'year' or 'range').
     * @param bool|int $lastN Whether to include the last N periods in the range or not.
     *                         Ignored if period == range.
     *
     * @return Date[]   array of Date objects or array(false, false)
     */
    public static function getDateRangeForPeriod($date, $period, $lastN = false)
    {
        if ($date === false) {
            return array(false, false);
        }

        $isMultiplePeriod = Period\Range::isMultiplePeriod($date, $period);

        // if the range is just a normal period (or the period is a range in which case lastN is ignored)
        if ($period == 'range') {
            $oPeriod = new Period\Range('day', $date);
            $startDate = $oPeriod->getDateStart();
            $endDate = $oPeriod->getDateEnd();
        } else if ($lastN == false && !$isMultiplePeriod) {
            $oPeriod = Period\Factory::build($period, Date::factory($date));
            $startDate = $oPeriod->getDateStart();
            $endDate = $oPeriod->getDateEnd();
        } else { // if the range includes the last N periods or is a multiple period
            if (!$isMultiplePeriod) {
                list($date, $lastN) = EvolutionViz::getDateRangeAndLastN($period, $date, $lastN);
            }
            list($startDate, $endDate) = explode(',', $date);

            $startDate = Date::factory($startDate);
            $endDate = Date::factory($endDate);
        }
        return array($startDate, $endDate);
    }
}
