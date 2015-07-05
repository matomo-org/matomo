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

        $format = $this->translator->translate('Intl_Format_Interval_Week_Short_'.$this->getMinDifference($dateStart, $dateEnd));
        $string = $this->getTranslatedRange($format, $dateStart, $dateEnd);
        return $string;
    }

    /**
     * Returns the current period as a localized long string
     *
     * @return string
     */
    public function getLocalizedLongString()
    {
        //"30 Dec - 6 Jan 09"
        $dateStart = $this->getDateStart();
        $dateEnd   = $this->getDateEnd();

        $format = $this->translator->translate('Intl_Format_Interval_Week_Long_'.$this->getMinDifference($dateStart, $dateEnd));
        $string = $this->getTranslatedRange($format, $dateStart, $dateEnd);

        return $this->translator->translate('Intl_PeriodWeek') . " " . $string;
    }

    protected function getMinDifference($dateFrom, $dateEnd)
    {
        if ($dateFrom->toString('y') != $dateEnd->toString('y')) {
            return 'Y';
        } elseif ($dateFrom->toString('m') != $dateEnd->toString('m')) {
            return 'M';
        }

        return 'D';
    }

    /**
     * @param string $format
     * @param \Piwik\Date $dateStart
     * @param \Piwik\Date $dateEnd
     *
     * @return mixed
     */
    protected function getTranslatedRange($format, $dateStart, $dateEnd)
    {
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

        // search for first duplicate date field
        foreach ($intervalTokens AS $tokens) {
            if (preg_match_all('/['.implode('|', $tokens).']+/', $format, $matches, PREG_OFFSET_CAPTURE)) {
                if (count($matches[0]) > 1 && $offset > $matches[0][1][1]) {
                    $offset = $matches[0][1][1];
                }
            }

        }

        return array(substr($format, 0, $offset), substr($format, $offset));
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
}
