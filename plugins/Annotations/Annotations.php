<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Annotations;

use Piwik\Date;
use Piwik\Period;
use Piwik\Piwik;
use Piwik\Plugins\CoreVisualizations\Visualizations\JqplotGraph\Evolution as EvolutionViz;

/**
 * Annotations plugins. Provides the ability to attach text notes to
 * dates for each site. Notes can be viewed, modified, deleted or starred.
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
     * @param int|null $idSite the id of the current site, used to get timezone
     *
     * @return Date[]|bool[]   array of Date objects or array(false, false)
     */
    public static function getDateRangeForPeriod($date, $period, $lastN = false, ?int $idSite = null): array
    {
        if ($date === false) {
            return [false, false];
        }

        $isMultiplePeriod = Period\Range::isMultiplePeriod($date, $period);

        // if the range is just a normal period (or the period is a range in which case lastN is ignored)
        if ($period == 'range') {
            $oPeriod = new Period\Range('day', $date);
            $startDate = $oPeriod->getDateStart()->getStartOfDay();
            $endDate = $oPeriod->getDateEnd()->getStartOfDay();
        } elseif ($lastN == false && !$isMultiplePeriod) {
            $oPeriod = Period\Factory::build($period, Date::factory($date));
            $startDate = $oPeriod->getDateStart();
            $endDate = $oPeriod->getDateEnd();
        } else { // if the range includes the last N periods or is a multiple period
            if (!$isMultiplePeriod) {
                [$date, $lastN] = EvolutionViz::getDateRangeAndLastN($period, $date, $lastN, $idSite);
            }
            [$startDate, $endDate] = explode(',', $date);

            $startDate = Date::factory($startDate);
            $endDate = Date::factory($endDate);
        }
        return [$startDate, $endDate];
    }

    /**
     * Returns true if the current user can modify or delete a specific annotation.
     *
     * A user can modify/delete a note if the user has write access for the site OR
     * the user has view access, is not the anonymous user and is the user that
     * created the note in question.
     *
     * @param array $annotation The annotation.
     * @return bool
     */
    public static function canUserModifyOrDelete(array $annotation): bool
    {
        // user can save if user is admin or if has view access, is not anonymous & is user who wrote note
        return Piwik::isUserHasWriteAccess($annotation['idsite'])
            || (!Piwik::isUserIsAnonymous()
                && Piwik::getCurrentUserLogin() === $annotation['user']);
    }
}
