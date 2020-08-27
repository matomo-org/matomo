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

class WhitelistedValue extends BaseValidator
{
    private $whitelisted = array();

    /**
     * @param array $whitelistedValues
     */
    public function __construct($whitelistedValues)
    {
        if (!is_array($whitelistedValues)) {
            throw new Exception('The whitelisted values need to be an array');
        }
        $this->whitelisted = $whitelistedValues;
    }

    public function validate($value)
    {
        if (!in_array($value, $this->whitelisted, true)) {
            throw new Exception(Piwik::translate('General_ValidatorErrorXNotWhitelisted', array($value, implode(', ', $this->whitelisted))));
        }

    }
}