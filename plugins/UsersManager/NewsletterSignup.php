<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link http://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\UsersManager;

use Exception;
use Piwik\Container\StaticContainer;
use Piwik\Http;
use Piwik\Option;
use Piwik\Plugins\Marketplace\Api\Client;
use Piwik\SettingsPiwik;

class NewsletterSignup
{
    const NEWSLETTER_SIGNUP_OPTION = 'UsersManager.newsletterSignup.';

    public static function signupForNewsletter($userLogin, $email, $matomoOrg = false, $professionalServices = false)
    {
        // Don't bother if they aren't signing up for at least one newsletter, or if we don't have internet access
        $doSignup = ($matomoOrg || $professionalServices) && SettingsPiwik::isInternetEnabled();
        if (!$doSignup) {
            return false;
        }

        $url = Client::getApiServiceUrl();
        $url .= '/1.0/subscribeNewsletter/';

        $params = array(
            'email'     => $email,
            'piwikorg'  => (int)$matomoOrg,
            'piwikpro'  => (int)$professionalServices,
            'language'  => StaticContainer::get('Piwik\Translation\Translator')->getCurrentLanguage(),
        );

        $url .= '?' . Http::buildQuery($params);
        try {
            Http::sendHttpRequest($url, $timeout = 2);
            $optionKey = self::NEWSLETTER_SIGNUP_OPTION . $userLogin;
            Option::set($optionKey, 1);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
