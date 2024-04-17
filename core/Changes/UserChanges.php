<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Changes;

use Piwik\Container\StaticContainer;
use Piwik\Date;
use Piwik\Plugins\UsersManager\Model as UsersModel;

/**
 * CoreHome user changes class
 */
class UserChanges
{
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
        return (isset($this->user['idchange_last_viewed']) ? intval($this->user['idchange_last_viewed']) : null);
    }

    /**
     * Return true if the changes popup is snoozed
     *
     * @return bool If changes were shown to the user in the last 24hrs then this will be true, otherwise false
     */
    public function shownRecently(): bool
    {
        $lastShown = (isset($this->user['ts_changes_shown']) ? $this->user['ts_changes_shown'] : null);
        if (!$lastShown) {
            return false; // Never shown
        }
        // Less than 24hrs since last shown
        return ((Date::factory('now')->getTimestamp() - Date::factory($lastShown)->getTimestamp()) < 86400);
    }

    /**
     * Return an array of changes and update the user's popup last shown timestamp
     *
     * @return array
     */
    public function getChanges(): array
    {
        $usersModel = new UsersModel();
        $usersModel->updateUserFields($this->user['login'], ['ts_changes_shown' => Date::now()->getDatetime()]);

        return $this->changesModel->getChangeItems();
    }

    /**
     * Record all changes as read
     *
     * @return void
     * @throws \Piwik\Tracker\Db\DbException
     */
    public function markChangesAsRead(): void
    {
        $changes =  $this->changesModel->getChangeItems();

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
    }
}
