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
use Piwik\Cache;
use Piwik\Common;
use Piwik\Container\StaticContainer;
use Piwik\Date;
use Piwik\Period;
use Piwik\Piwik;

/**
 * Arbitrary date range representation.
 *
 * This class represents a period that contains a list of consecutive days as subperiods
 * It is created when the **period** query parameter is set to **range** and is used
 * to calculate the subperiods of multiple period requests (eg, when period=day and
 * date=2007-07-24,2013-11-15).
 *
 * The range period differs from other periods mainly in that since it is arbitrary,
 * range periods are not pre-archived by the cron core:archive command.
 *
 * @api
 */
class Range extends Period
{
    const PERIOD_ID = 5;

    protected $label = 'range';
    protected $today;

    /**
     * @var null|Date
     */
    protected $defaultEndDate;

    /**
     * Constructor.
     *
     * @param string $strPeriod The type of period each subperiod is. Either `'day'`, `'week'`,
     *                          `'month'` or `'year'`.
     * @param string $strDate The date range, eg, `'2007-07-24,2013-11-15'`.
     * @param string $timezone The timezone to use, eg, `'UTC'`.
     * @param bool|Date $today The date to use as _today_. Defaults to `Date::factory('today', $timzeone)`.
     * @api
     */
    public function __construct($strPeriod, $strDate, $timezone = 'UTC', $today = false)
    {
        $this->strPeriod = $strPeriod;
        $this->strDate   = $strDate;
        $this->timezone  = $timezone;
        $this->defaultEndDate = null;

        if ($today === false) {
            $today = Date::factory('now', $this->timezone);
        }

        $this->today = $today;

        $this->translator = StaticContainer::get('Piwik\Translation\Translator');
    }

    private function getCache()
    {
        return Cache::getTransientCache();
    }

    private function getCacheId()
    {
        $end = '';
        if ($this->defaultEndDate) {
            $end = $this->defaultEndDate->getTimestamp();
        }

        $today = $this->today->getTimestamp();

        return 'range' . $this->strPeriod . $this->strDate . $this->timezone . $end . $today;
    }

    private function loadAllFromCache()
    {
        $range = $this->getCache()->fetch($this->getCacheId());

        if (!empty($range)) {
            foreach ($range as $key => $val) {
                $this->$key = $val;
            }
        }
    }

    private function cacheAll()
    {
        $props = get_object_vars($this);

        $this->getCache()->save($this->getCacheId(), $props);
    }

    /**
     * Returns the current period as a localized short string.
     *
     * @return string
     */
    public function getLocalizedShortString()
    {
        return $this->getTranslatedRange($this->getRangeFormat(true));
    }

    /**
     * Returns the current period as a localized long string.
     *
     * @return string
     */
    public function getLocalizedLongString()
    {
        return $this->getTranslatedRange($this->getRangeFormat());
    }

    /**
     * Returns the start date of the period.
     *
     * @return Date
     * @throws Exception
     */
    public function getDateStart()
    {
        $dateStart = parent::getDateStart();

        if (empty($dateStart)) {
            throw new Exception("Specified date range is invalid.");
        }

        return $dateStart;
    }

    /**
     * Returns the current period as a string.
     *
     * @return string
     */
    public function getPrettyString()
    {
        $out = $this->translator->translate('General_DateRangeFromTo', array($this->getDateStart()->toString(), $this->getDateEnd()->toString()));
        return $out;
    }

    protected function getMaxN($lastN)
    {
        switch ($this->strPeriod) {
            case 'day':
                $lastN = min($lastN, 5 * 365);
                break;

            case 'week':
                $lastN = min($lastN, 10 * 52);
                break;

            case 'month':
                $lastN = min($lastN, 10 * 12);
                break;

            case 'year':
                $lastN = min($lastN, 10);
                break;
        }
        return $lastN;
    }

    /**
     * Sets the default end date of the period.
     *
     * @param Date $oDate
     */
    public function setDefaultEndDate(Date $oDate)
    {
        $this->defaultEndDate = $oDate;
    }

    /**
     * Generates the subperiods
     *
     * @throws Exception
     */
    protected function generate()
    {
        if ($this->subperiodsProcessed) {
            return;
        }

        $this->loadAllFromCache();

        if ($this->subperiodsProcessed) {
            return;
        }

        parent::generate();

        if (preg_match('/(last|previous)([0-9]*)/', $this->strDate, $regs)) {
            $lastN = $regs[2];
            $lastOrPrevious = $regs[1];
            if (!is_null($this->defaultEndDate)) {
                $defaultEndDate = $this->defaultEndDate;
            } else {
                $defaultEndDate = $this->today;
            }

            $period = $this->strPeriod;
            if ($period == 'range') {
                $period = 'day';
            }

            if ($lastOrPrevious == 'last') {
                $endDate = $defaultEndDate;
            } elseif ($lastOrPrevious == 'previous') {
                if ('month' == $period) {
                    $endDate = $defaultEndDate->subMonth(1);
                } else {
                    $endDate = $defaultEndDate->subPeriod(1, $period);
                }
            }

            $lastN = $this->getMaxN($lastN);

            // last1 means only one result ; last2 means 2 results so we remove only 1 to the days/weeks/etc
            $lastN--;
            if ($lastN < 0) {
                $lastN = 0;
            }

            $startDate = $endDate->addPeriod(-1 * $lastN, $period);
        } elseif ($dateRange = Range::parseDateRange($this->strDate)) {
            $strDateStart = $dateRange[1];
            $strDateEnd = $dateRange[2];
            $startDate = Date::factory($strDateStart);

            // we set the timezone in the Date object only if the date is relative eg. 'today', 'yesterday', 'now'
            $timezone = null;
            if (strpos($strDateEnd, '-') === false) {
                $timezone = $this->timezone;
            }
            $endDate = Date::factory($strDateEnd, $timezone);
        } else {
            throw new Exception($this->translator->translate('General_ExceptionInvalidDateRange', array($this->strDate, ' \'lastN\', \'previousN\', \'YYYY-MM-DD,YYYY-MM-DD\'')));
        }

        if ($this->strPeriod != 'range') {
            $this->fillArraySubPeriods($startDate, $endDate, $this->strPeriod);
            $this->cacheAll();
            return;
        }

        $this->processOptimalSubperiods($startDate, $endDate);
        // When period=range, we want End Date to be the actual specified end date,
        // rather than the end of the month / week / whatever is used for processing this range
        $this->endDate = $endDate;
        $this->cacheAll();
    }

    /**
     * Given a date string, returns `false` if not a date range,
     * or returns the array containing start and end dates.
     *
     * @param string $dateString
     * @return mixed  array(1 => dateStartString, 2 => dateEndString) or `false` if the input was not a date range.
     */
    public static function parseDateRange($dateString)
    {
        $matched = preg_match('/^([0-9]{4}-[0-9]{1,2}-[0-9]{1,2}),(([0-9]{4}-[0-9]{1,2}-[0-9]{1,2})|today|now|yesterday)$/D', trim($dateString), $regs);

        if (empty($matched)) {
            return false;
        }

        return $regs;
    }

    protected $endDate = null;

    /**
     * Returns the end date of the period.
     *
     * @return null|Date
     */
    public function getDateEnd()
    {
        if (!is_null($this->endDate)) {
            return $this->endDate;
        }

        return parent::getDateEnd();
    }

    /**
     * Determine which kind of period is best to use.
     * See Range.test.php
     *
     * @param Date $startDate
     * @param Date $endDate
     */
    protected function processOptimalSubperiods($startDate, $endDate)
    {
        while ($startDate->isEarlier($endDate)
            || $startDate == $endDate) {
            $endOfPeriod = null;

            $month        = new Month($startDate);
            $endOfMonth   = $month->getDateEnd();
            $startOfMonth = $month->getDateStart();

            $year        = new Year($startDate);
            $endOfYear   = $year->getDateEnd();
            $startOfYear = $year->getDateStart();

            if ($startDate == $startOfYear
                && ($endOfYear->isEarlier($endDate)
                    || $endOfYear == $endDate
                    || $endOfYear->isLater($this->today)
                )
                // We don't use the year if
                // the end day is in this year, is before today, and year not finished
                && !($endDate->isEarlier($this->today)
                    && $this->today->toString('Y') == $endOfYear->toString('Y')
                    && $this->today->compareYear($endOfYear) == 0)
            ) {
                $this->addSubperiod($year);
                $endOfPeriod = $endOfYear;
            } elseif ($startDate == $startOfMonth
                && ($endOfMonth->isEarlier($endDate)
                    || $endOfMonth == $endDate
                    || $endOfMonth->isLater($this->today)
                )
                // We don't use the month if
                // the end day is in this month, is before today, and month not finished
                && !($endDate->isEarlier($this->today)
                    && $this->today->toString('Y') == $endOfMonth->toString('Y')
                    && $this->today->compareMonth($endOfMonth) == 0)
            ) {
                $this->addSubperiod($month);
                $endOfPeriod = $endOfMonth;
            } else {
                // From start date,
                //  Process end of week
                $week        = new Week($startDate);
                $startOfWeek = $week->getDateStart();
                $endOfWeek   = $week->getDateEnd();

                $firstDayNextMonth      = $startDate->addPeriod(2, 'month')->setDay(1);
                $useMonthsNextIteration = $firstDayNextMonth->isEarlier($endDate);

                if ($useMonthsNextIteration
                    && $endOfWeek->isLater($endOfMonth)
                ) {
                    $this->fillArraySubPeriods($startDate, $endOfMonth, 'day');
                    $endOfPeriod = $endOfMonth;
                } //   If end of this week is later than end date, we use days
                elseif ($this->isEndOfWeekLaterThanEndDate($endDate, $endOfWeek) &&
                    ($endOfWeek->isEarlier($this->today)
                        || $startOfWeek->toString() != $startDate->toString()
                        || $endDate->isEarlier($this->today))
                ) {
                    $this->fillArraySubPeriods($startDate, $endDate, 'day');
                    break 1;
                } elseif ($startOfWeek->isEarlier($startDate)
                    && $endOfWeek->isEarlier($this->today)
                ) {
                    $this->fillArraySubPeriods($startDate, $endOfWeek, 'day');
                    $endOfPeriod = $endOfWeek;
                } else {
                    $this->addSubperiod($week);
                    $endOfPeriod = $endOfWeek;
                }
            }
            $startDate = $endOfPeriod->addDay(1);
        }
    }

    /**
     * Adds new subperiods
     *
     * @param Date $startDate
     * @param Date $endDate
     * @param string $period
     */
    protected function fillArraySubPeriods($startDate, $endDate, $period)
    {
        $arrayPeriods = array();
        $endSubperiod = Period\Factory::build($period, $endDate);
        $arrayPeriods[] = $endSubperiod;

        // set end date to start of end period since we're comparing against start date.
        $endDate = $endSubperiod->getDateStart();
        while ($endDate->isLater($startDate)) {
            $endDate = $endDate->addPeriod(-1, $period);
            $subPeriod = Period\Factory::build($period, $endDate);
            $arrayPeriods[] = $subPeriod;
        }
        $arrayPeriods = array_reverse($arrayPeriods);
        foreach ($arrayPeriods as $period) {
            $this->addSubperiod($period);
        }
    }

    /**
     * Returns the date that is one period before the supplied date.
     *
     * @param bool|string $date The date to get the last date of.
     * @param bool|string $period The period to use (either 'day', 'week', 'month', 'year');
     *
     * @return array An array with two elements, a string for the date before $date and
     *               a Period instance for the period before $date.
     * @api
     */
    public static function getLastDate($date = false, $period = false)
    {
        return self::getDateXPeriodsAgo(1, $date, $period);
    }

    /**
     * Returns the date that is X periods before the supplied date.
     *
     * @param bool|string $date The date to get the last date of.
     * @param bool|string $period The period to use (either 'day', 'week', 'month', 'year');
     * @param int         $subXPeriods How many periods in the past the date should be, for instance 1 or 7.
     *                    If sub period is 365 days and the current year is a leap year we assume you want to get the
     *                    day one year ago and change the value to 366 days therefore.
     *
     * @return array An array with two elements, a string for the date before $date and
     *               a Period instance for the period before $date.
     * @api
     */
    public static function getDateXPeriodsAgo($subXPeriods, $date = false, $period = false)
    {
        if ($date === false) {
            $date = Common::getRequestVar('date');
        }

        if ($period === false) {
            $period = Common::getRequestVar('period');
        }

        if (365 == $subXPeriods && 'day' == $period && Date::today()->isLeapYear()) {
            $subXPeriods = 366;
        }

        // can't get the last date for range periods & dates that use lastN/previousN
        $strLastDate = false;
        $lastPeriod  = false;
        if ($period != 'range' && !preg_match('/(last|previous)([0-9]*)/', $date, $regs)) {
            if (strpos($date, ',')) {
                // date in the form of 2011-01-01,2011-02-02

                $rangePeriod = new Range($period, $date);

                $lastStartDate = $rangePeriod->getDateStart()->subPeriod($subXPeriods, $period);
                $lastEndDate   = $rangePeriod->getDateEnd()->subPeriod($subXPeriods, $period);

                $strLastDate = "$lastStartDate,$lastEndDate";
            } else {
                $lastPeriod  = Date::factory($date)->subPeriod($subXPeriods, $period);
                $strLastDate = $lastPeriod->toString();
            }
        }

        return array($strLastDate, $lastPeriod);
    }

    /**
     * Returns a date range string given a period type, end date and number of periods
     * the range spans over.
     *
     * @param string $period The sub period type, `'day'`, `'week'`, `'month'` and `'year'`.
     * @param int $lastN The number of periods of type `$period` that the result range should
     *                   span.
     * @param string $endDate The desired end date of the range.
     * @param \Piwik\Site $site The site whose timezone should be used.
     * @return string The date range string, eg, `'2012-01-02,2013-01-02'`.
     * @api
     */
    public static function getRelativeToEndDate($period, $lastN, $endDate, $site)
    {
        $last30Relative = new Range($period, $lastN, $site->getTimezone());
        $last30Relative->setDefaultEndDate(Date::factory($endDate));
        $date = $last30Relative->getDateStart()->toString() . "," . $last30Relative->getDateEnd()->toString();

        return $date;
    }

    private function isEndOfWeekLaterThanEndDate($endDate, $endOfWeek)
    {
        $isEndOfWeekLaterThanEndDate = $endOfWeek->isLater($endDate);

        $isEndDateAlsoEndOfWeek      = ($endOfWeek->toString() == $endDate->toString());
        $isEndOfWeekLaterThanEndDate = ($isEndOfWeekLaterThanEndDate
            || ($isEndDateAlsoEndOfWeek
                && $endDate->isLater($this->today)));

        return $isEndOfWeekLaterThanEndDate;
    }

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

    public function getImmediateChildPeriodLabel()
    {
        $subperiods = $this->getSubperiods();
        return reset($subperiods)->getImmediateChildPeriodLabel();
    }

    public function getParentPeriodLabel()
    {
        return null;
    }
}
