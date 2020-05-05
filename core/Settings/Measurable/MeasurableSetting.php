<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Settings\Measurable;

use Piwik\Container\StaticContainer;
use Piwik\Piwik;

/**
 * Describes a Measurable setting for a measurable type such as a website, a mobile app, ...
 *
 * See {@link \Piwik\Settings\Setting}.
 */
class MeasurableSetting extends \Piwik\Settings\Setting
{
    /**
     * @var int
     */
    private $idSite = 0;

    /**
     * Constructor.
     *
     * @param string $name The persisted name of the setting.
     * @param mixed $defaultValue  Default value for this setting if no value was specified.
     * @param string $type Eg an array, int, ... see TYPE_* constants
     * @param string $pluginName The name of the plugin the setting belongs to
     * @param int $idSite The idSite this setting belongs to.
     */
    public function __construct($name, $defaultValue, $type, $pluginName, $idSite)
    {
        parent::__construct($name, $defaultValue, $type, $pluginName);

        $this->idSite = $idSite;

        $storageFactory = StaticContainer::get('Piwik\Settings\Storage\Factory');
        $this->storage = $storageFactory->getMeasurableSettingsStorage($idSite, $this->pluginName);
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
        if ($this->hasSiteBeenCreated()) {
            $this->hasWritePermission = Piwik::isUserHasAdminAccess($this->idSite);
        } else {
            $this->hasWritePermission = Piwik::hasUserSuperUserAccess();
        }

        return $this->hasWritePermission;
    }

    private function hasSiteBeenCreated()
    {
        return !empty($this->idSite);
    }
}
