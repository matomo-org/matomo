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
use Piwik\Db;
use Piwik\Changes\Model as ChangesModel;
use Piwik\Plugins\UsersManager\Model as UsersModel;

/**
 * CoreHome user changes class
 */
class UserChanges
{
    const NO_CHANGES_EXIST = 0;
    const CHANGES_EXIST = 1;
    const NEW_CHANGES_EXIST = 2;

    private $db;
    private $user;

    public function __construct(array $user, $db = null)
    {
        $this->db = ($db ?? Db::get());
        $this->user = $user;
    }

    /**
     * Return a value indicating if there are any changes available to show the user
     *
     * @return int   Changes::NO_CHANGES_EXIST, Changes::CHANGES_EXIST or Changes::NEW_CHANGES_EXIST
     * @throws \Exception
     */
    public function getNewChangesStatus()
    {
        $idchangeLastViewed = (isset($this->user['idchange_last_viewed']) ? $this->user['idchange_last_viewed'] : null);

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

        $res = $this->db->fetchRow($selectSql, $params);
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
     * Return an array of changes and update the user's changes last viewed value
     *
     * @return array
     */
    public function getChanges()
    {
        $changesModel = new ChangesModel(Db::get());
        $changes = $changesModel->getChangeItems();

        // Record the time that changes were viewed for the current user
        $maxId = null;
        foreach ($changes as $k => $change) {
            if ($maxId < $change['idchange']) {
                $maxId = $change['idchange'];
            }
        }

        if ($maxId) {
            $usersModel = new UsersModel();
            $usersModel->updateUserFields($this->user['login'], ['idchange_last_viewed' => $maxId]);
        }

        return $changes;
    }

}
