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
use Piwik\Updater\Migration;
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

    const NO_CHANGES_EXIST = 0;
    const CHANGES_EXIST = 1;
    const NEW_CHANGES_EXIST = 2;

    private $pluginManager;

    /**
     * @var Db\AdapterInterface
     */
    private $db;

    /**
     * @param Db\AdapterInterface|null $db
     * @param PluginManager|null $pluginManager
     */
    public function __construct(?Db\AdapterInterface $db = null, ?PluginManager $pluginManager = null)
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
    public function addChanges(string $pluginName): void
    {
        if ($this->pluginManager->isValidPluginName($pluginName) && $this->pluginManager->isPluginInFilesystem($pluginName)) {

            $plugin = $this->pluginManager->loadPlugin($pluginName);
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
    public function removeChanges(string $pluginName): void
    {
        $table = Common::prefixTable('changes');

        try {
            $this->db->query("DELETE FROM " . $table . " WHERE plugin_name = ?", [$pluginName]);
        } catch (\Exception $e) {
            if (Db::get()->isErrNo($e, Migration\Db::ERROR_CODE_TABLE_NOT_EXISTS)) {
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
    public function addChange(string $pluginName, array $change): void
    {
        if(!isset($change['version']) || !isset($change['title']) || !isset($change['description'])) {
            StaticContainer::get('Psr\Log\LoggerInterface')->warning(
                "Change item for plugin {plugin} missing version, title or description fields - ignored",
                ['plugin' => $pluginName]);
            return;
        }

        $table = Common::prefixTable('changes');

        $values = [
            'created_time' => Date::now()->getDatetime(),
            'plugin_name' => $pluginName,
            'version' => $change['version'],
            'title' => $change['title'],
            'description' => $change['description']
        ];

        if (isset($change['link_name']) && isset($change['link'])) {
            $values['link_name'] = $change['link_name'];
            $values['link'] = $change['link'];
        }

        try {
            $this->db->insert($table, $values);
        } catch (\Exception $e) {
            if (Db::get()->isErrNo($e, Migration\Db::ERROR_CODE_TABLE_NOT_EXISTS)) {
                return;
            }
            throw $e;
        }
    }

    /**
     * Check if any changes items exist
     *
     * @param int|null $newerThanId     Only check for changes newer than this sequential key
     *
     * @return int
     */
    public function doChangesExist(?int $newerThanId = null): int
    {

        if ($newerThanId !== null) {
            $selectSql = "
                SELECT COUNT(*) AS a,
                  (SELECT COUNT(*) FROM " . Common::prefixTable('changes') . " WHERE idchange > ?) AS n
                FROM ".Common::prefixTable('changes');
            $params = [$newerThanId];
        } else {
            $selectSql = "SELECT COUNT(*) AS a, COUNT(*) AS n FROM ".Common::prefixTable('changes');
            $params = [];
        }

        try {
            $res = $this->db->fetchRow($selectSql, $params);
        } catch (\Exception $e) {
            if (Db::get()->isErrNo($e, Migration\Db::ERROR_CODE_TABLE_NOT_EXISTS)) {
                return self::NO_CHANGES_EXIST;
            }
            throw $e;
        }
        $new = $res['n'];
        $all = $res['a'];

        if ($all == 0) {
            return self::NO_CHANGES_EXIST;
        } else if ($all > 0 && $new == 0) {
            return self::CHANGES_EXIST;
        } else {
            return self::NEW_CHANGES_EXIST;
        }
    }

    /**
     * Return an array of changes from the changes tables
     *
     * @return array
     */
    public function getChangeItems(): array
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
