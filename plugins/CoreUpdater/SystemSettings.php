<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreUpdater;

use Piwik\Piwik;
use Piwik\Plugin\ReleaseChannels;
use Piwik\Plugins\CoreAdminHome\Controller as CoreAdminController;
use Piwik\Plugins\Marketplace\UpdateCommunication as PluginUpdateCommunication;
use Piwik\Settings\Setting;
use Piwik\Settings\FieldConfig;

/**
 * Defines Settings for CoreUpdater.
 *
 * Usage like this:
 * $settings = new SystemSettings();
 * $settings->metric->getValue();
 * $settings->description->getValue();
 */
class SystemSettings extends \Piwik\Settings\Plugin\SystemSettings
{
    /** @var Setting */
    public $releaseChannel;

    /** @var Setting */
    public $sendPluginUpdateEmail;

    /**
     * @var ReleaseChannels
     */
    private $releaseChannels;

    public function __construct(ReleaseChannels $releaseChannels)
    {
        $this->releaseChannels = $releaseChannels;

        parent::__construct();
    }

    protected function init()
    {
        $this->title = Piwik::translate('CoreAdminHome_UpdateSettings');

        $isWritable = Piwik::hasUserSuperUserAccess() && CoreAdminController::isGeneralSettingsAdminEnabled();
        $this->releaseChannel = $this->createReleaseChannel();
        $this->releaseChannel->setIsWritableByCurrentUser($isWritable);

        $isWritable = $isWritable && PluginUpdateCommunication::canBeEnabled();
        $this->sendPluginUpdateEmail = $this->createSendPluginUpdateEmail();
        $this->sendPluginUpdateEmail->setIsWritableByCurrentUser($isWritable);
    }

    private function createReleaseChannel()
    {
        $releaseChannels = $this->releaseChannels;
        $default = 'latest_stable';

        return $this->makeSettingManagedInConfigOnly('General', 'release_channel', $default, FieldConfig::TYPE_STRING, function (FieldConfig $field) use ($releaseChannels) {

            $field->introduction = Piwik::translate('CoreAdminHome_ReleaseChannel');
            $field->uiControl = FieldConfig::UI_CONTROL_RADIO;

            $field->availableValues = array();
            foreach ($releaseChannels->getAllReleaseChannels() as $channel) {
                $name = $channel->getName();
                $description = $channel->getDescription();
                if (!empty($description)) {
                    $name .= ' (' . $description . ')';
                }

                $field->availableValues[$channel->getId()] = $name;
            }

            $field->validate = function ($channel) use ($releaseChannels) {
                if (!$releaseChannels->isValidReleaseChannelId($channel)) {
                    throw new \Exception('Release channel is not valid');
                };
            };

            $field->inlineHelp = Piwik::translate('CoreAdminHome_DevelopmentProcess',
                                                  array("<a href='?module=Proxy&action=redirect&url=https://matomo.org/participate/development-process/' target='_blank'>",
                                                        "</a>"))
                            . Piwik::translate('<br/>')
                            . Piwik::translate('CoreAdminHome_StableReleases',
                                               array("<a href='?module=Proxy&action=redirect&url=https%3A%2F%2Fdeveloper.matomo.org%2Fguides%2Fcore-team-workflow%23influencing-piwik-development' target='_blank'>",
                                                     "</a>"))
                            . Piwik::translate('<br />')
                            . Piwik::translate('CoreAdminHome_LtsReleases');
        });
    }

    private function createSendPluginUpdateEmail()
    {
        return $this->makeSetting('enable_plugin_update_communication', $default = true, FieldConfig::TYPE_BOOL, function (FieldConfig $field) {
            $field->introduction = Piwik::translate('CoreAdminHome_SendPluginUpdateCommunication');
            $field->uiControl = FieldConfig::UI_CONTROL_RADIO;
            $field->availableValues = array('1' => sprintf('%s (%s)', Piwik::translate('General_Yes'), Piwik::translate('General_Default')),
                                            '0' => Piwik::translate('General_No'));
            $field->inlineHelp = Piwik::translate('CoreAdminHome_SendPluginUpdateCommunicationHelp');
        });
    }

}
