<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Period;

use Piwik\Date;
use Piwik\Period;

/**
 */
class Month extends Period
{
    const PERIOD_ID = 3;

    protected $label = 'month';

    /**
     * Returns the current period as a localized short string
     *
     * @return string
     */
    public function getLocalizedShortString()
    {
        //"Aug 09"
        $out = $this->getDateStart()->getLocalized(Date::DATE_FORMAT_MONTH_SHORT);
        return $out;
    }

    /**
     * Returns the current period as a localized long string
     *
     * @return string
     */
    public function getLocalizedLongString()
    {
        //"August 2009"
        $out = $this->getDateStart()->getLocalized(Date::DATE_FORMAT_MONTH_LONG);
        return $out;
    }

    /**
     * Returns the current period as a string
     *
     * @return string
     */
    public function getPrettyString()
    {
        $out = $this->getDateStart()->toString('Y-m');
        return $out;
    }

    /**
     * Generates the subperiods (one for each day in the month)
     */
    protected function generate()
    {
        if ($this->subperiodsProcessed) {
            return;
        }

        parent::generate();

        $date = $this->date;

        $startMonth = $date->setDay(1)->setTime('00:00:00');
        $endMonth   = $startMonth->addPeriod(1, 'month')->setDay(1)->subDay(1);

        $this->processOptimalSubperiods($startMonth, $endMonth);
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
            $week        = new Week($startDate);
            $startOfWeek = $week->getDateStart();
            $endOfWeek   = $week->getDateEnd();

            if ($endOfWeek->isLater($endDate)) {
                $this->fillDayPeriods($startDate, $endDate);
            } elseif ($startOfWeek == $startDate) {
                $this->addSubperiod($week);
            } else {
                $this->fillDayPeriods($startDate, $endOfWeek);
            }

            $startDate = $endOfWeek->addDay(1);
        }
    }

    /**
     * Fills the periods from startDate to endDate with days
     *
     * @param Date $startDate
     * @param Date $endDate
     */
    private function fillDayPeriods($startDate, $endDate)
    {
        while (($startDate->isEarlier($endDate) || $startDate == $endDate)) {
            $this->addSubperiod(new Day($startDate));
            $startDate = $startDate->addDay(1);
        }
    }

    public function getImmediateChildPeriodLabel()
    {
        return 'week';
    }

    public function getParentPeriodLabel()
    {
        return 'year';
    }
}
