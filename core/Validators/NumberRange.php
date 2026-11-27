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
 * Validator that fails for a non-numeric value or a number outside the optional minimum and maximum. Empty values (`false`, `null`, `''`) pass.
 *
 * @api
 */
class NumberRange extends BaseValidator
{
    /** Maximum value of an unsigned SMALLINT database column. */
    public const MAX_SMALL_INT_UNSIGNED = 65535;
    /** Maximum value of an unsigned MEDIUMINT database column. */
    public const MAX_MEDIUM_INT_UNSIGNED = 16777215;

    /**
     * @var int|float|null
     */
    private $min;

    /**
     * @var int|float|null
     */
    private $max;

    /**
     * @param int|float|null $min The minimum allowed value, or null for no minimum.
     * @param int|float|null $max The maximum allowed value, or null for no maximum.
     */
    public function __construct($min = null, $max = null)
    {
        $this->min = $min;
        $this->max = $max;
    }

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
        if (!is_numeric($value)) {
            throw new Exception(Piwik::translate('General_ValidatorErrorNotANumber'));
        }

        if (isset($this->min) && $this->min > $value) {
            throw new Exception(Piwik::translate('General_ValidatorErrorNumberTooLow', array($value, $this->min)));
        }

        if (isset($this->max) && $this->max < $value) {
            throw new Exception(Piwik::translate('General_ValidatorErrorNumberTooHigh', array($value, $this->max)));
        }
    }
}
