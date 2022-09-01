<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Changes;

use Piwik\Db;
use Piwik\Changes\Model as ChangesModel;
use Piwik\Plugins\UsersManager\Model as UsersModel;

/**
 * CoreHome user changes class
 */
class UserChanges
{

    /**
     * @var Db\AdapterInterface
     */
    private $db;
    private $user;

    /**
     * @param array $user
     * @param Db\AdapterInterface|null $db
     */
    public function __construct(array $user, ?Db\AdapterInterface $db = null)
    {
        $this->db = ($db ?? Db::get());
        $this->user = $user;
    }

    /**
     * Return a value indicating if there are any changes available to show the user
     *
     * @return int   Changes\Model::NO_CHANGES_EXIST, Changes\Model::CHANGES_EXIST or Changes\Model::NEW_CHANGES_EXIST
     * @throws \Exception
     */
    public function getNewChangesStatus(): int
    {
        $idchangeLastViewed = (isset($this->user['idchange_last_viewed']) ? $this->user['idchange_last_viewed'] : null);

        $changesModel = new ChangesModel($this->db);
        return $changesModel->doChangesExist($idchangeLastViewed);
    }

    /**
     * Return an array of changes and update the user's changes last viewed value
     *
     * @return array
     */
    public function getChanges(): array
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
