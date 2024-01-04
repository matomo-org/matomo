<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Framework\Mock\Settings;


class FakeUserSettings extends \Piwik\Plugins\ExampleSettingsPlugin\UserSettings
{
    protected $pluginName = 'ExampleSettingsPlugin';

    public function init()
    {
    }

    public function makeSetting($name, $defaultValue, $type, $configureCallback)
    {
        return parent::makeSetting($name, $defaultValue, $type, $configureCallback);
    }
}
