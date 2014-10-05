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
class Week extends Period
{
    protected $label = 'week';

    /**
     * Returns the current period as a localized short string
     *
     * @return string
     */
    public function getLocalizedShortString()
    {
        //"30 Dec - 6 Jan 09"
        $dateStart = $this->getDateStart();
        $dateEnd   = $this->getDateEnd();

        $string = Piwik::translate('CoreHome_ShortWeekFormat');
        $string = self::getTranslatedRange($string, $dateStart, $dateEnd);
        return $string;
    }

    /**
     * Returns the current period as a localized long string
     *
     * @return string
     */
    public function getLocalizedLongString()
    {
        $format = Piwik::translate('CoreHome_LongWeekFormat');
        $string = self::getTranslatedRange($format, $this->getDateStart(), $this->getDateEnd());

        return Piwik::translate('CoreHome_PeriodWeek') . " " . $string;
    }

    /**
     * @param string $format
     * @param \Piwik\Date $dateStart
     * @param \Piwik\Date $dateEnd
     *
     * @return mixed
     */
    protected static function getTranslatedRange($format, $dateStart, $dateEnd)
    {
        $string = str_replace('From%', '%', $format);
        $string = $dateStart->getLocalized($string);
        $string = str_replace('To%', '%', $string);
        $string = $dateEnd->getLocalized($string);

        return $string;
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

        $out = Piwik::translate('General_DateRangeFromTo', array($dateStart->toString(), $dateEnd->toString()));

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
}
