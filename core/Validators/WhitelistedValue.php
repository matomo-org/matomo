<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Validators;

use Piwik\Piwik;

/**
 * Validator that fails for a value that is not one of the allowed values. Values are compared strictly, so an empty value fails unless it is allowed.
 *
 * @api
 */
class WhitelistedValue extends BaseValidator
{
    private $whitelisted = array();

    /**
     * @param array $whitelistedValues The allowed values.
     */
    public function __construct($whitelistedValues)
    {
        if (!is_array($whitelistedValues)) {
            throw new Exception('The whitelisted values need to be an array');
        }
        $this->whitelisted = $whitelistedValues;
    }

    /**
     * Validates the given value and throws an exception if it is invalid.
     *
     * @param mixed $value The value to validate.
     */
    public function validate($value)
    {
        if (!in_array($value, $this->whitelisted, true)) {
            throw new Exception(Piwik::translate('General_ValidatorErrorXNotWhitelisted', array($value, implode(', ', $this->whitelisted))));
        }
    }
}
