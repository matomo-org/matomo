<?php
/**
 * Piwik - Open source web analytics
 *
 * @link     http://piwik.org
 * @license  http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\Dashboard;

use Piwik\View\UIControl;

/**
 * Generates the HTML for the dashboard manager control.
 */
class DashboardSettingsControl extends UIControl
{
    const TEMPLATE = "@Dashboard/_dashboardSettings";

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->jsClass = "DashboardSettingsControl";
        $this->cssIdentifier = "dashboardSettings";
        $this->cssClass = "js-autoLeftPanel";
    }
}