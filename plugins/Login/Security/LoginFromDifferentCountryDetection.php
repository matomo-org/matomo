<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Login\Security;

use Piwik\Container\StaticContainer;
use Piwik\IP;
use Piwik\Piwik;
use Piwik\Plugins\Login\Emails\LoginFromDifferentCountryEmail;
use Piwik\Plugins\Login\Model;
use Piwik\Plugins\UserCountry\LocationProvider;
use Piwik\Plugins\UsersManager\Model as UsersModel;

class LoginFromDifferentCountryDetection
{
    /**
     * @var Model
     */
    private $model;

    /**
     * @var UsersModel
     */
    private $usersModel;

    public function __construct(Model $model, UsersModel $usersModel)
    {
        $this->model = $model;
        $this->usersModel = $usersModel;
    }

    public function isEnabled(): bool
    {
        // we need at least one GeoIP provider that is not the default or disabled one
        return $this->isGeoIPWorking();
    }

    private function isGeoIPWorking(): bool
    {
        $provider = LocationProvider::getCurrentProvider();

        return null !== $provider
            && $provider->canBeUsedForLocationBasedSecurityChecks()
            && $provider->isAvailable()
            && $provider->isWorking()
            && ($provider->getSupportedLocationInfo()[LocationProvider::COUNTRY_CODE_KEY] ?? false);
    }

    private function getLocation(): array
    {
        // since we checked whether the current provider is GeoIP,
        // we can directly use it here
        $provider = LocationProvider::getCurrentProvider();
        $location = $provider->getLocation([
            'ip' => IP::getIpFromHeader(),
            'disable_fallbacks' => true,
        ]) ?: [LocationProvider::COUNTRY_CODE_KEY => ''];

        return $location;
    }

    private function getCurrentLoginCountry(): string
    {
        return $this->getLocation()[LocationProvider::COUNTRY_CODE_KEY] ?? '';
    }

    public function check(string $login): void
    {
        $lastLoginCountry = $this->model->getLastLoginCountry($login);
        $currentLoginCountry = $this->getCurrentLoginCountry();

        $isDifferentCountries = $currentLoginCountry !== $lastLoginCountry;

        if ($isDifferentCountries) {
            if (null !== $lastLoginCountry) {
                // send email only if we had previous value
                $this->sendLoginFromDifferentCountryEmailToUser(
                    $login,
                    $currentLoginCountry,
                    IP::getIpFromHeader()
                );
            }

            // store new country
            $this->model->setLastLoginCountry($login, $currentLoginCountry);
        }
    }

    private function sendLoginFromDifferentCountryEmailToUser(string $login, string $countryCode, string $ip): void
    {
        $country = $countryCode ? Piwik::translate('Intl_Country_' . strtoupper($countryCode)) : '';
        $user = $this->usersModel->getUser($login);

        if (empty($user)) {
            throw new \Exception('Unexpected error: unable to find user');
        }

        // create from DI container so plugins can modify email contents if they want
        $email = StaticContainer::getContainer()->make(LoginFromDifferentCountryEmail::class, [
            'login' => $login,
            'country' => $country,
            'ip' => $ip,
        ]);
        $email->addTo($user['email'], $login);
        $email->safeSend();
    }
}
