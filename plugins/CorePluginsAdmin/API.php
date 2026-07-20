<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CorePluginsAdmin;

use Piwik\Cache;
use Piwik\Piwik;
use Piwik\Plugin\SettingsProvider;
use Exception;
use Piwik\Container\StaticContainer;
use Piwik\Plugins\CoreAdminHome\Emails\SettingsChangedEmail;
use Piwik\Plugins\CoreAdminHome\Emails\SecurityNotificationEmail;
use Piwik\Plugins\Marketplace\Marketplace;

/**
 * Provides API methods for reading and updating plugin settings.
 *
 * @method static \Piwik\Plugins\CorePluginsAdmin\API getInstance()
 */
class API extends \Piwik\Plugin\API
{
    /**
     * @var SettingsMetadata
     */
    private $settingsMetadata;

    /**
     * @var SettingsProvider
     */
    private $settingsProvider;

    public function __construct(SettingsProvider $settingsProvider, SettingsMetadata $settingsMetadata)
    {
        $this->settingsProvider = $settingsProvider;
        $this->settingsMetadata = $settingsMetadata;
    }

    /**
     * @internal
     * @param array<string, array<int, array{name:string, value?:mixed}>> $settingValues
     * @param string|false $passwordConfirmation
     */
    public function setSystemSettings(
        $settingValues,
        #[\SensitiveParameter]
        $passwordConfirmation = false
    ): void {
        Piwik::checkUserHasSuperUserAccess();

        $this->confirmCurrentUserPassword($passwordConfirmation);

        $pluginsSettings = $this->settingsProvider->getAllSystemSettings();

        $this->settingsMetadata->setPluginSettings($pluginsSettings, $settingValues);

        $sendSettingsChangedNotificationEmailPlugins = [];

        try {
            foreach ($pluginsSettings as $pluginSetting) {
                if (!empty($settingValues[$pluginSetting->getPluginName()])) {
                    $pluginSetting->save();

                    $pluginName = $pluginSetting->getPluginName();
                    if (in_array($pluginName, array_keys(SecurityNotificationEmail::$notifyPluginList))) {
                        $sendSettingsChangedNotificationEmailPlugins[] = $pluginName;
                    }
                }
            }
        } catch (Exception $e) {
            throw new Exception(Piwik::translate('CoreAdminHome_PluginSettingsSaveFailed'));
        }

        if (count($sendSettingsChangedNotificationEmailPlugins) > 0) {
            $this->sendNotificationEmails($sendSettingsChangedNotificationEmailPlugins);
        }
    }

    /**
     * @internal
     * @param array<string, array<int, array{name:string, value?:mixed}>> $settingValues
     */
    public function setUserSettings($settingValues): void
    {
        Piwik::checkUserIsNotAnonymous();

        $pluginsSettings = $this->settingsProvider->getAllUserSettings();

        $this->settingsMetadata->setPluginSettings($pluginsSettings, $settingValues);

        try {
            foreach ($pluginsSettings as $pluginSetting) {
                if (!empty($settingValues[$pluginSetting->getPluginName()])) {
                    $pluginSetting->save();
                }
            }
        } catch (Exception $e) {
            throw new Exception(Piwik::translate('CoreAdminHome_PluginSettingsSaveFailed'));
        }
    }

    /**
     * @internal
     * @return array<int, array<string, mixed>>
     */
    public function getSystemSettings()
    {
        Piwik::checkUserHasSuperUserAccess();

        $systemSettings = $this->settingsProvider->getAllSystemSettings();

        return $this->settingsMetadata->formatSettings($systemSettings);
    }

    /**
     * @internal
     * @return array<int, array<string, mixed>>
     */
    public function getUserSettings()
    {
        Piwik::checkUserIsNotAnonymous();

        $userSettings = $this->settingsProvider->getAllUserSettings();

        return $this->settingsMetadata->formatSettings($userSettings);
    }

    /**
     * @internal
     */
    public function getNumberOfPluginUpdates(): int
    {
        try {
            Piwik::checkUserHasSuperUserAccess();

            if (!Marketplace::isMarketplaceEnabled()) {
                return 0;
            }

            $cacheKey = 'CorePluginsAdmin_NumberOfPluginUpdates';
            $cache = Cache::getLazyCache();

            if ($cache->contains($cacheKey)) {
                return $cache->fetch($cacheKey);
            }

            $marketplacePlugins = StaticContainer::get('Piwik\Plugins\Marketplace\Plugins');
            $updatesCount = count($marketplacePlugins->getPluginsHavingUpdate());
            $cache->save($cacheKey, $updatesCount, 300);

            return $updatesCount;
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * @param string[] $sendSettingsChangedNotificationEmailPlugins
     */
    private function sendNotificationEmails(array $sendSettingsChangedNotificationEmailPlugins): void
    {
        $pluginNames = [];
        foreach ($sendSettingsChangedNotificationEmailPlugins as $plugin) {
            $pluginNames[] = Piwik::translate(SettingsChangedEmail::$notifyPluginList[$plugin]);
        }
        $pluginNames = implode(', ', $pluginNames);

        $container = StaticContainer::getContainer();

        $email = $container->make(SettingsChangedEmail::class, array(
            'login' => Piwik::getCurrentUserLogin(),
            'emailAddress' => Piwik::getCurrentUserEmail(),
            'pluginNames' => $pluginNames,
        ));
        $email->safeSend();

        $superuserEmailAddresses = Piwik::getAllSuperUserAccessEmailAddresses();
        unset($superuserEmailAddresses[Piwik::getCurrentUserLogin()]);
        $superUserEmail = false;

        foreach ($superuserEmailAddresses as $address) {
            $superUserEmail = $superUserEmail ?: $container->make(SettingsChangedEmail::class, array(
                'login' => Piwik::translate('Installation_SuperUser'),
                'emailAddress' => $address,
                'pluginNames' => $pluginNames,
                'superuser' => Piwik::getCurrentUserLogin(),
            ));
            $superUserEmail->addTo($address);
        }

        if ($superUserEmail) {
            $superUserEmail->safeSend();
        }
    }
}
