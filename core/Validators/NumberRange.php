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

class NumberRange extends BaseValidator
{
    const MAX_SMALL_INT_UNSIGNED = 65535;
    const MAX_MEDIUM_INT_UNSIGNED = 16777215;

    /**
     * @var null|int
     */
    private $min;

    /**
     * @var null|int
     */
    private $max;

    /**
     * @param null|int $min
     * @param null|int $max
     */
    public function __construct($min = null, $max = null)
    {
        $this->min = $min;
        $this->max = $max;
    }

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