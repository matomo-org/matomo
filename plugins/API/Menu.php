<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\API;

use Piwik\Menu\MenuTop;
use Piwik\Piwik;

/**
 */
class Menu extends \Piwik\Plugin\Menu
{

    public function configureTopMenu(MenuTop $menu)
    {
        $apiUrlParams = array('module' => 'API', 'action' => 'listAllAPI', 'segment' => false);
        $tooltip      = Piwik::translate('API_TopLinkTooltip');

        $menu->add('General_API', null, $apiUrlParams, true, 7, $tooltip);

        $this->addTopMenuMobileApp($menu);
    }

    protected function addTopMenuMobileApp(MenuTop $menu)
    {
        if (empty($_SERVER['HTTP_USER_AGENT'])) {
            return;
        }

        if (!class_exists("DeviceDetector")) {
            throw new \Exception("DeviceDetector could not be found, maybe you are using Piwik from git and need to have update Composer. <br>php composer.phar update");
        }

        $ua = new \DeviceDetector($_SERVER['HTTP_USER_AGENT']);
        $ua->parse();
        $os = $ua->getOs('short_name');
        if ($os && in_array($os, array('AND', 'IOS'))) {
            $menu->add('Piwik Mobile App', null, array('module' => 'Proxy', 'action' => 'redirect', 'url' => 'http://piwik.org/mobile/'), true, 4);
        }
    }

}
