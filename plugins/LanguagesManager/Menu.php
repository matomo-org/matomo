<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\LanguagesManager;

use Piwik\Development;
use Piwik\Menu\MenuAdmin;
use Piwik\Menu\MenuTop;
use Piwik\Piwik;
use Piwik\SettingsPiwik;

class Menu extends \Piwik\Plugin\Menu
{
    public function configureTopMenu(MenuTop $menu)
    {
        if (Piwik::isUserIsAnonymous() || !SettingsPiwik::isPiwikInstalled()) {
            $langManager = new LanguagesManager();
            $menu->addHtml('LanguageSelector', $langManager->getLanguagesSelector(), true, $order = 30, false);
        }
    }

    public function configureAdminMenu(MenuAdmin $menu)
    {
        if (Development::isEnabled() && Piwik::isUserHasSomeAdminAccess()) {
            $menu->addDevelopmentItem('LanguagesManager_TranslationSearch',
                                      $this->urlForAction('searchTranslation'));
        }
    }
}
