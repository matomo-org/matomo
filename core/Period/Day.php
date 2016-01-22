<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Period;

use Exception;
use Piwik\Date;
use Piwik\Period;

/**
 */
class Day extends Period
{
    const PERIOD_ID = 1;

    protected $label = 'day';

    /**
     * Returns the day of the period as a string
     *
     * @return string
     */
    public function getPrettyString()
    {
        $out = $this->getDateStart()->toString();
        return $out;
    }

    /**
     * Returns the day of the period as a localized short string
     *
     * @return string
     */
    public function getLocalizedShortString()
    {
        //"Mon 15 Aug"
        $date     = $this->getDateStart();
        $out = $date->getLocalized(Date::DATE_FORMAT_DAY_MONTH);
        return $out;
    }

    /**
     * Returns the day of the period as a localized long string
     *
     * @return string
     */
    public function getLocalizedLongString()
    {
        //"Mon 15 Aug"
        $date     = $this->getDateStart();
        $out = $date->getLocalized(Date::DATE_FORMAT_LONG);
        return $out;
    }

    /**
     * Returns the number of subperiods
     * Always 0, in that case
     *
     * @return int
     */
    public function getNumberOfSubperiods()
    {
        return 0;
    }

    /**
     * Adds a subperiod
     * Not supported for day periods
     *
     * @param $date
     * @throws Exception
     */
    public function addSubperiod($date)
    {
        throw new Exception("Adding a subperiod is not supported for Day");
    }

    /**
     * Returns the day of the period in the given format
     *
     * @param string $format
     * @return string
     */
    public function toString($format = "Y-m-d")
    {
        return $this->date->toString($format);
    }

    /**
     * Returns the current period as a string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }

    public function getImmediateChildPeriodLabel()
    {
        return null;
    }

    public function getParentPeriodLabel()
    {
        return 'week';
    }
}
