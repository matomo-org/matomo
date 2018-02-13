<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Validators;

class NotEmpty extends BaseValidator
{
    public function getHtmlAttributes()
    {
        return array(
            'required' => 'required'
        );
    }

    public function validate($value)
    {
        if (empty($value)) {
            throw new Exception('General_ValidatorErrorEmptyValue');
        }
    }
}