<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Resolution;

use Piwik\Plugins\CoreVisualizations\Visualizations\Graph;
use Piwik\Plugins\CoreVisualizations\Visualizations\HtmlTable;

/**
 *
 */
class Resolution extends \Piwik\Plugin
{
    /**
     * @see Piwik\Plugin::registerEvents
     */
    public function registerEvents()
    {
        return array(
            'Live.getAllVisitorDetails'            => 'extendVisitorDetails',
            'Request.getRenamedModuleAndAction' => 'renameUserSettingsModuleAndAction',
        );
    }

    public function extendVisitorDetails(&$visitor, $details)
    {
        $instance = new Visitor($details);

        $visitor['resolution']               = $instance->getResolution();
    }

    public function renameUserSettingsModuleAndAction(&$module, &$action)
    {
        if ($module == 'UserSettings' && ($action == 'getResolution' || $action == 'getConfiguration')) {
            $module = 'Resolution';
        }
    }
}
