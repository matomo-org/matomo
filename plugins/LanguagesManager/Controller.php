<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 *
 */

namespace Piwik\Plugins\LanguagesManager;

use Piwik\Common;
use Piwik\Nonce;
use Piwik\Piwik;
use Piwik\Url;

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

        Nonce::checkNonce(LanguagesManager::LANGUAGE_SELECTION_NONCE, $nonce);

        LanguagesManager::setLanguageForSession($language);

        $referrer = Url::getReferrer();

        if (false !== $referrer) {
            // a language in the URL wins over the session, so keeping it would send the user
            // back to the language they just replaced
            Url::redirectToUrl(self::withoutLanguageParameter($referrer));
        }

        Url::redirectToUrl(Url::getCurrentUrlWithoutQueryString());
    }

    private static function withoutLanguageParameter(string $url): string
    {
        // the hash is split off first, since it carries a query string of its own
        [$beforeHash, $hash] = array_pad(explode('#', $url, 2), 2, null);
        [$path, $query] = array_pad(explode('?', $beforeHash, 2), 2, null);

        if (null === $query) {
            return $url;
        }

        $query = ltrim(preg_replace('/(^|&)language=[^&]*/', '', $query), '&');

        return $path
            . ('' === $query ? '' : '?' . $query)
            . (null === $hash ? '' : '#' . $hash);
    }

    public function searchTranslation()
    {
        Piwik::checkUserHasSomeAdminAccess();

        return $this->renderTemplate('searchTranslation');
    }
}
