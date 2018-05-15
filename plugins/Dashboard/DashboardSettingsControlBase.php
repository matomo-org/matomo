<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link     http://piwik.org
 * @license  http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\Dashboard;

use Piwik\View\UIControl;

/**
 * Generates the HTML for the dashboard manager control.
 */
abstract class DashboardSettingsControlBase extends UIControl
{
    const TEMPLATE = "@Dashboard/_dashboardSettings";

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->cssClass = "borderedControl piwikTopControl dashboardSettings";
        $this->htmlAttributes = array('piwik-expand-on-click' => '');
        $this->dashboardActions = array();
        $this->generalActions = array();
    }
}