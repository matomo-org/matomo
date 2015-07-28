<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Updates;

use Piwik\Option;
use Piwik\Plugins\SitesManager\API;
use Piwik\Plugins\SitesManager\Model;
use Piwik\Updater;
use Piwik\Updates;

class Updates_2_15_0_b1 extends Updates
{
    public function doUpdate(Updater $updater)
    {
        $this->reformatExcludedUserAgents();
    }

    /**
     * Excluded user agents are now separated by line returns instead of commas
     */
    private function reformatExcludedUserAgents()
    {
        $globalExcludedUserAgents = Option::get(API::OPTION_EXCLUDED_USER_AGENTS_GLOBAL);
        $globalExcludedUserAgents = str_replace(',', "\n", $globalExcludedUserAgents);
        Option::set(API::OPTION_EXCLUDED_USER_AGENTS_GLOBAL, $globalExcludedUserAgents);

        $model = new Model();

        $sites = API::getInstance()->getAllSites();
        foreach ($sites as $site) {
            $site['excluded_user_agents'] = str_replace(',', "\n", $site['excluded_user_agents']);
            $model->updateSite($site, $site['idsite']);
        }
    }
}
