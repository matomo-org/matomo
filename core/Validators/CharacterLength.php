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
 * Validator that fails when a string or number has fewer characters than the minimum or more than the maximum. Values of other types, including `null` and `false`, are accepted.
 *
 * @api
 */
class CharacterLength extends BaseValidator
{
    /**
     * @var null|int
     */
    private $min;

    /**
     * @var null|int
     */
    private $max;

    /**
     * @param null|int $min The minimum number of characters, or null for no minimum.
     * @param null|int $max The maximum number of characters, or null for no maximum.
     */
    public function __construct($min = null, $max = null)
    {
        if (isset($min)) {
            $this->min = (int) $min;
        }
        if (isset($max)) {
            $this->max = (int) $max;
        }
    }

    /**
     * Validates the given value and throws an exception if it is invalid.
     *
     * @param mixed $value The value to validate.
     */
    public function validate($value)
    {
        if (!is_string($value) && !is_numeric($value)) {
            return;
        }

        $lenValue = mb_strlen($value);

        if (isset($this->min) && $this->min > $lenValue) {
            throw new Exception(Piwik::translate('General_ValidatorErrorCharacterTooShort', array($lenValue, $this->min)));
        }

        if (isset($this->max) && $this->max < $lenValue) {
            throw new Exception(Piwik::translate('General_ValidatorErrorCharacterTooLong', array($lenValue, $this->max)));
        }
    }
}
