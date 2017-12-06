<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CoreHome;

use Piwik\Common;
use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\Network\IP as NetworkIp;
use Piwik\NoAccessException;
use Piwik\Piwik;

/**
 * This class is in CoreHome since some alternative Login plugins disable the Login plugin and we want to ensure the
 * feature works for all login plugins.
 */
class LoginWhitelist
{
    public function shouldWhitelistApplyToAPI()
    {
        $general = $this->getGeneralConfig();
        return !empty($general['login_whitelist_apply_to_reporting_api_requests']);
    }

    public function shouldCheckWhitelist()
    {
        if (Common::isPhpCliMode()) {
            return false;
        }

        $ips = $this->getWhitelistedLoginIps();
        return !empty($ips);
    }

    public function checkIsWhitelisted($ipString)
    {
        if (!$this->isIpWhitelisted($ipString)) {
            throw new NoAccessException(Piwik::translate('CoreHome_ExceptionNotWhitelistedIP', $ipString));
        }
    }

    public function isIpWhitelisted($userIpString)
    {
        $userIp = NetworkIp::fromStringIP($userIpString);
        $ipsWhitelisted = $this->getWhitelistedLoginIps();

        if (empty($ipsWhitelisted)) {
            return false;
        }

        return $userIp->isInRanges($ipsWhitelisted);
    }

    /**
     * @return array
     */
    protected function getWhitelistedLoginIps()
    {
        $ips = StaticContainer::get('login.whitelist.ips');

        if (!empty($ips) && is_array($ips)) {
            $ips = array_map(function ($ip) {
                return trim($ip);
            }, $ips);
            $ips = array_filter($ips, function ($ip) {
                return !empty($ip);
            });
            return array_unique(array_values($ips));
        }

        return array();
    }

    private function getGeneralConfig()
    {
        $config = Config::getInstance();
        $general = $config->General;
        return $general;
    }
}
