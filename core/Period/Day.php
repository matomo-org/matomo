<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Period;

use Exception;
use Piwik\Date;
use Piwik\Period;

/**
 * Represents a single day period.
 *
 * @api
 */
class Day extends Period
{
    /**
     * The ID of the day period.
     */
    public const PERIOD_ID = 1;

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
     * @ignore
     */
    public function addSubperiod($date)
    {
        throw new Exception("Adding a subperiod is not supported for Day");
    }

    /**
     * Returns the day of the period in the given format
     *
     * @param string $format The date format, as accepted by {@link \Piwik\Date::toString()}.
     * @return string
     */
    public function toString($format = "Y-m-d")
    {
        return $this->date->toString($format);
    }

    /**
     * Returns the day in the format `Y-m-d`.
     *
     * @return string The day in `Y-m-d` format.
     */
    public function __toString()
    {
        return $this->toString();
    }

    /**
     * @ignore
     */
    public function getImmediateChildPeriodLabel()
    {
        return null;
    }

    /**
     * @ignore
     */
    public function getParentPeriodLabel()
    {
        return 'week';
    }
}
