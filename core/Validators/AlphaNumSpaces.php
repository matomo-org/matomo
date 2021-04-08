<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Validators;

use Piwik\Piwik;

class AlphaNumSpaces extends BaseValidator
{
    public function validate($value)
    {
        if ($this->isValueBare($value)) {
            return;
        }

        if (! is_string($value) && ! is_numeric($value)) {
            throw new Exception(Piwik::translate('General_ValidatorErrorNoValidAlphaNumSpaces', array($value)));
        }

        if (@preg_match('/^[\pL\pM\pN ]+$/u', $value) === 0) {
            throw new Exception(Piwik::translate('General_ValidatorErrorNoValidAlphaNumSpaces', array($value)));
        }
    }
}
