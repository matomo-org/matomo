<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\IntranetMeasurable\Tracker;

use Piwik\Container\StaticContainer;
use Piwik\Exception\UnexpectedWebsiteFoundException;
use Piwik\Plugins\IntranetMeasurable\Type;
use Piwik\Tracker\Cache;
use Piwik\Tracker\Request;

class RequestProcessor extends \Piwik\Tracker\RequestProcessor
{
    private $didEnableSetting = false;
    private $settingName = 'ini.Tracker.trust_visitors_cookies';

    public function manipulateRequest(Request $request)
    {
        try {
            $site = Cache::getCacheWebsiteAttributes($request->getIdSite());
        } catch (UnexpectedWebsiteFoundException $e) {
            return;
        }
        $isIntranetSite = !empty($site['type']) && $site['type'] === Type::ID;

        if ($isIntranetSite && !StaticContainer::get($this->settingName)) {
            $this->setTrustCookiesSetting(1);
            $this->didEnableSetting = true;
        } elseif ($this->didEnableSetting) {
            // we reset it in case of bulk tracking with different sites etc
            $this->setTrustCookiesSetting(0);
            $this->didEnableSetting = false;
        }
    }

    private function setTrustCookiesSetting($value)
    {
        StaticContainer::get('Piwik\Tracker\VisitorRecognizer')->setTrustCookiesOnly($value);
        StaticContainer::getContainer()->set($this->settingName, $value);
    }
}
