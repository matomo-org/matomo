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
 * Validator that fails for a value PHP's `empty()` treats as empty, which includes `0`, `'0'` and an empty array.
 *
 * @api
 */
class NotEmpty extends BaseValidator
{
    /**
     * Validates the given value and throws an exception if it is invalid.
     *
     * @param mixed $value The value to validate.
     */
    public function validate($value)
    {
        if (empty($value)) {
            throw new Exception(Piwik::translate('General_ValidatorErrorEmptyValue'));
        }
    }
}
