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
use Exception;
use Piwik\Settings\Setting;

/**
 * Describes a per user setting. Each user will be able to change this setting for themselves,
 * but not for other users.
 *
 * See {@link \Piwik\Settings\Setting}.
 */
class UserSetting extends Setting
{
    /**
     * @var null|string
     */
    private $userLogin = null;

    /**
     * Constructor.
     *
     * @param string $name The setting's persisted name.
     * @param mixed $defaultValue  Default value for this setting if no value was specified.
     * @param string $type Eg an array, int, ... see TYPE_* constants
     * @param string $pluginName The name of the plugin the setting belongs to
     * @param string $userLogin The name of the user the value should be set or get for
     * @throws Exception
     */
    public function __construct($name, $defaultValue, $type, $pluginName, $userLogin)
    {
        parent::__construct($name, $defaultValue, $type, $pluginName);

        if (empty($userLogin)) {
            throw new Exception('No userLogin given to create setting ' . $name);
        }

        $this->userLogin = $userLogin;

        $factory = StaticContainer::get('Piwik\Settings\Storage\Factory');
        $this->storage = $factory->getPluginStorage($this->pluginName, $this->userLogin);
    }

    /**
     * Returns `true` if this setting can be displayed for the current user, `false` if otherwise.
     *
     * @return bool
     */
    public function isWritableByCurrentUser()
    {
        if (isset($this->hasWritePermission)) {
            return $this->hasWritePermission;
        }

        // performance improvement, do not detect this in __construct otherwise likely rather "big" query to DB.
        $this->hasWritePermission = Piwik::isUserHasSomeViewAccess();

        return $this->hasWritePermission;
    }

}
