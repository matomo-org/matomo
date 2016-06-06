<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik;

use Piwik\Container\StaticContainer;
use Piwik\Period\Factory;
use Piwik\Period\Range;
use Piwik\Translation\Translator;

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
     * @var Period[]
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
     * @var Translator
     */
    protected $translator;

    /**
     * Constructor.
     *
     * @param Date $date
     * @ignore
     */
    public function __construct(Date $date)
    {
        $this->date = clone $date;

        $this->translator = StaticContainer::get('Piwik\Translation\Translator');
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
     * Checks the given date format whether it is a correct date format and if not, throw an exception.
     *
     * For valid date formats have a look at the {@link \Piwik\Date::factory()} method and
     * {@link isMultiplePeriod()} method.
     *
     * @param string $dateString
     * @throws \Exception If `$dateString` is in an invalid format or if the time is before
     *                   Tue, 06 Aug 1991.
     */
    public static function checkDateFormat($dateString)
    {
        if (self::isMultiplePeriod($dateString, 'day')) {
            return;
        }

        Date::factory($dateString);
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
     * Returns the label of the period type that is one size smaller than this one, or null if
     * it's the smallest.
     *
     * Range periods and other such 'period collections' are not considered as separate from
     * the value type of the collection. So a range period will return the result of the
     * subperiod's `getImmediateChildPeriodLabel()` method.
     *
     * @ignore
     * @return string|null
     */
    abstract public function getImmediateChildPeriodLabel();

    /**
     * Returns the label of the period type that is one size bigger than this one, or null
     * if it's the biggest.
     *
     * Range periods and other such 'period collections' are not considered as separate from
     * the value type of the collection. So a range period will return the result of the
     * subperiod's `getParentPeriodLabel()` method.
     *
     * @ignore
     */
    abstract public function getParentPeriodLabel();

    /**
     * Returns the date range string comprising two dates
     *
     * @return string eg, `'2012-01-01,2012-01-31'`.
     */
    public function getRangeString()
    {
        $dateStart = $this->getDateStart();
        $dateEnd   = $this->getDateEnd();

        return $dateStart->toString("Y-m-d") . "," . $dateEnd->toString("Y-m-d");
    }

    /**
     * @param string $format
     *
     * @return mixed
     */
    protected function getTranslatedRange($format)
    {
        $dateStart = $this->getDateStart();
        $dateEnd = $this->getDateEnd();
        list($formatStart, $formatEnd) = $this->explodeFormat($format);

        $string = $dateStart->getLocalized($formatStart);
        $string .= $dateEnd->getLocalized($formatEnd);

        return $string;
    }

    /**
     * Explodes the given format into two pieces. One that can be user for start date and the other for end date
     *
     * @param $format
     * @return array
     */
    protected function explodeFormat($format)
    {
        $intervalTokens = array(
            array('d', 'E', 'C'),
            array('M', 'L'),
            array('y')
        );

        $offset = strlen($format);
        // replace string literals encapsulated by ' with same country of *
        $cleanedFormat = preg_replace_callback('/(\'[^\']+\')/', array($this, 'replaceWithStars'), $format);

        // search for first duplicate date field
        foreach ($intervalTokens AS $tokens) {
            if (preg_match_all('/[' . implode('|', $tokens) . ']+/', $cleanedFormat, $matches, PREG_OFFSET_CAPTURE) &&
                count($matches[0]) > 1 && $offset > $matches[0][1][1]
            ) {
                $offset = $matches[0][1][1];
            }
        }

        return array(substr($format, 0, $offset), substr($format, $offset));
    }

    private function replaceWithStars($matches)
    {
        return str_repeat("*", strlen($matches[0]));
    }

    protected function getRangeFormat($short = false)
    {
        $maxDifference = 'D';
        if ($this->getDateStart()->toString('y') != $this->getDateEnd()->toString('y')) {
            $maxDifference = 'Y';
        } elseif ($this->getDateStart()->toString('m') != $this->getDateEnd()->toString('m')) {
            $maxDifference = 'M';
        }

        $dateTimeFormatProvider = StaticContainer::get('Piwik\Intl\Data\Provider\DateTimeFormatProvider');

        return $dateTimeFormatProvider->getRangeFormatPattern($short, $maxDifference);
    }

    /**
     * Returns all child periods that exist within this periods entire date range. Cascades
     * downwards over all period types that are smaller than this one. For example, month periods
     * will cascade to week and day periods and year periods will cascade to month, week and day
     * periods.
     *
     * The method will not return periods that are outside the range of this period.
     *
     * @return Period[]
     * @ignore
     */
    public function getAllOverlappingChildPeriods()
    {
        return $this->getAllOverlappingChildPeriodsInRange($this->getDateStart(), $this->getDateEnd());
    }

    private function getAllOverlappingChildPeriodsInRange(Date $dateStart, Date $dateEnd)
    {
        $result = array();

        $childPeriodType = $this->getImmediateChildPeriodLabel();
        if (empty($childPeriodType)) {
            return $result;
        }

        $childPeriods = Factory::build($childPeriodType, $dateStart->toString() . ',' . $dateEnd->toString());
        return array_merge($childPeriods->getSubperiods(), $childPeriods->getAllOverlappingChildPeriodsInRange($dateStart, $dateEnd));
    }
}
