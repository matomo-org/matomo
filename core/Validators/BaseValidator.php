<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Validators;

/**
 * Base class for validators. Validators are used for example in the `validators` of a setting field
 * or in {@link BaseValidator::check()}.
 *
 * Whether an empty value (`false`, `null`, `''`) passes differs per validator and is stated on each one; add {@link NotEmpty} to require a value.
 *
 * @api
 */
abstract class BaseValidator
{
    /**
     * The method to validate a value. If the value has not an expected format, an instance of
     * {@link Piwik\Validators\Exception} should be thrown.
     *
     * @param mixed $value The value to validate.
     * @throws Exception
     */
    abstract public function validate($value);

    protected function isValueBare($value)
    {
        // we allow this value. if it is supposed to be not empty, please use NotEmpty validator on top
        return $value === false || $value === null || $value === '';
    }

    /**
     * Lets you easily check a value against multiple validators.
     *
     * @param string $name The name/description of the field you want to validate the value for.
     *                     The name will be prefixed in case there is any error.
     * @param mixed $value The value which needs to be tested
     * @param BaseValidator[] $validators The validators to apply, in order.
     */
    public static function check($name, $value, $validators)
    {
        foreach ($validators as $validator) {
            try {
                $validator->validate($value);
            } catch (\Exception $e) {
                throw new Exception(strip_tags($name) . ': ' . $e->getMessage(), $e->getCode());
            }
        }
    }
}
