<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Settings\Storage;

use Piwik\Common;
use Piwik\Container\StaticContainer;
use Piwik\Db;
use Piwik\Option;
use Piwik\Piwik;
use Piwik\Plugins\MobileMessaging\MobileMessaging;
use Piwik\Plugins\UsersManager\API as UsersManagerAPI;

class LegacyUserSettingsMigration
{
    /**
     * @var UserScopedSettingsAccessManager
     */
    private $store;

    public function __construct(?UserScopedSettingsAccessManager $store = null)
    {
        if ($store === null) {
            $store = new UserScopedSettingsAccessManager();
        }

        $this->store = $store;
    }

    /**
     * @return array<string, int>
     */
    public function migrate(): array
    {
        $knownLogins = $this->getKnownLogins();

        return [
            'mobileMessaging' => $this->migrateMobileMessagingSettings($knownLogins),
            'feedback' => $this->migrateFeedbackSettings($knownLogins),
            'professionalServices' => $this->migrateProfessionalServicesSettings($knownLogins),
            'usersManagerPreferences' => $this->migrateUsersManagerPreferences($knownLogins),
        ];
    }

    /**
     * @param array<string, bool> $knownLogins
     */
    private function migrateMobileMessagingSettings(array $knownLogins): int
    {
        $migratedCount = 0;
        $suffix = MobileMessaging::USER_SETTINGS_POSTFIX_OPTION;
        $legacySettings = Option::getLike('%' . $suffix);

        foreach ($legacySettings as $optionName => $optionValue) {
            if (!str_ends_with($optionName, $suffix)) {
                continue;
            }

            $login = substr($optionName, 0, -strlen($suffix));
            if ($login !== '' && !isset($knownLogins[$login])) {
                Option::delete($optionName);
                continue;
            }

            $existingSettings = $this->getMobileMessagingSettings($login);
            if (empty($existingSettings) && is_string($optionValue)) {
                $decoded = json_decode($optionValue, true);
                if (is_array($decoded)) {
                    $this->setMobileMessagingSettings($login, $decoded);
                    ++$migratedCount;
                }
            }

            Option::delete($optionName);
        }

        return $migratedCount;
    }

    /**
     * @param array<string, bool> $knownLogins
     */
    private function migrateFeedbackSettings(array $knownLogins): int
    {
        $migratedCount = 0;
        $prefix = 'Feedback.nextFeedbackReminder.';
        $legacySettings = Option::getLike($prefix . '%');

        foreach ($legacySettings as $optionName => $optionValue) {
            if (!str_starts_with($optionName, $prefix)) {
                continue;
            }

            $login = substr($optionName, strlen($prefix));
            if ($login !== '' && isset($knownLogins[$login]) && !$this->hasSetting('Feedback', $login, 'nextFeedbackReminder')) {
                $this->store->set('Feedback', $login, 'nextFeedbackReminder', $optionValue);
                ++$migratedCount;
            }

            Option::delete($optionName);
        }

        return $migratedCount;
    }

    /**
     * @param array<string, bool> $knownLogins
     */
    private function migrateProfessionalServicesSettings(array $knownLogins): int
    {
        $migratedCount = 0;
        $prefix = 'ProfessionalServices.DismissedWidget.';
        $legacySettings = Option::getLike($prefix . '%');
        $legacyByLogin = [];
        $knownLoginNames = array_keys($knownLogins);
        // sort logins by length to avoid partial collisions
        usort($knownLoginNames, function ($a, $b) {
            return strlen($b) <=> strlen($a);
        });

        foreach ($legacySettings as $optionName => $optionValue) {
            if (!str_starts_with($optionName, $prefix)) {
                continue;
            }

            $payload = substr($optionName, strlen($prefix));

            $matchedLogin = null;
            $matchedWidget = null;

            foreach ($knownLoginNames as $candidateLogin) {
                if (empty($candidateLogin)) {
                    continue;
                }

                $suffix = '.' . $candidateLogin;
                if (!str_ends_with($payload, $suffix)) {
                    continue;
                }

                $widgetName = substr($payload, 0, strlen($suffix) * -1);
                if (empty($widgetName)) {
                    continue;
                }

                $matchedLogin = $candidateLogin;
                $matchedWidget = $widgetName;
                break;
            }

            if (empty($matchedLogin)) {
                continue;
            }

            $legacyByLogin[$matchedLogin][$matchedWidget] = (int) $optionValue;
        }

        foreach ($legacyByLogin as $login => $legacyWidgets) {
            $dismissedWidgets = $this->store->get('ProfessionalServices', $login, 'dismissedWidgets', []);
            if (!is_array($dismissedWidgets)) {
                $dismissedWidgets = [];
            }

            $hasChanges = false;
            foreach ($legacyWidgets as $widgetName => $optionValue) {
                if (!isset($dismissedWidgets[$widgetName])) {
                    $dismissedWidgets[$widgetName] = $optionValue;
                    $hasChanges = true;
                }
            }

            if ($hasChanges) {
                $this->store->set('ProfessionalServices', $login, 'dismissedWidgets', $dismissedWidgets);
                ++$migratedCount;
            }
        }

        Option::deleteLike('ProfessionalServices.DismissedWidget.%');

        return $migratedCount;
    }

    /**
     * @param array<string, bool> $knownLogins
     */
    private function migrateUsersManagerPreferences(array $knownLogins): int
    {
        $migratedCount = 0;
        $preferenceNames = $this->getSupportedPreferenceNames();

        foreach ($preferenceNames as $preferenceName) {
            $suffix = UsersManagerAPI::OPTION_NAME_PREFERENCE_SEPARATOR . $preferenceName;
            $legacySettings = Option::getLike('%' . $suffix);

            foreach ($legacySettings as $optionName => $optionValue) {
                if (!str_ends_with($optionName, $suffix)) {
                    continue;
                }

                $login = substr($optionName, 0, -strlen($suffix));
                if ($login === '' || !$this->isLikelyValidLogin($login)) {
                    continue;
                }

                if (isset($knownLogins[$login]) && !$this->hasSetting('UsersManager', $login, $preferenceName)) {
                    $this->store->set('UsersManager', $login, $preferenceName, $optionValue);
                    ++$migratedCount;
                }

                /**
                 * @deprecated Should be removed with Matomo 6, LoginLdap should be
                 *             updated to not rely on Option storage for this setting
                 */
                if ($preferenceName === 'isLDAPUser' && isset($knownLogins[$login])) {
                    // Keep legacy option key for LoginLdap submodule compatibility.
                    continue;
                }

                Option::delete($optionName);
            }
        }

        return $migratedCount;
    }

    /**
     * @return array<string, bool>
     */
    private function getKnownLogins(): array
    {
        $logins = ['anonymous' => true];
        $rows = Db::fetchAll('SELECT login FROM `' . Common::prefixTable('user') . '`');

        foreach ($rows as $row) {
            if (!empty($row['login'])) {
                $logins[$row['login']] = true;
            }
        }

        return $logins;
    }

    /**
     * @return string[]
     */
    private function getSupportedPreferenceNames(): array
    {
        $preferenceNames = [
            UsersManagerAPI::PREFERENCE_DEFAULT_REPORT,
            UsersManagerAPI::PREFERENCE_DEFAULT_REPORT_DATE,
            'isLDAPUser',
            'hideSegmentDefinitionChangeMessage',
        ];
        $customPreferenceNames = StaticContainer::get('usersmanager.user_preference_names');
        if (!is_array($customPreferenceNames)) {
            $customPreferenceNames = [];
        }

        $customPreferenceNames = array_values(array_filter($customPreferenceNames, 'is_string'));

        return array_values(array_unique(array_merge($preferenceNames, $customPreferenceNames)));
    }

    private function isLikelyValidLogin(string $login): bool
    {
        try {
            Piwik::checkValidLoginString($login);
            return true;
        } catch (\Exception $exception) {
            return false;
        }
    }

    private function hasSetting(string $pluginName, string $userLogin, string $settingName): bool
    {
        $settings = $this->store->getAll($pluginName, $userLogin);
        return array_key_exists($settingName, $settings);
    }

    /**
     * @return array<string, mixed>
     */
    private function getMobileMessagingSettings(string $userLogin): array
    {
        if ($userLogin === '') {
            $settings = $this->getFactory()->getPluginStorage('MobileMessaging', $userLogin)->getBackend()->load();
            return is_array($settings) ? $settings : [];
        }

        return $this->store->getAll('MobileMessaging', $userLogin);
    }

    /**
     * @param array<string, mixed> $settings
     */
    private function setMobileMessagingSettings(string $userLogin, array $settings): void
    {
        if ($userLogin === '') {
            $this->getFactory()->getPluginStorage('MobileMessaging', $userLogin)->getBackend()->save($settings);
            return;
        }

        $this->store->setAll('MobileMessaging', $userLogin, $settings);
    }

    private function getFactory(): Factory
    {
        return StaticContainer::get(Factory::class);
    }
}
