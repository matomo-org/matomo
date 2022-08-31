<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CorePluginsAdmin;

use Piwik\Piwik;
use Piwik\Plugin\SettingsProvider;
use Exception;
use Piwik\Container\StaticContainer;
use Piwik\Plugins\CoreAdminHome\Emails\SettingsChangedEmail;
use Piwik\Plugins\CoreAdminHome\Emails\SecurityNotificationEmail;

/**
 * API for plugin CorePluginsAdmin
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
     * @param array $settingValues Format: array('PluginName' => array(array('name' => 'SettingName1', 'value' => 'SettingValue1), ..))
     * @throws Exception
     */
    public function setSystemSettings($settingValues, $passwordConfirmation = false)
    {
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
     * @param array $settingValues  Format: array('PluginName' => array(array('name' => 'SettingName1', 'value' => 'SettingValue1), ..))
     * @throws Exception
     */
    public function setUserSettings($settingValues)
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
     * @return array
     * @throws \Piwik\NoAccessException
     */
    public function getSystemSettings()
    {
        Piwik::checkUserHasSuperUserAccess();

        $systemSettings = $this->settingsProvider->getAllSystemSettings();

        return $this->settingsMetadata->formatSettings($systemSettings);
    }

    /**
     * @internal
     * @return array
     * @throws \Piwik\NoAccessException
     */
    public function getUserSettings()
    {
        Piwik::checkUserIsNotAnonymous();

        $userSettings = $this->settingsProvider->getAllUserSettings();

        return $this->settingsMetadata->formatSettings($userSettings);
    }

    private function sendNotificationEmails($sendSettingsChangedNotificationEmailPlugins)
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
            'pluginNames' => $pluginNames
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
                'superuser' => Piwik::getCurrentUserLogin()
            ));
            $superUserEmail->addTo($address);
        }

        if ($superUserEmail) {
            $superUserEmail->safeSend();
        }
    }
}
