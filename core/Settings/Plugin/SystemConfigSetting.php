<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Settings\Plugin;

use Piwik\Container\StaticContainer;
use Piwik\Piwik;
use Piwik\Settings\Setting;

/**
 * Describes a system wide setting. Only the Super User can change this type of setting by d efault and
 * the value of this setting will affect all users.
 *
 * See {@link \Piwik\Settings\Setting}.
 *
 * @api
 */
class SystemConfigSetting extends Setting
{
    protected $configSection = null;
    /**
     * Constructor.
     *
     * @param string $name The setting's persisted name.
     * @param mixed $defaultValue  Default value for this setting if no value was specified.
     * @param string $type Eg an array, int, ... see TYPE_* constants
     * @param string $pluginName The name of the plugin the system setting belongs to.
     */
    public function __construct($name, $defaultValue, $type, $pluginName, $configSectionName)
    {
        parent::__construct($name, $defaultValue, $type, $pluginName);

        $factory = StaticContainer::get('Piwik\Settings\Storage\Factory');
        $this->configSection = $configSectionName;
        $this->storage = $factory->getConfigStorage($configSectionName);
    }

    /**
     * Returns `true` if this setting is writable for the current user, `false` if otherwise. In case it returns
     * writable for the current user it will be visible in the Plugin settings UI.
     *
     * @return bool
     */
    public function isWritableByCurrentUser()
    {
        if (isset($this->hasWritePermission)) {
            return $this->hasWritePermission;
        }

        // performance improvement, do not detect this in __construct otherwise likely rather "big" query to DB.
        $this->hasWritePermission = Piwik::hasUserSuperUserAccess();

        return $this->hasWritePermission;
    }

    /**
     * Returns the config section the setting is for
     *
     * @return string
     */
    public function getConfigSectionName()
    {
        return $this->configSection;
    }
}
