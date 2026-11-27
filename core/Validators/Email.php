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
 * Validator that fails for a value that is not a valid email address. Empty values (`false`, `null`, `''`) pass.
 *
 * @api
 */
class Email extends BaseValidator
{
    /**
     * Validates the given value and throws an exception if it is invalid.
     *
     * @param mixed $value The value to validate.
     */
    public function validate($value)
    {
        if ($this->isValueBare($value)) {
            return;
        }

        if (!Piwik::isValidEmailString($value)) {
            throw new Exception(Piwik::translate('General_ValidatorErrorNotEmailLike', array($value)));
        }
    }
}
