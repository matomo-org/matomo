<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Changes;

use Piwik\Container\StaticContainer;
use Piwik\Db;
use Piwik\Plugins\UsersManager\Model as UsersModel;

/**
 * CoreHome user changes class
 */
class UserChanges
{

    /**
     * @var Db\AdapterInterface
     */
    private $user;
    private $changesModel;

    /**
     * @param array $user
     */
    public function __construct(array $user)
    {
        $this->user = $user;
        $this->changesModel = StaticContainer::get(\Piwik\Changes\Model::class);
    }

    /**
     * Return a value indicating if there are any changes available to show the user
     *
     * @return int   Changes\Model::NO_CHANGES_EXIST, Changes\Model::CHANGES_EXIST or Changes\Model::NEW_CHANGES_EXIST
     * @throws \Exception
     */
    public function getNewChangesStatus(): int
    {
        return $this->changesModel->doChangesExist($this->getIdchangeLastViewed());
    }

    /**
     * Return the count of new changes unseen by the user
     *
     * @return int   Change count
     * @throws \Exception
     */
    public function getNewChangesCount(): int
    {
        return $this->changesModel->getNewChangesCount($this->getIdchangeLastViewed());
    }

    /**
     * Return the key of the last viewed change for the user, if any
     *
     * @return int
     */
    private function getIdchangeLastViewed(): ?int
    {
        return (isset($this->user['idchange_last_viewed']) ? $this->user['idchange_last_viewed'] : null);
    }

    /**
     * Return an array of changes and update the user's changes last viewed value
     *
     * @return array
     */
    public function getChanges(): array
    {
        $changes = $this->changesModel->getChangeItems();

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
