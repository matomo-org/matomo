<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik;

use Piwik\Period\Factory as PeriodFactory;
use Piwik\Period\Range;

/**
 * Date range representation.
 *
 * Piwik allows users to view aggregated statistics for single days and for date
 * ranges consisting of several days. When requesting data, a **date** string and
 * a **period** string must be used to specify the date range that the data regards.
 * This is the class Piwik uses to represent and manipulate those date ranges.
 *
 * There are five types of periods in Piwik: day, week, month, year and range,
 * where **range** is any date range. The reason the other periods exist instead
 * of just **range** is that Piwik will pre-archive reports for days, weeks, months
 * and years, while every custom date range is archived on-demand.
 *
 * @api
 */
abstract class Period
{
    /**
     * Array of subperiods
     * @var \Piwik\Period[]
     */
    protected $subperiods = array();
    protected $subperiodsProcessed = false;

    /**
     * @var string
     */
    protected $label = null;

    /**
     * @var Date
     */
    protected $date = null;

    /**
     * Constructor.
     *
     * @param Date $date
     * @ignore
     */
    public function __construct(Date $date)
    {
        $this->date = clone $date;
    }

    /**
     * @deprecated Use Factory::build instead
     * @param $period
     * @param $date
     * @return Period
     */
    public static function factory($period, $date)
    {
        return PeriodFactory::build($period, $date);
    }

    /**
     * Returns true if `$dateString` and `$period` represent multiple periods.
     *
     * Will return true for date/period combinations where date references multiple
     * dates and period is not `'range'`. For example, will return true for:
     *
     * - **date** = `2012-01-01,2012-02-01` and **period** = `'day'`
     * - **date** = `2012-01-01,2012-02-01` and **period** = `'week'`
     * - **date** = `last7` and **period** = `'month'`
     *
     * etc.
     *
     * @static
     * @param  $dateString string The **date** query parameter value.
     * @param  $period string The **period** query parameter value.
     * @return boolean
     */
    public static function isMultiplePeriod($dateString, $period)
    {
        return is_string($dateString)
            && (preg_match('/^(last|previous){1}([0-9]*)$/D', $dateString, $regs)
                || Range::parseDateRange($dateString))
            && $period != 'range';
    }

    /**
     * Returns the first day of the period.
     *
     * @return Date
     */
    public function getDateStart()
    {
        $this->generate();

        if (count($this->subperiods) == 0) {
            return $this->getDate();
        }

        $periods = $this->getSubperiods();

        /** @var $currentPeriod Period */
        $currentPeriod = $periods[0];
        while ($currentPeriod->getNumberOfSubperiods() > 0) {
            $periods       = $currentPeriod->getSubperiods();
            $currentPeriod = $periods[0];
        }

        return $currentPeriod->getDate();
    }

    /**
     * Returns the last day of the period.
     *
     * @return Date
     */
    public function getDateEnd()
    {
        $this->generate();

        if (count($this->subperiods) == 0) {
            return $this->getDate();
        }

        $periods = $this->getSubperiods();

        /** @var $currentPeriod Period */
        $currentPeriod = $periods[count($periods) - 1];
        while ($currentPeriod->getNumberOfSubperiods() > 0) {
            $periods       = $currentPeriod->getSubperiods();
            $currentPeriod = $periods[count($periods) - 1];
        }

        return $currentPeriod->getDate();
    }

    /**
     * Returns the period ID.
     *
     * @return int A unique integer for this type of period.
     */
    public function getId()
    {
        return Piwik::$idPeriods[$this->getLabel()];
    }

    /**
     * Returns the label for the current period.
     *
     * @return string `"day"`, `"week"`, `"month"`, `"year"`, `"range"`
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return Date
     */
    protected function getDate()
    {
        return $this->date;
    }

    protected function generate()
    {
        $this->subperiodsProcessed = true;
    }

    /**
     * Returns the number of available subperiods.
     *
     * @return int
     */
    public function getNumberOfSubperiods()
    {
        $this->generate();
        return count($this->subperiods);
    }

    /**
     * Returns the set of Period instances that together make up this period. For a year,
     * this would be 12 months. For a month this would be 28-31 days. Etc.
     *
     * @return Period[]
     */
    public function getSubperiods()
    {
        $this->generate();
        return $this->subperiods;
    }

    /**
     * Add a date to the period.
     *
     * Protected because adding periods after initialization is not supported.
     *
     * @param \Piwik\Period $period Valid Period object
     * @ignore
     */
    protected function addSubperiod($period)
    {
        $this->subperiods[] = $period;
    }

    /**
     * Returns a list of strings representing the current period.
     *
     * @param string $format The format of each individual day.
     * @return array An array of string dates that this period consists of.
     */
    public function toString($format = "Y-m-d")
    {
        $this->generate();

        $dateString = array();
        foreach ($this->subperiods as $period) {
            $dateString[] = $period->toString($format);
        }

        return $dateString;
    }

    /**
     * See {@link toString()}.
     *
     * @return string
     */
    public function __toString()
    {
        return implode(",", $this->toString());
    }

    /**
     * Returns a pretty string describing this period.
     *
     * @return string
     */
    abstract public function getPrettyString();

    /**
     * Returns a short string description of this period that is localized with the currently used
     * language.
     *
     * @return string
     */
    abstract public function getLocalizedShortString();

    /**
     * Returns a long string description of this period that is localized with the currently used
     * language.
     *
     * @return string
     */
    abstract public function getLocalizedLongString();

    /**
     * Returns a succinct string describing this period.
     *
     * @return string eg, `'2012-01-01,2012-01-31'`.
     */
    public function getRangeString()
    {
        $dateStart = $this->getDateStart();
        $dateEnd   = $this->getDateEnd();

        return $dateStart->toString("Y-m-d") . "," . $dateEnd->toString("Y-m-d");
    }
}
