<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\ExampleVisualization;

/**
 */
class ExampleVisualization extends \Piwik\Plugin
{
    /**
     * @see Piwik\Plugin::getListHooksRegistered
     */
    public function getListHooksRegistered()
    {
        return array(
            'ViewDataTable.addViewDataTable' => 'getAvailableVisualizations'
        );
    }

    public function getAvailableVisualizations(&$visualizations)
    {
        $visualizations[] = __NAMESPACE__ . '\\SimpleTable';
    }
}
