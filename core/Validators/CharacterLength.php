<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Validators;

use Piwik\Common;
use Piwik\Piwik;

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
     * @param null|int $min
     * @param null|int $max
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

    public function getHtmlAttributes()
    {
        if (!isset($this->min) && !isset($this->max)) {
            return array();
        }

        if (!isset($this->min) && $this->max) {
            return array(
                'maxlength' => $this->max
            );
        }

        $pattern = '.{' . $this->min . ',';
        if (isset($this->max)) {
            $pattern .= $this->max;
        }
        $pattern .= '}';

        return array(
            'pattern' => $pattern
        );
    }

    public function validate($value)
    {
        if (is_array($value) || is_object($value) || is_resource($value)) {
            return;
        }

        $lenValue = Common::mb_strlen($value);

        if (isset($this->min) && $this->min > $lenValue) {
            throw new Exception(Piwik::translate('General_ValidatorErrorCharacterTooShort', array($lenValue, $this->min)));
        }

        if (isset($this->max) && $this->max < $lenValue) {
            throw new Exception(Piwik::translate('General_ValidatorErrorCharacterTooLong', array($lenValue, $this->max)));
        }

    }
}