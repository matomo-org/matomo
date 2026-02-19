<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Settings\Storage;

use Piwik\Option;
use Piwik\Plugins\MobileMessaging\MobileMessaging;
use Piwik\Plugins\UsersManager\API as UsersManagerAPI;

class LegacyUserSettingsMigrator
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

    public function migrateMobileMessagingSettings(string $userLogin): void
    {
        if ($userLogin === '') {
            return;
        }

        if (!empty($this->store->getAll('MobileMessaging', $userLogin))) {
            return;
        }

        $legacyKey = $userLogin . MobileMessaging::USER_SETTINGS_POSTFIX_OPTION;
        $legacyValue = Option::get($legacyKey);
        if (empty($legacyValue)) {
            return;
        }

        $decoded = json_decode($legacyValue, true);
        if (!is_array($decoded)) {
            return;
        }

        $this->store->setAll('MobileMessaging', $userLogin, $decoded);
        Option::delete($legacyKey);
    }

    public function migrateFeedbackReminder(string $userLogin): void
    {
        if ($userLogin === '') {
            return;
        }

        $storeKey = 'nextFeedbackReminder';
        $current = $this->store->get('Feedback', $userLogin, $storeKey, false);
        if ($current !== false) {
            return;
        }

        $legacyKey = 'Feedback.nextFeedbackReminder.' . $userLogin;
        $legacyValue = Option::get($legacyKey);
        if ($legacyValue === false) {
            return;
        }

        $this->store->set('Feedback', $userLogin, $storeKey, $legacyValue);
        Option::delete($legacyKey);
    }

    public function migrateProfessionalServicesDismissedWidgets(string $userLogin): void
    {
        if ($userLogin === '') {
            return;
        }

        $storeKey = 'dismissedWidgets';
        $current = $this->store->get('ProfessionalServices', $userLogin, $storeKey, null);
        if (is_array($current) && !empty($current)) {
            return;
        }

        $legacyOptions = Option::getLike('ProfessionalServices.DismissedWidget.%.' . $userLogin);
        if (empty($legacyOptions)) {
            return;
        }

        $dismissed = [];
        $pattern = '/^ProfessionalServices\.DismissedWidget\.(.+)\.' . preg_quote($userLogin, '/') . '$/';
        foreach ($legacyOptions as $optionName => $optionValue) {
            if (!preg_match($pattern, $optionName, $matches)) {
                continue;
            }

            $dismissed[$matches[1]] = (int) $optionValue;
        }

        if (!empty($dismissed)) {
            $this->store->set('ProfessionalServices', $userLogin, $storeKey, $dismissed);
        }

        Option::deleteLike('ProfessionalServices.DismissedWidget.%.' . $userLogin);
    }

    public function migrateUsersManagerPreference(string $userLogin, string $preferenceName): void
    {
        if ($userLogin === '' || $preferenceName === '') {
            return;
        }

        $current = $this->store->get('UsersManager', $userLogin, $preferenceName, false);
        if ($current !== false) {
            return;
        }

        $legacyKey = $this->getUsersManagerPreferenceOptionName($userLogin, $preferenceName);
        $legacyValue = Option::get($legacyKey);
        if ($legacyValue === false) {
            return;
        }

        $this->store->set('UsersManager', $userLogin, $preferenceName, $legacyValue);
        Option::delete($legacyKey);
    }

    /**
     * @param string $userLogin
     * @param string[] $preferenceNames
     * @return void
     */
    public function migrateUsersManagerPreferences(string $userLogin, array $preferenceNames): void
    {
        foreach ($preferenceNames as $preferenceName) {
            $this->migrateUsersManagerPreference($userLogin, $preferenceName);
        }
    }

    private function getUsersManagerPreferenceOptionName(string $userLogin, string $preferenceName): string
    {
        return $userLogin . UsersManagerAPI::OPTION_NAME_PREFERENCE_SEPARATOR . $preferenceName;
    }
}
