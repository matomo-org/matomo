<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link     https://matomo.org
 * @license  http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\Dashboard;

use Piwik\Piwik;

/**
 * Generates the HTML for the dashboard manager control.
 */
class DashboardManagerControl extends DashboardSettingsControlBase
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->jsClass = "DashboardManagerControl";
        $this->cssIdentifier = "dashboard-manager piwikSelector";

        $this->addDashboardActions();
        $this->addGeneralActions();
    }

    private function addDashboardActions()
    {
        $this->dashboardActions['resetDashboard'] = 'Dashboard_ResetDashboard';
        $this->dashboardActions['showChangeDashboardLayoutDialog'] = 'Dashboard_ChangeDashboardLayout';

        if ($this->userLogin && $this->userLogin != 'anonymous') {
            $this->dashboardActions['renameDashboard'] = 'Dashboard_RenameDashboard';
            $this->dashboardActions['removeDashboard'] = 'Dashboard_RemoveDashboard';

            if ($this->isSuperUser) {
                $this->dashboardActions['setAsDefaultWidgets'] = 'Dashboard_SetAsDefaultWidgets';
            }
            if (Piwik::isUserHasSomeAdminAccess()) {
                $this->dashboardActions['copyDashboardToUser'] = 'Dashboard_CopyDashboardToUser';
            }
        }
    }

    private function addGeneralActions()
    {
        if ($this->userLogin && $this->userLogin != 'anonymous') {
            $this->generalActions['createDashboard'] = 'Dashboard_CreateNewDashboard';
        }
    }
}