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
class Piwik_Period_Day extends Piwik_Period
{
    protected $label = 'day';

    /**
     * Returns the day of the period as a string
     *
     * @return string
     */
    public function getPrettyString()
    {
        $out = $this->getDateStart()->toString();
        return $out;
    }

    /**
     * Returns the day of the period as a localized short string
     *
     * @return string
     */
    public function getLocalizedShortString()
    {
        //"Mon 15 Aug"
        $date = $this->getDateStart();
        $out = $date->getLocalized(Piwik_Translate('CoreHome_ShortDateFormat'));
        return $out;
    }

    /**
     * Returns the day of the period as a localized long string
     *
     * @return string
     */
    public function getLocalizedLongString()
    {
        //"Mon 15 Aug"
        $date = $this->getDateStart();
        $template = Piwik_Translate('CoreHome_DateFormat');
        $out = $date->getLocalized($template);
        return $out;
    }

    /**
     * Returns the number of subperiods
     * Always 0, in that case
     *
     * @return int
     */
    public function getNumberOfSubperiods()
    {
        return 0;
    }

    /**
     * Adds a subperiod
     * Not supported for day periods
     *
     * @param $date
     * @throws Exception
     */
    public function addSubperiod($date)
    {
        throw new Exception("Adding a subperiod is not supported for Piwik_Period_Day");
    }

    /**
     * Returns the day of the period in the given format
     *
     * @param string $format
     * @return string
     */
    public function toString($format = "Y-m-d")
    {
        return $this->date->toString($format);
    }

    /**
     * Returns the current period as a string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }
}
