<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\TrackingSpamPrevention\Commands;

use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\Plugin\ConsoleCommand;
use Piwik\Plugins\TrackingSpamPrevention\SystemSettings;

class BlockGeoIpOrganisation extends ConsoleCommand
{
    protected function configure()
    {
        $this->setName('trackingspamprevention:block-geo-ip-organisation');
        $this->setDescription('Blocks a new GeoIP organisation. It will save the organisation in the "Organisation block list" system setting.');
        $this->addRequiredValueOption('organisation-name', null, 'Name of the organisation to block:');
    }

    protected function doExecute(): int
    {
        $this->checkAllRequiredOptionsAreNotEmpty();

        $name = mb_strtolower(trim($this->getInput()->getOption('organisation-name')));
        if ($name === '') {
            throw new \InvalidArgumentException('The organisation name must not be empty.');
        }

        $settings = StaticContainer::get(SystemSettings::class);
        $setting = $settings->organisationBlockList;
        $modeSetting = $settings->cloudBlockingMode;

        $pluginConfig = Config::getInstance()->TrackingSpamPrevention;
        $pluginConfig = is_array($pluginConfig) ? $pluginConfig : [];

        if (array_key_exists($setting->getName(), $pluginConfig)) {
            // a config override makes the setting unwritable and shadows any stored value
            $this->getOutput()->writeln(sprintf(
                '<error>The organisation block list is overridden by an "%s" entry in the config file. Remove that entry to manage the list with this command.</error>',
                $setting->getName()
            ));
            return self::FAILURE;
        }

        $mode = $settings->getCloudBlockingMode();

        // only the custom list is consulted, so an addition made while the default list is selected
        // would never match anything. Turning blocking on from off is a bigger step than this command
        // is asked to take, so that mode is left alone and reported instead.
        $switchesToCustomList = $mode === SystemSettings::CLOUD_BLOCKING_DEFAULT_LIST;

        if ($switchesToCustomList && array_key_exists($modeSetting->getName(), $pluginConfig)) {
            $this->getOutput()->writeln(sprintf(
                '<error>Cloud hosting provider blocking is overridden by a "%s" entry in the config file, so it cannot be switched to the custom organisation list. Remove that entry, or set it to "%s".</error>',
                $modeSetting->getName(),
                SystemSettings::CLOUD_BLOCKING_CUSTOM_LIST
            ));
            return self::FAILURE;
        }

        // the command runs without a session, so force writability
        $setting->setIsWritableByCurrentUser(true);

        $organisations = $setting->getValue();
        if (!is_array($organisations)) {
            $organisations = [];
        }
        $organisations[] = $name;

        $setting->setValue(array_values(array_unique($organisations)));

        if ($switchesToCustomList) {
            $modeSetting->setIsWritableByCurrentUser(true);
            $modeSetting->setValue(SystemSettings::CLOUD_BLOCKING_CUSTOM_LIST);
        }

        $settings->save();

        $this->getOutput()->writeln(sprintf('<info>Added "%s" to the organisation block list.</info>', $name));

        if ($switchesToCustomList) {
            $this->getOutput()->writeln('<info>Cloud hosting provider blocking now uses the custom organisation list.</info>');
        }

        if ($mode === SystemSettings::CLOUD_BLOCKING_OFF) {
            $this->getOutput()->writeln(sprintf(
                '<comment>Cloud hosting provider blocking is turned off, so this entry has no effect until you select "%s".</comment>',
                SystemSettings::CLOUD_BLOCKING_CUSTOM_LIST
            ));
        }

        return self::SUCCESS;
    }
}
