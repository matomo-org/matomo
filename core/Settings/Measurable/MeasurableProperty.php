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
use Exception;

/**
 * Describes a Measurable property for a measurable type such as a website, a mobile app, ....
 *
 * The difference to {@link MeasurableSetting} is that these fields will be stored in the actual site table whereas
 * MeasurableSetting will be stored in a site_settings table. For this reasons MeasurableProperty can be used only
 * for some specific fields that already exist in site table such as "ecommerce", "sitesearch" etc.
 *
 * See {@link \Piwik\Settings\Setting}.
 */
class MeasurableProperty extends \Piwik\Settings\Setting
{
    /**
     * @var int
     */
    private $idSite = 0;

    private $allowedNames = array(
        'ecommerce', 'sitesearch', 'sitesearch_keyword_parameters',
        'sitesearch_category_parameters', 'excluded_referrers',
        'exclude_unknown_urls', 'excluded_ips', 'excluded_parameters',
        'excluded_user_agents', 'keep_url_fragment', 'urls', 'group'
    );

    /**
     * Constructor.
     *
     * @param string $name The persisted name of the setting.
     * @param mixed $defaultValue  Default value for this setting if no value was specified.
     * @param string $type Eg an array, int, ... see TYPE_* constants
     * @param string $pluginName The name of the plugin the setting belongs to.
     * @param int $idSite The idSite this property belongs to.
     * @throws Exception
     */
    public function __construct($name, $defaultValue, $type, $pluginName, $idSite)
    {
        if (!in_array($name, $this->allowedNames)) {
            throw new Exception(sprintf('Name "%s" is not allowed to be used with a MeasurableProperty, use a MeasurableSetting instead.', $name));
        }

        parent::__construct($name, $defaultValue, $type, $pluginName);

        $this->idSite = $idSite;

        $storageFactory = StaticContainer::get('Piwik\Settings\Storage\Factory');
        $this->storage = $storageFactory->getSitesTable($idSite);
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
