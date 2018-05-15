<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Metrics\Formatter;

use Piwik\Metrics\Formatter;

/**
 * Metrics formatter that formats for HTML output. Uses non-breaking spaces in formatted values
 * so text will stay unbroken in HTML views.
 */
class Html extends Formatter
{
    public function getPrettyTimeFromSeconds($numberOfSeconds, $displayTimeAsSentence = true, $round = false)
    {
        $result = parent::getPrettyTimeFromSeconds($numberOfSeconds, $displayTimeAsSentence, $round);
        $result = $this->replaceSpaceWithNonBreakingSpace($result);
        return $result;
    }

    public function getPrettySizeFromBytes($size, $unit = null, $precision = 1)
    {
        $result = parent::getPrettySizeFromBytes($size, $unit, $precision);
        $result = $this->replaceSpaceWithNonBreakingSpace($result);
        return $result;
    }

    public function getPrettyMoney($value, $idSite)
    {
        $result = parent::getPrettyMoney($value, $idSite);
        $result = $this->replaceSpaceWithNonBreakingSpace($result);
        return $result;
    }

    private function replaceSpaceWithNonBreakingSpace($value)
    {
        return str_replace(' ', '&nbsp;', $value);
    }
}
