<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Period;

use Piwik\Period;

/**
 */
class Week extends Period
{
    const PERIOD_ID = 2;

    protected $label = 'week';

    /**
     * Returns the current period as a localized short string
     *
     * @return string
     */
    public function getLocalizedShortString()
    {
        return $this->getTranslatedRange($this->getRangeFormat(true));
    }

    /**
     * Returns the current period as a localized long string
     *
     * @return string
     */
    public function getLocalizedLongString()
    {
        $string = $this->getTranslatedRange($this->getRangeFormat());
        return $this->translator->translate('Intl_PeriodWeek') . " " . $string;
    }

    /**
     * Returns the current period as a string
     *
     * @return string
     */
    public function getPrettyString()
    {
        $dateStart = $this->getDateStart();
        $dateEnd   = $this->getDateEnd();

        $out = $this->translator->translate('General_DateRangeFromTo', array($dateStart->toString(), $dateEnd->toString()));

        return $out;
    }

    /**
     * Generates the subperiods - one for each day in the week
     */
    protected function generate()
    {
        if ($this->subperiodsProcessed) {
            return;
        }

        parent::generate();
        $date = $this->date;

        if ($date->toString('N') > 1) {
            $date = $date->subDay($date->toString('N') - 1);
        }

        $startWeek = $date;

        $currentDay = clone $startWeek;
        while ($currentDay->compareWeek($startWeek) == 0) {
            $this->addSubperiod(new Day($currentDay));
            $currentDay = $currentDay->addDay(1);
        }
    }

    public function getImmediateChildPeriodLabel()
    {
        return 'day';
    }

    public function getParentPeriodLabel()
    {
        return 'month';
    }
}
