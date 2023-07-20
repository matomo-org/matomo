<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 *
 */
namespace Piwik\Plugins\LanguagesManager;

use Piwik\Common;
use Piwik\DbHelper;
use Piwik\Nonce;
use Piwik\Piwik;
use Piwik\Url;
use Piwik\Updater as PiwikCoreUpdater;

/**
 */
class Controller extends \Piwik\Plugin\ControllerAdmin
{
    /**
     * anonymous = in the session
     * authenticated user = in the session
     */
    public function saveLanguage()
    {
        $language = Common::getRequestVar('language');
        $nonce = Common::getRequestVar('nonce', '');

        // Check if is an update in progress
        $updater = new PiwikCoreUpdater();
        $updating = ($updater->getComponentUpdates() !== null);

        // Prevent CSRF if Matomo is not installed yet or is updating
        // (During install/update user can change language)
        if (DbHelper::isInstalled() && !$updating) {
            $this->checkTokenInUrl();
        }

        Nonce::checkNonce(LanguagesManager::LANGUAGE_SELECTION_NONCE, $nonce);

        LanguagesManager::setLanguageForSession($language);
        Url::redirectToReferrer();
    }

    public function searchTranslation()
    {
        Piwik::checkUserHasSomeAdminAccess();

        return $this->renderTemplate('searchTranslation');
    }
}
