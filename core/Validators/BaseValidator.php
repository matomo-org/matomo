<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Validators;

abstract class BaseValidator
{

    public function validate($value)
    {

    }

    /**
     * @param string $name The name/description of the field you want to validate the value for.
     *                     The name will be prefixed in case there is any error.
     * @param mixed $value The value which needs to be tested
     * @param BaseValidator[] $validators
     */
    public static function check($name, $value, $validators)
    {
        foreach ($validators as $validator) {
            try {
                $validator->validate($value);
            } catch (\Exception $e) {
                throw new Exception($name . ': ' . $e->getMessage(), $e->getCode(), $e);
            }
        }
    }
}