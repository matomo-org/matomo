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
namespace Piwik\Log;

use Piwik\IP;
use Piwik\Log;

/**
 * Class used to format the API Call log on the screen.
 *
 * @package Piwik
 * @subpackage Log
 */
class APICallScreenFormatter extends ScreenFormatter
{
    /**
     * Formats data into a single line to be written by the writer.
     *
     * @param  array $event    event data
     * @return string  formatted line to write to the log
     */
    public function format($event)
    {
        $str = "\n<br /> ";
        $str .= "Called: {$event['class_name']}.{$event['method_name']} (took {$event['execution_time']}ms)\n<br /> ";
        $str .= "Parameters: ";
        $parameterNamesAndDefault = unserialize($event['parameter_names_default_values']);
        $parameterValues = unserialize($event['parameter_values']);
        $i = 0;
        foreach ($parameterNamesAndDefault as $pName => $pDefault) {
            if (isset($parameterValues[$i])) {
                $currentValue = $parameterValues[$i];
            } else {
                $currentValue = $pDefault;
            }
            $currentValue = $this->formatValue($currentValue);
            $str .= "$pName = $currentValue, ";

            $i++;
        }
        $str .= "\n<br /> ";
        $str .= "\n<br /> ";
        return parent::format($str);
    }

    /**
     * Converts the given value to a string
     *
     * @param mixed $value
     * @return string
     */
    private function formatValue($value)
    {
        if (is_string($value)) {
            $value = "'$value'";
        }
        if (is_null($value)) {
            $value = 'null';
        }
        if (is_array($value)) {
            $value = "array( " . implode(", ", $value) . ")";
        }
        return $value;
    }
}