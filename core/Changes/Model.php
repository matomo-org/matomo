<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Changes;

use Piwik\Common;
use Piwik\Date;
use Piwik\Db;
use Piwik\Container\StaticContainer;
use Piwik\Plugin\Manager as PluginManager;

/**
 * Change model class
 *
 * Handle all data access operations for changes
 *
 */
class Model
{
    private $pluginManager;
    private $db;

    public function __construct($db = null, ?PluginManager $pluginManager = null)
    {
        $this->db = ($db ?? Db::get());
        $this->pluginManager = ($pluginManager ?? PluginManager::getInstance());
    }

    /**
     * Add any new changes for a plugin to the changes table
     *
     * @param string $pluginName
     *
     * @throws \Exception
     */
    public function addChanges(string $pluginName)
    {
        if ($this->pluginManager->isValidPluginName($pluginName) && $this->pluginManager->isPluginInFilesystem($pluginName)) {
            if ($this->pluginManager->isPluginLoaded($pluginName)) {
                $plugin = $this->pluginManager->getLoadedPlugin($pluginName);
                if (!empty($plugin)) {
                    $plugin->reloadPluginInformation();
                }
            } else {
                $plugin = $this->pluginManager->loadPlugin($pluginName);
            }

            if (!$plugin) {
                return;
            }

            $changes = $plugin->getChanges();
            foreach ($changes as $change) {
                $this->addChange($pluginName, $change);
            }
        }
    }

    /**
     * Remove all changes for a plugin
     *
     * @param string $pluginName
     */
    public function removeChanges(string $pluginName)
    {
        $table = Common::prefixTable('changes');

        try {
            $this->db->query("DELETE FROM " . $table . " WHERE plugin_name = ?", [$pluginName]);
        } catch (\Exception $e) {
            // Ignore table not found
            if ($e->getCode() === 42) {
                return;
            }
            throw $e;
        }
    }

    /**
     * Add a change item to the database table
     *
     * @param string $pluginName
     * @param array  $change
     */
    public function addChange(string $pluginName, array $change)
    {
        if(!isset($change['version']) || !isset($change['title']) || !isset($change['description'])) {
            StaticContainer::get('Psr\Log\LoggerInterface')->warning(
                "Change item for plugin {plugin} missing version, title or description fields - ignored",
                ['plugin' => $pluginName]);
            return;
        }

        $table = Common::prefixTable('changes');

        $fields = ['plugin_name', 'version', 'title', 'description'];
        $params = [$pluginName, $change['version'], $change['title'], $change['description']];

        if (isset($change['link_name']) && isset($change['link'])) {
            $fields[] = 'link_name';
            $fields[] = 'link';
            $params[] = $change['link_name'];
            $params[] = $change['link'];
        }

        $insertSql = 'INSERT IGNORE INTO ' . $table . ' (created_time,'.implode(',', $fields).') 
                      VALUES (NOW(),'.implode(',', array_fill(0, count($params), "?")).')';

        try {
            $this->db->query($insertSql, $params);
        } catch (\Exception $e) {
            // Ignore table not found
            if ($e->getCode() === 42) {
                return;
            }
            throw $e;
        }

    }

    /**
     * Return an array of changes from the changes tables
     *
     * @return array
     */
    public function getChangeItems()
    {
        $showAtLeast = 10; // Always show at least this number of changes
        $expireOlderThanDays = 90; // Don't show changes that were added to the table more than x days ago

        $table = Common::prefixTable('changes');
        $selectSql = "SELECT * FROM " . $table . " WHERE title IS NOT NULL ORDER BY idchange DESC";
        $changes = $this->db->fetchAll($selectSql);

        // Remove expired changes, only if there are at more than the minimum changes
        $cutOffDate = Date::now()->subDay($expireOlderThanDays);
        foreach ($changes as $k => $change) {
            if (count($changes) > $showAtLeast && $change['created_time'] < $cutOffDate) {
                unset($changes[$k]);
            }
        }

        return $changes;
    }

}
