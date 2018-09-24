<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\IntranetMeasurable\Tracker;

use Piwik\Container\StaticContainer;
use Piwik\Plugins\IntranetMeasurable\Type;
use Piwik\Tracker\Cache;
use Piwik\Tracker\Request;

class RequestProcessor extends \Piwik\Tracker\RequestProcessor
{
    private $settingName = 'ini.Tracker.trust_visitors_cookies';

    public function manipulateRequest(Request $request)
    {
        $idSite = $request->getIdSite();
        if ($idSite && !StaticContainer::get($this->settingName)) {
            // we may need to enable it for an intranet site...
            $site = Cache::getCacheWebsiteAttributes($idSite);
            if (!empty($site['type'])
                && $site['type'] === Type::ID
                && !empty($site['enable_trust_visitors_cookies'])) {

                StaticContainer::get('Piwik\Tracker\VisitorRecognizer')->setTrustCookiesOnly(1);
                StaticContainer::getContainer()->set($this->settingName, 1);
            }
        }
    }
}
