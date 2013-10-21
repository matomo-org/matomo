<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package CustomVariables
 */
namespace Piwik\Plugins\CustomVariables;

use Piwik\Piwik;
use Piwik\View;
use Piwik\ViewDataTable\Factory;

/**
 * @package CustomVariables
 */
class Controller extends \Piwik\Plugin\Controller
{
    public function index($fetch = false)
    {
        return View::singleReport(
            Piwik::translate('CustomVariables_CustomVariables'),
            $this->getCustomVariables(true), $fetch);
    }

    public function getCustomVariables($fetch = false)
    {
        return Factory::renderReport($this->pluginName, __FUNCTION__, $fetch);
    }

    public function getCustomVariablesValuesFromNameId($fetch = false)
    {
        return Factory::renderReport($this->pluginName, __FUNCTION__, $fetch);
    }
}

