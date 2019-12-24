<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CorePluginsAdmin\Model;

use Piwik\Plugin;
use Piwik\Option;
use Piwik\Piwik;
use Piwik\Plugins\CorePluginsAdmin\CorePluginsAdmin;
use Piwik\Settings\Storage\Backend\PluginSettingsTable;

class TagManagerTeaser
{
    const DISABLE_GLOBALLY_KEY = 'CorePluginsAdmin.disableTagManagerTeaser';

    /**
     * @var string
     */
    private $login;

    public function __construct($login)
    {
        $this->login = $login;
    }

    public function shouldShowTeaser()
    {
        $pluginManager = Plugin\Manager::getInstance();

        return CorePluginsAdmin::isPluginsAdminEnabled()
                && (!$pluginManager->isPluginActivated('TagManager')
                || !$pluginManager->isPluginLoaded('TagManager'))
                && $pluginManager->isPluginInFilesystem('TagManager')
                && Piwik::isUserHasSomeAdminAccess()
                && $this->isEnabledGlobally()
                && $this->isEnabledForUser();
    }

    public function disableForUser()
    {
        $table = $this->getTable();
        $settings = $table->load();
        $settings['disable_activate_tag_manager_page'] = 1;
        $table->save($settings);
    }

    public function isEnabledForUser()
    {
        $pluginSettingsTable = $this->getTable();
        $settings = $pluginSettingsTable->load();

        return empty($settings['disable_activate_tag_manager_page']);
    }

    public function disableGlobally()
    {
        $this->reset();
        Option::set(self::DISABLE_GLOBALLY_KEY, 1, true);
    }

    public function reset()
    {
        Option::delete(self::DISABLE_GLOBALLY_KEY);

        // no need to keep any old login entries
        $this->getTable()->delete();
    }

    public function isEnabledGlobally()
    {
        $value = Option::get(self::DISABLE_GLOBALLY_KEY);
        return empty($value);
    }

    private function getTable()
    {
        return new PluginSettingsTable('CorePluginsAdmin', $this->login);
    }

}
