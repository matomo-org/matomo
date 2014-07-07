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
        $apiUrlParams = array('module' => 'API', 'action' => 'listAllAPI', 'segment' => false);
        $tooltip      = Piwik::translate('API_TopLinkTooltip');

        $menu->add('CorePluginsAdmin_MenuPlatform', 'General_API', $apiUrlParams, true, 6, $tooltip);
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
            $menu->add('Piwik Mobile App', null, array('module' => 'Proxy', 'action' => 'redirect', 'url' => 'http://piwik.org/mobile/'), true, 4);
        }
    }

}
