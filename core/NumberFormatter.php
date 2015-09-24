<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik;

use Piwik\Container\StaticContainer;

/**
 * Class NumberFormatter
 *
 * Used to format numbers according to current language
 */
class NumberFormatter extends Singleton
{
    /** @var string language specific pattern for positive numbers */
    protected $patternPositive;

    /** @var string language specific pattern for negative numbers */
    protected $patternNegative;

    /** @var string language specific pattern for percent numbers */
    protected $patternPercent;

    /** @var string language specific plus sign */
    protected $symbolPlus;

    /** @var string language specific minus sign */
    protected $symbolMinus;

    /** @var string language specific percent sign */
    protected $symbolPercent;

    /** @var string language specific symbol used as decimal separator */
    protected $symbolDecimal;

    /** @var string language specific symbol used as group separator */
    protected $symbolGroup;

    /** @var bool indicates if language uses grouping for numbers */
    protected $usesGrouping;

    /** @var int language specific size for primary group numbers */
    protected $primaryGroupSize;

    /** @var int language specific size for secondary group numbers */
    protected $secondaryGroupSize;

    /**
     * @return NumberFormatter
     */
    public static function getInstance()
    {
        return StaticContainer::get('Piwik\NumberFormatter');
    }

    /**
     * Loads all required data from Intl plugin
     */
    public function __construct()
    {
        $this->patternPositive = Piwik::translate('Intl_NumberFormat');
        $this->patternNegative = Piwik::translate('Intl_NumberFormatNegative');
        $this->patternPercent = Piwik::translate('Intl_NumberFormatPercent');
        $this->symbolPlus = Piwik::translate('Intl_NumberSymbolPlus');
        $this->symbolMinus = Piwik::translate('Intl_NumberSymbolMinus');
        $this->symbolPercent = Piwik::translate('Intl_NumberSymbolPercent');
        $this->symbolGroup = Piwik::translate('Intl_NumberSymbolGroup');
        $this->symbolDecimal = Piwik::translate('Intl_NumberSymbolDecimal');

        $this->usesGrouping = (strpos($this->patternPositive, ',') !== false);
        // if pattern has number groups, parse them.
        if ($this->usesGrouping) {
            preg_match('/#+0/', $this->patternPositive, $primaryGroupMatches);
            $this->primaryGroupSize = $this->secondaryGroupSize = strlen($primaryGroupMatches[0]);
            $numberGroups = explode(',', $this->patternPositive);
            // check for distinct secondary group size.
            if (count($numberGroups) > 2) {
                $this->secondaryGroupSize = strlen($numberGroups[1]);
            }
        }
    }

    /**
     * Formats a given number
     *
     * @param string|int|float $value
     * @param int $maximumFractionDigits
     * @param int $minimumFractionDigits
     * @return mixed|string
     */
    public function format($value, $maximumFractionDigits=0, $minimumFractionDigits=0)
    {
        $negative = (bccomp('0', $value, 12) == 1);
        $pattern = $negative ? $this->patternNegative : $this->patternPositive;
        return $this->formatNumberWithPattern($pattern, $value, $maximumFractionDigits, $minimumFractionDigits);
    }

    /**
     * Formats a given number
     *
     * @see \Piwik\NumberFormatter::format()
     *
     * @param string|int|float $value
     * @param int $maximumFractionDigits
     * @param int $minimumFractionDigits
     * @return mixed|string
     */
    public function formatNumber($value, $maximumFractionDigits=0, $minimumFractionDigits=0)
    {
        return $this->format($value, $maximumFractionDigits, $minimumFractionDigits);
    }

    /**
     * Formats given number as percent value
     * @param string|int|float $value
     * @param int $maximumFractionDigits
     * @param int $minimumFractionDigits
     * @return mixed|string
     */
    public function formatPercent($value, $maximumFractionDigits=0, $minimumFractionDigits=0)
    {
        $newValue =  trim($value, " \0\x0B%");
        if (!is_numeric($newValue)) {
            return $value;
        }
        return $this->formatNumberWithPattern($this->patternPercent, $newValue, $maximumFractionDigits, $minimumFractionDigits);
    }

    /**
     * Formats the given number with the given pattern
     *
     * @param string $pattern
     * @param string|int|float $value
     * @param int $maximumFractionDigits
     * @param int $minimumFractionDigits
     * @return mixed|string
     */
    protected function formatNumberWithPattern($pattern, $value, $maximumFractionDigits=0, $minimumFractionDigits=0)
    {
        $orig = $value;
        if (!is_numeric($value)) {
            return $value;
        }
        // Ensure that the value is positive and has the right number of digits.
        $negative = (bccomp('0', $value, 12) == 1);
        $signMultiplier = $negative ? '-1' : '1';
        $value = bcdiv($value, $signMultiplier, $maximumFractionDigits);
        // Split the number into major and minor digits.
        $valueParts = explode('.', $value);
        $majorDigits = $valueParts[0];
        // Account for maximumFractionDigits = 0, where the number won't
        // have a decimal point, and $valueParts[1] won't be set.
        $minorDigits = isset($valueParts[1]) ? $valueParts[1] : '';
        if ($this->usesGrouping) {
            // Reverse the major digits, since they are grouped from the right.
            $majorDigits = array_reverse(str_split($majorDigits));
            // Group the major digits.
            $groups = [];
            $groups[] = array_splice($majorDigits, 0, $this->primaryGroupSize);
            while (!empty($majorDigits)) {
                $groups[] = array_splice($majorDigits, 0, $this->secondaryGroupSize);
            }
            // Reverse the groups and the digits inside of them.
            $groups = array_reverse($groups);
            foreach ($groups as &$group) {
                $group = implode(array_reverse($group));
            }
            // Reconstruct the major digits.
            $majorDigits = implode(',', $groups);
        }
        if ($minimumFractionDigits < $maximumFractionDigits) {
            // Strip any trailing zeroes.
            $minorDigits = rtrim($minorDigits, '0');
            if (strlen($minorDigits) < $minimumFractionDigits) {
                // Now there are too few digits, re-add trailing zeroes
                // until the desired length is reached.
                $neededZeroes = $minimumFractionDigits - strlen($minorDigits);
                $minorDigits .= str_repeat('0', $neededZeroes);
            }
        }
        // Assemble the final number and insert it into the pattern.
        $value = $minorDigits ? $majorDigits . '.' . $minorDigits : $majorDigits;
        $value = preg_replace('/#(?:[\.,]#+)*0(?:[,\.][0#]+)*/', $value, $pattern);
        // Localize the number.
        $value = $this->replaceSymbols($value);
        return $value;
    }


    /**
     * Replaces number symbols with their localized equivalents.
     *
     * @param string $value The value being formatted.
     *
     * @return string
     *
     * @see http://cldr.unicode.org/translation/number-symbols
     */
    protected function replaceSymbols($value)
    {
        $replacements = [
            '.' => $this->symbolDecimal,
            ',' => $this->symbolGroup,
            '+' => $this->symbolPlus,
            '-' => $this->symbolMinus,
            '%' => $this->symbolPercent,
        ];
        return strtr($value, $replacements);
    }
}