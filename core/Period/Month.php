<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Period;

use Piwik\Period;
use Piwik\Piwik;

/**
 */
class Month extends Period
{
    protected $label = 'month';

    /**
     * Returns the current period as a localized short string
     *
     * @return string
     */
    public function getLocalizedShortString()
    {
        //"Aug 09"
        $out = $this->getDateStart()->getLocalized(Piwik::translate('CoreHome_ShortMonthFormat'));
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
        $out = $this->getDateStart()->getLocalized(Piwik::translate('CoreHome_LongMonthFormat'));
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

        $startMonth = $date->setDay(1);
        $currentDay = clone $startMonth;
        while ($currentDay->compareMonth($startMonth) == 0) {
            $this->addSubperiod(new Day($currentDay));
            $currentDay = $currentDay->addDay(1);
        }
    }
}
