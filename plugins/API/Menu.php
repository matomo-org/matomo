<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\API;

use Piwik\Container\StaticContainer;
use Piwik\Menu\MenuAdmin;
use Piwik\Menu\MenuTop;
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

    public function configureAdminMenu(MenuAdmin $menu)
    {
        $menu->addPlatformItem('General_API',
            $this->urlForAction('listAllAPI', array('segment' => false)),
            7,
            Piwik::translate('API_TopLinkTooltip')
        );

        if(Piwik::isUserIsAnonymous()) {
            $menu->addPlatformItem('API_Glossary',
                $this->urlForAction('glossary', array('segment' => false)),
                50
            );
        }
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
        $ua->setCache(StaticContainer::get('DeviceDetector\Cache\Cache'));
        $parsedOS = $ua->parse();

        if (!empty($parsedOS['short_name']) && in_array($parsedOS['short_name'], array(self::DD_SHORT_NAME_ANDROID, self::DD_SHORT_NAME_IOS))) {
            $menu->addItem('Mobile_MatomoMobile', null, 'https://matomo.org/mobile/', 4);
        }
    }

}
