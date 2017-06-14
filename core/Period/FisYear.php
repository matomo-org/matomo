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
use Piwik\Config;

/**
 */
class FisYear extends Period
{
    const PERIOD_ID = 6;

    protected $label = 'fisyear';

    /**
     * Returns the current period as a localized short string
     *
     * @return string
     */
    public function getLocalizedShortString()
    {
        return $this->getLocalizedLongString();
    }

    /**
     * Returns the current period as a localized long string
     *
     * @return string
     */
    public function getLocalizedLongString()
    {
        //"2016-2017"
        $out = $this->getDateStart()->getLocalized(Date::DATE_FORMAT_YEAR) . '-' . $this->getDateEnd()->getLocalized(Date::DATE_FORMAT_YEAR);
        return $out;
    }

    /**
     * Returns the current period as a string
     *
     * @return string
     */
    public function getPrettyString()
    {
        //"2016-2017"
        $out = $this->getDateStart()->toString('Y'). '-' . $this->getDateEnd()->toString('Y');
        return $out;
    }

    /**
     * Generates the subperiods (one for each month of the financial year)
     * Also adjusts the financial year in function of what is needed
     * (ex. 2016 or 2017 in fiscal year 2016-2017)
     *
     */
    protected function generate()
    {
        if ($this->subperiodsProcessed) {
            return;
        }

        parent::generate();

        $startMonth = Config::getInstance()->General['fiscal_year_start_month'];
        $year = $this->date->toString("Y");

        //If the month of the date is lower then the starting month,
        //we are in the previous fiscal year
        if ($this->date->toString("m") < $startMonth) {
            $year -= 1;
        }
        
        for ($i = $startMonth; $i < ($startMonth + 12); $i++) {
            if ($i < 13) {
                $this->addSubperiod(new Month(
                        Date::factory("$year-$i-01")
                    )
                );
            } else {
                $this->addSubperiod(new Month(
                        Date::factory(strval($year + 1) . "-" . strval($i - 12) . "-01")
                    )
                );                
            }
        }
    }
    /**
     * Returns the current period as a string
     *
     * @param string $format
     * @return array
     */
    public function toString($format = 'ignored')
    {
        $this->generate();

        $stringMonth = array();
        foreach ($this->subperiods as $month) {
            $stringMonth[] = $month->getDateStart()->toString("Y") . "-" . $month->getDateStart()->toString("m") . "-01";
        }

        return $stringMonth;
    }

    public function getImmediateChildPeriodLabel()
    {
        return 'month';
    }

    public function getParentPeriodLabel()
    {
        return null;
    }
}
