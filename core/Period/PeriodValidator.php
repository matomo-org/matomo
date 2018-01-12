<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Period;

use Piwik\Config;

class PeriodValidator
{
    /**
     * @param string $period
     * @return bool
     */
    public function isPeriodAllowedForUI($period)
    {
        return in_array($period, $this->getPeriodsAllowedForUI());
    }

    /**
     * @param string $period
     * @return bool
     */
    public function isPeriodAllowedForAPI($period)
    {
        return in_array($period, $this->getPeriodsAllowedForAPI());
    }

    /**
     * @return string[]
     */
    public function getPeriodsAllowedForUI()
    {
        $periodsAllowed = Config::getInstance()->General['enabled_periods_UI'];
        $periodsAllowed = array_map('trim', explode(',', $periodsAllowed));
        $periodsAllowed = array_unique($periodsAllowed);
        $periodsAllowed = array_values($periodsAllowed);
        return $periodsAllowed;
    }

    /**
     * @return string[]
     */
    public function getPeriodsAllowedForAPI()
    {
        $periodsAllowed = Config::getInstance()->General['enabled_periods_API'];
        $periodsAllowed = array_map('trim', explode(',', $periodsAllowed));
        $periodsAllowed = array_unique($periodsAllowed);
        $periodsAllowed = array_values($periodsAllowed);
        return $periodsAllowed;
    }
}
