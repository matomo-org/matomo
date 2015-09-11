<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CustomVariables\Columns;

use Piwik\Piwik;

class CustomVariableValue extends Base
{
    protected function configureSegments()
    {
        $this->configureSegmentsFor('custom_var_v', 'Value');
    }

    public function getName()
    {
        return Piwik::translate('CustomVariables_ColumnCustomVariableValue');
    }
}