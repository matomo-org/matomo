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
use Piwik\Translation\Translator;

/**
 * Class NumberFormatter
 *
 * Used to format numbers according to current language
 */
class NumberFormatter
{
    /** @var string language specific patterns for numbers */
    protected $patternNumber;

    /** @var string language specific pattern for percent numbers */
    protected $patternPercent;

    /** @var string language specific pattern for currency numbers */
    protected $patternCurrency;

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
     * Loads all required data from Intl plugin
     *
     * TODO: instead of going directly through Translator, there should be a specific class
     * that gets needed characters (ie, NumberFormatSource). The default implementation
     * can use the Translator. This will make it easier to unit test NumberFormatter,
     * w/o needing the Piwik Environment.
     *
     * @return NumberFormatter
     */
    public function __construct(Translator $translator)
    {
        $this->patternNumber = $translator->translate('Intl_NumberFormatNumber');
        $this->patternCurrency = $translator->translate('Intl_NumberFormatCurrency');
        $this->patternPercent = $translator->translate('Intl_NumberFormatPercent');
        $this->symbolPlus = $translator->translate('Intl_NumberSymbolPlus');
        $this->symbolMinus = $translator->translate('Intl_NumberSymbolMinus');
        $this->symbolPercent = $translator->translate('Intl_NumberSymbolPercent');
        $this->symbolGroup = $translator->translate('Intl_NumberSymbolGroup');
        $this->symbolDecimal = $translator->translate('Intl_NumberSymbolDecimal');
    }

    /**
     * Parses the given pattern and returns patterns for positive and negative numbers
     *
     * @param string $pattern
     * @return array
     */
    protected function parsePattern($pattern)
    {
        $patterns = explode(';', $pattern);
        if (!isset($patterns[1])) {
            // No explicit negative pattern was provided, construct it.
            $patterns[1] = '-' . $patterns[0];
        }
        return $patterns;
    }

    /**
     * Formats a given number or percent value (if $value starts or ends with a %)
     *
     * @param string|int|float $value
     * @param int $maximumFractionDigits
     * @param int $minimumFractionDigits
     * @return mixed|string
     */
    public function format($value, $maximumFractionDigits=0, $minimumFractionDigits=0)
    {
        if (is_string($value)
            && trim($value, '%') != $value
        ) {
            return $this->formatPercent($value, $maximumFractionDigits, $minimumFractionDigits);
        }

        return $this->formatNumber($value, $maximumFractionDigits, $minimumFractionDigits);
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

        static $positivePattern, $negativePattern;

        if (empty($positivePatter) || empty($negativePattern)) {
            list($positivePattern, $negativePattern) = $this->parsePattern($this->patternNumber);
        }
        $negative = $this->isNegative($value);
        $pattern = $negative ? $negativePattern : $positivePattern;

        return $this->formatNumberWithPattern($pattern, $value, $maximumFractionDigits, $minimumFractionDigits);
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
        static $positivePattern, $negativePattern;

        if (empty($positivePatter) || empty($negativePattern)) {
            list($positivePattern, $negativePattern) = $this->parsePattern($this->patternPercent);
        }

        $newValue =  trim($value, " \0\x0B%");
        if (!is_numeric($newValue)) {
            return $value;
        }

        $negative = $this->isNegative($value);
        $pattern = $negative ? $negativePattern : $positivePattern;

        return $this->formatNumberWithPattern($pattern, $newValue, $maximumFractionDigits, $minimumFractionDigits);
    }


    /**
     * Formats given number as percent value, but keep the leading + sign if found
     *
     * @param $value
     * @return string
     */
    public function formatPercentEvolution($value)
    {
        $isPositiveEvolution = !empty($value) && ($value > 0 || $value[0] == '+');

        $formatted = self::formatPercent($value);

        if($isPositiveEvolution) {
            return $this->symbolPlus . $formatted;
        }
        return $formatted;
    }

    /**
     * Formats given number as percent value
     * @param string|int|float $value
     * @param string $currency
     * @param int $precision
     * @return mixed|string
     */
    public function formatCurrency($value, $currency, $precision=2)
    {
        static $positivePattern, $negativePattern;

        if (empty($positivePatter) || empty($negativePattern)) {
            list($positivePattern, $negativePattern) = $this->parsePattern($this->patternCurrency);
        }

        $newValue =  trim($value, " \0\x0B$currency");
        if (!is_numeric($newValue)) {
            return $value;
        }

        $negative = $this->isNegative($value);
        $pattern = $negative ? $negativePattern : $positivePattern;

        if ($newValue == round($newValue)) {
            // if no fraction digits available, don't show any
            $value = $this->formatNumberWithPattern($pattern, $newValue, 0, 0);
        } else {
            // show given count of fraction digits otherwise
            $value = $this->formatNumberWithPattern($pattern, $newValue, $precision, $precision);
        }

        return str_replace('¤', $currency, $value);
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
        if (!is_numeric($value)) {
            return $value;
        }

        $this->usesGrouping = (strpos($pattern, ',') !== false);
        // if pattern has number groups, parse them.
        if ($this->usesGrouping) {
            preg_match('/#+0/', $pattern, $primaryGroupMatches);
            $this->primaryGroupSize = $this->secondaryGroupSize = strlen($primaryGroupMatches[0]);
            $numberGroups = explode(',', $pattern);
            // check for distinct secondary group size.
            if (count($numberGroups) > 2) {
                $this->secondaryGroupSize = strlen($numberGroups[1]);
            }
        }

        // Ensure that the value is positive and has the right number of digits.
        $negative = $this->isNegative($value);
        $signMultiplier = $negative ? '-1' : '1';
        $value = $value / $signMultiplier;
        $value = round($value, $maximumFractionDigits);
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
            $groups = array();
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
        $replacements = array(
            '.' => $this->symbolDecimal,
            ',' => $this->symbolGroup,
            '+' => $this->symbolPlus,
            '-' => $this->symbolMinus,
            '%' => $this->symbolPercent,
        );
        return strtr($value, $replacements);
    }

    /**
     * @param $value
     * @return bool
     */
    protected function isNegative($value)
    {
        return $value < 0;
    }

    /**
     * @deprecated
     * @return self
     */
    public static function getInstance()
    {
        return StaticContainer::get('Piwik\NumberFormatter');
    }
}