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

        // Prevent CSRF only when piwik is not installed yet (During install user can change language)
        if (DbHelper::isInstalled()) {
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
