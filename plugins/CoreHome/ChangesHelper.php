<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CoreHome;

use Piwik\Common;
use Piwik\Date;
use Piwik\Db;
use Piwik\Piwik;
use Piwik\Plugin\Manager;
use Piwik\Plugins\UsersManager\Model AS UsersModel;

/**
 * CoreHome changes helper class
 */
class ChangesHelper
{
    const NO_CHANGES_EXIST = 0;
    const CHANGES_EXIST = 1;
    const NEW_CHANGES_EXIST = 2;

    /**
     * Find any new version changes for a plugin
     *
     * @param $pluginName
     * @param $version
     */
    public static function loadChangesForPlugin($pluginName, $version)
    {
        $file = Manager::getPluginDirectory($pluginName).'/changes.json';
        if (file_exists($file)) {
            $changes = file_get_contents($file);
            if ($changes) {
                $json = json_decode($changes, true);
                if ($json && is_array($json)) {
                    $json = array_reverse($json);
                    foreach ($json as $change) {
                        if (isset($change['version']) == $version) {
                            self::addChange($pluginName, $change);
                        }
                    }
                }
            }
        }
    }

    /**
     * Add a change item to the database table
     *
     * @param string $pluginName
     * @param array $change
     */
    public static function addChange(string $pluginName, array $change)
    {
        $db = Db::get();
        $table = Common::prefixTable('changes');
        $insertSql = "INSERT IGNORE INTO " . $table . ' (created_time, plugin_name, version, title, description, link_name, link) VALUES (NOW(), ?, ?, ?, ?, ?, ?)';

        $db->query($insertSql, [$pluginName, $change['version'], $change['title'], $change['description'], $change['link_name'], $change['link']]);
    }

    /**
     * Return a value indicating if there are any changes available to show the user
     *
     * @return int   ChangesHelper::NO_CHANGES_EXIST, ChangesHelper::CHANGES_EXIST or ChangesHelper::NEW_CHANGES_EXIST
     * @throws \Exception
     */
    public static function getNewChangesStatus()
    {
        $model = new UsersModel();
        $user = $model->getUser(Piwik::getCurrentUserLogin());
        $idchangeLastViewed = (isset($user['idchange_last_viewed']) ? $user['idchange_last_viewed'] : null);

        if ($idchangeLastViewed !== null) {
            $selectSql = "
                SELECT COUNT(*) AS a,
                  (SELECT COUNT(*) FROM " . Common::prefixTable('changes') . " WHERE idchange > ?) AS n
                FROM ".Common::prefixTable('changes');
            $params = [$idchangeLastViewed];
        } else {
            $selectSql = "SELECT COUNT(*) AS a, COUNT(*) AS n FROM ".Common::prefixTable('changes');
            $params = [];
        }

        $db = Db::get();
        $res = $db->fetchRow($selectSql, $params);
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
     * Return an array of changes provided by plugins
     *
     * @return array
     */
    public static function getChanges()
    {
        $showAtLeast = 10; // Always show at least this number of changes
        $expireOlderThanDays = 90; // Don't show changes that were added to the table more than x days ago

        $db = Db::get();
        $table = Common::prefixTable('changes');
        $selectSql = "SELECT * FROM " . $table . " WHERE title IS NOT NULL ORDER BY idchange DESC";
        $changes = $db->fetchAll($selectSql);

        // Remove expired changes, only if there are at more than the minimum changes
        $cutOffDate = Date::now()->subDay($expireOlderThanDays);
        $maxId = null;

        foreach ($changes as $k => $change) {
            if (count($changes) > $showAtLeast && $change['created_time'] < $cutOffDate) {
                unset($changes[$k]);
            } else {
                if ($maxId < $change['idchange']) {
                    $maxId = $change['idchange'];
                }
            }
        }

        // Record the time that changes were viewed for the current user
        if ($maxId) {
            $login = Piwik::getCurrentUserLogin();
            $model = new UsersModel();
            $model->updateUserFields($login, ['idchange_last_viewed' => $maxId]);
        }
        return $changes;
    }

}
