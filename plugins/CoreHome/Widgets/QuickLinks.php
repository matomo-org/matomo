<?php


namespace Piwik\Plugins\CoreHome\Widgets;


use Piwik\Piwik;
use Piwik\Plugins\SitesManager\SitesManager;
use Piwik\Plugins\UsersManager\UsersManager;
use Piwik\Widget\Widget;
use Piwik\Widget\WidgetConfig;

class QuickLinks extends Widget
{
    public static function configure(WidgetConfig $config)
    {
        $config->setCategoryId('About Matomo');
        $config->setName('CoreHome_QuickLinks');
        $config->setOrder(16);
        $config->setIsEnabled(Piwik::hasUserSuperUserAccess());
    }

    public function render()
    {
        $hasUsersAdmin = UsersManager::isUsersAdminEnabled();
        $hasSitesAdmin = SitesManager::isSitesAdminEnabled();

        return $this->renderTemplate('quickLinks', array(
            'hasUsersAdmin' => $hasUsersAdmin,
            'hasSitesAdmin' => $hasSitesAdmin,
        ));
    }

}