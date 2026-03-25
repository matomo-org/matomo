<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Period;

use Piwik\Date;
use Piwik\Period;

/**
 * Quarter period.
 *
 * A quarter represents a 3-month period:
 * - Q1: January - March
 * - Q2: April - June
 * - Q3: July - September
 * - Q4: October - December
 */
class Quarter extends Period
{
    public const PERIOD_ID = 6;

    protected $label = 'quarter';

    /**
     * Returns the quarter number (1-4) for this period
     *
     * @return int
     */
    public function getQuarterNumber(): int
    {
        $month = (int) $this->date->toString('n');
        return (int) ceil($month / 3);
    }

    /**
     * Returns the current period as a localized short string
     *
     * @return string
     */
    public function getLocalizedShortString()
    {
        // "Q1 2024"
        $quarterNum = $this->getQuarterNumber();
        $year = $this->getDateStart()->toString('Y');
        return "Q{$quarterNum} {$year}";
    }

    /**
     * Returns the current period as a localized long string
     *
     * @return string
     */
    public function getLocalizedLongString()
    {
        // "Q1 2024"
        return $this->getLocalizedShortString();
    }

    /**
     * Returns the current period as a string
     *
     * @return string
     */
    public function getPrettyString()
    {
        // "2024-Q1"
        $quarterNum = $this->getQuarterNumber();
        $year = $this->getDateStart()->toString('Y');
        return "{$year}-Q{$quarterNum}";
    }

    /**
     * Generates the subperiods (one for each month in the quarter)
     */
    protected function generate()
    {
        if ($this->subperiodsProcessed) {
            return;
        }

        parent::generate();

        $year = $this->date->toString('Y');
        $quarterNum = $this->getQuarterNumber();

        // Calculate start month: Q1=1, Q2=4, Q3=7, Q4=10
        $startMonth = (($quarterNum - 1) * 3) + 1;

        // Add 3 month subperiods
        for ($i = 0; $i < 3; $i++) {
            $monthNum = $startMonth + $i;
            $this->addSubperiod(new Month(
                Date::factory("{$year}-{$monthNum}-01")
            ));
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

        $stringMonth = [];
        foreach ($this->subperiods as $month) {
            $stringMonth[] = $month->getDateStart()->toString('Y-m') . '-01';
        }

        return $stringMonth;
    }

    public function getImmediateChildPeriodLabel()
    {
        return 'month';
    }

    public function getParentPeriodLabel()
    {
        return 'year';
    }
}
