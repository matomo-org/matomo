<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\API;

use Piwik\DeviceDetectorCache;
use Piwik\Menu\MenuTop;
use Piwik\Menu\MenuUser;
use Piwik\Piwik;
use DeviceDetector\Parser\OperatingSystem;

class Menu extends \Piwik\Plugin\Menu
{
    const DD_SHORT_NAME_ANDROID = 'AND';
    const DD_SHORT_NAME_IOS     = 'IOS';

    public function configureTopMenu(MenuTop $menu)
    {
        $this->addTopMenuMobileApp($menu);
    }

    public function configureUserMenu(MenuUser $menu)
    {
        $apiUrlParams = $this->urlForAction('listAllAPI', array('segment' => false));
        $tooltip      = Piwik::translate('API_TopLinkTooltip');

        $menu->addPlatformItem('General_API', $apiUrlParams, 6, $tooltip);
    }

    private function addTopMenuMobileApp(MenuTop $menu)
    {
        if (empty($_SERVER['HTTP_USER_AGENT'])) {
            return;
        }

        if (!class_exists("DeviceDetector\\DeviceDetector")) {
            throw new \Exception("DeviceDetector could not be found, maybe you are using Piwik from git and need to update Composer. Execute this command: php composer.phar update");
        }

        $ua = new OperatingSystem($_SERVER['HTTP_USER_AGENT']);
        $ua->setCache(new DeviceDetectorCache('tracker', 86400));
        $parsedOS = $ua->parse();

        if (!empty($parsedOS['short_name']) && in_array($parsedOS['short_name'], array(self::DD_SHORT_NAME_ANDROID, self::DD_SHORT_NAME_IOS))) {

            $url = $this->urlForModuleAction('Proxy', 'redirect', array('url' => 'http://piwik.org/mobile/'));

            if ($url) {
                $menu->addItem('Piwik Mobile App', null, $url, 4);
            }
        }
    }

}
