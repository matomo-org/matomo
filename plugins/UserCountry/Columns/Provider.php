<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\UserCountry\Columns;

use Piwik\Plugin\Manager;
use Piwik\Plugins\UserCountry\LocationProvider;
use Piwik\Tracker\Visitor;
use Piwik\Tracker\Action;
use Piwik\Tracker\Request;

class Provider extends Base
{
    protected $columnName = 'location_provider';

    /**
     * @param Request $request
     * @param Visitor $visitor
     * @param Action|null $action
     * @return mixed
     */
    public function onNewVisit(Request $request, Visitor $visitor, $action)
    {
        if (!Manager::getInstance()->isPluginInstalled('Provider')) {
            return false;
        }

        $userInfo = $this->getUserInfo($request, $visitor);

        $isp = $this->getLocationDetail($userInfo, LocationProvider::ISP_KEY);
        $org = $this->getLocationDetail($userInfo, LocationProvider::ORG_KEY);

        // if the location has provider/organization info, set it
        if (!empty($isp)) {
            $providerValue = $isp;

            // if the org is set and not the same as the isp, add it to the provider value
            if (!empty($org) && $org != $providerValue) {
                $providerValue .= ' - ' . $org;
            }

            return $providerValue;
        }

        if (!empty($org)) {
            return $org;
        }

        return false;
    }
}