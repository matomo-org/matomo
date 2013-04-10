<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package Piwik
 */

/**
 * @package Piwik
 * @subpackage Piwik_Period
 */
class Piwik_Period_Week extends Piwik_Period
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
        $dateEnd = $this->getDateEnd();

        $string = Piwik_Translate('CoreHome_ShortWeekFormat');
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
        $format = Piwik_Translate('CoreHome_LongWeekFormat');
        $string = self::getTranslatedRange($format, $this->getDateStart(), $this->getDateEnd());
        return Piwik_Translate('CoreHome_PeriodWeek') . " " . $string;
    }

    static protected function getTranslatedRange($format, $dateStart, $dateEnd)
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
        $out = Piwik_Translate('General_DateRangeFromTo',
            array($this->getDateStart()->toString(),
                  $this->getDateEnd()->toString())
        );
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
            $this->addSubperiod(new Piwik_Period_Day($currentDay));
            $currentDay = $currentDay->addDay(1);
        }
    }
}
