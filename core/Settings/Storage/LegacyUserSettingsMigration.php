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
     * @var UserScopedSettingsStore
     */
    private $store;

    public function __construct(?UserScopedSettingsStore $store = null)
    {
        if ($store === null) {
            $store = new UserScopedSettingsStore();
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

        foreach (array_keys($knownLogins) as $login) {
            $legacySettings = Option::getLike('ProfessionalServices.DismissedWidget.%.' . $login);
            if (empty($legacySettings)) {
                continue;
            }

            $pattern = '/^ProfessionalServices\.DismissedWidget\.(.+)\.' . preg_quote($login, '/') . '$/';
            $dismissedWidgets = $this->store->get('ProfessionalServices', $login, 'dismissedWidgets', []);
            if (!is_array($dismissedWidgets)) {
                $dismissedWidgets = [];
            }

            $hasChanges = false;
            foreach ($legacySettings as $optionName => $optionValue) {
                if (!preg_match($pattern, $optionName, $matches)) {
                    continue;
                }

                if (!isset($dismissedWidgets[$matches[1]])) {
                    $dismissedWidgets[$matches[1]] = (int) $optionValue;
                    $hasChanges = true;
                }
            }

            if ($hasChanges) {
                $this->store->set('ProfessionalServices', $login, 'dismissedWidgets', $dismissedWidgets);
                ++$migratedCount;
            }

            Option::deleteLike('ProfessionalServices.DismissedWidget.%.' . $login);
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
