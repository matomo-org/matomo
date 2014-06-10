<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CustomVariables;

use Piwik\Piwik;
use Piwik\View;

/**
 */
class Controller extends \Piwik\Plugin\Controller
{
    public function index()
    {
        return View::singleReport(
            Piwik::translate('CustomVariables_CustomVariables'),
            $this->getCustomVariables(true));
    }

    public function getCustomVariables()
    {
        return $this->renderReport(__FUNCTION__);
    }

    public function getCustomVariablesValuesFromNameId()
    {
        return $this->renderReport(__FUNCTION__);
    }
}

