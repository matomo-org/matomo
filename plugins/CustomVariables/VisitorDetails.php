<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CustomVariables;

use Piwik\Plugins\Live\VisitorDetailsAbstract;

class VisitorDetails extends VisitorDetailsAbstract
{
    public function extendVisitorDetails(&$visitor)
    {
        $customVariables = array();

        $maxCustomVariables = CustomVariables::getNumUsableCustomVariables();

        for ($i = 1; $i <= $maxCustomVariables; $i++) {
            if (!empty($this->details['custom_var_k' . $i])) {
                $customVariables[$i] = array(
                    'customVariableName' . $i  => $this->details['custom_var_k' . $i],
                    'customVariableValue' . $i => $this->details['custom_var_v' . $i],
                );
            }
        }

        $visitor['customVariables'] = $customVariables;
    }
}