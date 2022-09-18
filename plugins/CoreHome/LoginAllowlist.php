<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CoreHome;

use Piwik\Common;
use Piwik\Config;
use Piwik\Container\StaticContainer;
use Matomo\Network\IP as NetworkIp;
use Piwik\NoAccessException;
use Piwik\Piwik;
use Piwik\SettingsServer;

/**
 * This class is in CoreHome since some alternative Login plugins disable the Login plugin and we want to ensure the
 * feature works for all login plugins.
 */
class LoginAllowlist
{
    public function shouldAllowlistApplyToAPI()
    {
        $general = $this->getGeneralConfig();
        return !empty($general['login_allowlist_apply_to_reporting_api_requests']) || !empty($general['login_whitelist_apply_to_reporting_api_requests']);
    }

    public function shouldCheckAllowlist()
    {
        if (Common::isPhpCliMode()) {
            return false;
        }

        // ignore whitelist checks for opt out iframe or opt out JS
        if (!SettingsServer::isTrackerApiRequest()
            && (('CoreAdminHome' === Piwik::getModule() && ('optOut' === Piwik::getAction() || 'optOutJS' === Piwik::getAction())))
            )
        {
            return false;
        }

        $ips = $this->getAllowlistedLoginIps();
        return !empty($ips);
    }

    public function checkIsAllowed($ipString)
    {
        if (!$this->isIpAllowed($ipString)) {
            throw new NoAccessException(Piwik::translate('CoreHome_ExceptionNotAllowlistedIP', $ipString));
        }
    }

    public function isIpAllowed($userIpString)
    {
        $userIp = NetworkIp::fromStringIP($userIpString);
        $ipsAllowed = $this->getAllowlistedLoginIps();

        if (empty($ipsAllowed)) {
            return false;
        }

        return $userIp->isInRanges($ipsAllowed);
    }

    /**
     * @return array
     */
    protected function getAllowlistedLoginIps()
    {
        $ips = StaticContainer::get('login.allowlist.ips');

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
