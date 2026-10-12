<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Marketplace\PluginTrial;

use Exception;
use Piwik\Common;
use Piwik\Container\StaticContainer;
use Piwik\Db;
use Piwik\Log\LoggerInterface;
use Piwik\Option;
use Piwik\Piwik;
use Piwik\Plugin\Manager;
use Piwik\Plugins\UsersManager\API as UsersManagerApi;

class Storage
{
    /** the user had already requested the plugin, so nothing was recorded */
    public const REQUEST_ALREADY_RECORDED = 'already_recorded';
    /** recorded for a plugin that another user's request had already made pending */
    public const REQUEST_ADDED_TO_PENDING = 'added_to_pending';
    /** recorded and made the plugin's request pending */
    public const REQUEST_PENDING = 'pending';

    private const OPTION_NAME = 'Marketplace.PluginTrialRequest.%s';
    private const LOCK_FOR_UPDATE = ' FOR UPDATE';
    private const LOCK_SHARED = ' LOCK IN SHARE MODE';
    private $pluginName;
    private $optionName;
    private $storage = [];

    public function __construct(string $pluginName)
    {
        if (!Manager::getInstance()->isValidPluginName($pluginName)) {
            throw new Exception('Invalid plugin name given ' . $pluginName);
        }

        $this->pluginName = $pluginName;
        $this->optionName = sprintf(self::OPTION_NAME, $pluginName);
        $this->loadStorage();
    }

    /**
     * Records a trial request by the current user. Only a request made while none is pending makes one pending, so
     * super users are notified once per plugin and keep their dismissals. Each request that is recorded adds the
     * current super users to the ones notified.
     *
     * @return string one of the REQUEST_* constants
     */
    public function setRequested(string $pluginDisplayName = ''): string
    {
        $requestTime = time();
        $login = Piwik::getCurrentUserLogin();
        $superUsers = array_column(UsersManagerApi::getInstance()->getUsersHavingSuperUserAccess(), 'login');
        $result = self::REQUEST_ALREADY_RECORDED;

        $this->writeWithHistory(function (RequestHistory $history) use ($pluginDisplayName, $requestTime, $login, $superUsers, &$result) {
            $storedValue = $this->readStoredValue();
            $storedRequest = $this->decodeStored($storedValue);
            $this->recordInHistory($history, $storedRequest);

            // Matomo before the update saves a dismissal without the request when the request was cleared since it was
            // loaded; such a row is no request, but would make insertStorage() treat one as pending
            if ($storedValue !== false && empty($storedRequest['requestTime'])) {
                $this->deleteStoredWithoutRequest();
            }

            if (!$history->add($this->pluginName, $login, $requestTime)) {
                return;
            }

            $request = [
                'requestTime' => $requestTime,
                'displayName' => $pluginDisplayName,
                'dismissed' => [],
                'requestedBy' => $login,
                'superUsers' => $superUsers,
            ];

            if ($this->insertStorage($request)) {
                $this->storage = $request;
                $result = self::REQUEST_PENDING;
                return;
            }

            // a locking read, as the plain one above does not see a request made pending since it ran. Shared, as the
            // failed insert already holds a shared lock on the row, and upgrading it would deadlock a concurrent request
            $this->storage = $this->readStored(self::LOCK_SHARED);
            $result = self::REQUEST_ADDED_TO_PENDING;
        });

        if ($result === self::REQUEST_ADDED_TO_PENDING) {
            try {
                $this->addSuperUsers($superUsers, (int) ($this->storage['requestTime'] ?? 0));
            } catch (\Throwable $e) {
                // the request is already committed, so failing here would report an error for a saved request
                StaticContainer::get(LoggerInterface::class)->warning(
                    'Could not add the current super users to a pending plugin trial request',
                    ['exception' => $e]
                );
            }
        }

        return $result;
    }

    /**
     * Super users granted access since the request became pending are notified of a later request. This is its own
     * transaction: the request's holds a shared lock on the row, and two requests both writing under it would deadlock.
     *
     * @param string[] $superUsers
     */
    private function addSuperUsers(array $superUsers, int $pendingRequestTime): void
    {
        $this->writeWithHistory(function () use ($superUsers, $pendingRequestTime) {
            $storedRequest = $this->readStored(self::LOCK_FOR_UPDATE);

            // one stored without the list already notifies every super user, and one fulfilled or replaced since is
            // not the request these users were added to
            if (!isset($storedRequest['superUsers']) || (int) ($storedRequest['requestTime'] ?? 0) !== $pendingRequestTime) {
                return;
            }

            $mergedSuperUsers = array_values(array_unique(array_merge($storedRequest['superUsers'], $superUsers)));
            if ($mergedSuperUsers === $storedRequest['superUsers']) {
                return;
            }

            $storedRequest['superUsers'] = $mergedSuperUsers;
            $this->storage = $storedRequest;
            $this->saveStorage();
        });
    }

    /**
     * Ends the pending trial request because the plugin was installed or activated
     */
    public function setFulfilled(): void
    {
        $this->writeWithHistory(function (RequestHistory $history) {
            $this->recordInHistory($history, $this->readStored(self::LOCK_FOR_UPDATE));
            $this->clearStorage();
        });
    }

    /**
     * Returns if the plugin has a pending trial request from any user. A row without a request time is a dismissal
     * Matomo before the update saved after the request was cleared, so it is none.
     */
    public function wasRequested(): bool
    {
        return !empty($this->storage['requestTime']);
    }

    /**
     * Returns if the current user has ever requested a trial of the plugin. Requests are permanent, so this stays true
     * after the plugin was installed, and after a trial or subscription for it ended.
     */
    public function wasRequestedByCurrentUser(): bool
    {
        // also covers a request that Matomo before the update stored in the option alone
        if (($this->storage['requestedBy'] ?? null) === Piwik::getCurrentUserLogin()) {
            return true;
        }

        $hasRequested = StaticContainer::get(RequestHistory::class)->hasRequested($this->pluginName, Piwik::getCurrentUserLogin());

        // until the update creates the history table, a pending request blocks every user, as it did before
        return $hasRequested ?? $this->wasRequested();
    }

    /**
     * Dismisses the trial request for the current user
     */
    public function setNotificationDismissed(): void
    {
        $this->writeWithHistory(function () {
            $storedRequest = $this->readStored(self::LOCK_FOR_UPDATE);
            if (empty($storedRequest)) {
                return;
            }

            $storedRequest['dismissed'][] = Piwik::getCurrentUserLogin();
            $this->storage = $storedRequest;
            $this->saveStorage();
        });
    }

    /**
     * Returns the display name for the plugin stored when requesting the trial
     */
    public function getDisplayName(): string
    {
        return $this->storage['displayName'] ?: $this->pluginName;
    }

    /**
     * Returns if the current user has dismissed the trial request, or was not yet a super user when it was made
     */
    public function isNotificationDismissed(): bool
    {
        $login = Piwik::getCurrentUserLogin();

        // a request stored before super users were recorded with it has no list, and is shown to every super user, as is
        // one whose recorded super users were all deleted or demoted since, so it is never hidden from everyone
        if (
            isset($this->storage['superUsers'])
            && !in_array($login, $this->storage['superUsers'], true)
            && $this->isARecordedSuperUserStillOne()
        ) {
            return true;
        }

        return !empty($this->storage['dismissed']) && in_array($login, $this->storage['dismissed'], true);
    }

    private function isARecordedSuperUserStillOne(): bool
    {
        $superUsers = array_column(UsersManagerApi::getInstance()->getUsersHavingSuperUserAccess(), 'login');

        return !empty(array_intersect($this->storage['superUsers'], $superUsers));
    }

    /**
     * @return string[] the requester, the super users at the time of the request and the users who dismissed the notification
     */
    public function getLogins(): array
    {
        return array_values(array_unique(array_filter(array_merge(
            [$this->storage['requestedBy'] ?? null],
            $this->storage['superUsers'] ?? [],
            $this->storage['dismissed'] ?? []
        ))));
    }

    /**
     * Removes a deleted user's login from the pending request
     */
    public function anonymizeLogin(string $login): void
    {
        $this->writeWithHistory(function () use ($login) {
            $storedRequest = $this->readStored(self::LOCK_FOR_UPDATE);
            if (empty($storedRequest)) {
                return;
            }

            $dismissed = $storedRequest['dismissed'] ?? [];
            $remainingDismissed = array_values(array_diff($dismissed, [$login]));
            $wasRequester = ($storedRequest['requestedBy'] ?? null) === $login;
            $wasSuperUser = in_array($login, $storedRequest['superUsers'] ?? [], true);

            if (!$wasRequester && !$wasSuperUser && count($remainingDismissed) === count($dismissed)) {
                return;
            }

            if ($wasRequester) {
                $storedRequest['requestedBy'] = null;
            }

            if ($wasSuperUser) {
                $storedRequest['superUsers'] = array_values(array_diff($storedRequest['superUsers'], [$login]));
            }

            $storedRequest['dismissed'] = $remainingDismissed;
            $this->storage = $storedRequest;
            $this->saveStorage();
        });
    }

    /**
     * Writers re-read the request under lock, so one fulfilled, replaced or anonymised since this object was
     * loaded is not written back.
     *
     * @return array<string, mixed>
     */
    private function readStored(string $lock = ''): array
    {
        return $this->decodeStored($this->readStoredValue($lock));
    }

    /**
     * @param string|false $storedValue
     * @return array<string, mixed>
     */
    private function decodeStored($storedValue): array
    {
        $storedRequest = json_decode($storedValue ?: '[]', true);

        return is_array($storedRequest) ? $storedRequest : [];
    }

    /**
     * @return string|false false when no request is stored
     */
    private function readStoredValue(string $lock = '')
    {
        return Db::fetchOne(
            'SELECT option_value FROM `' . Common::prefixTable('option') . '` WHERE option_name = ?' . $lock,
            [$this->optionName]
        );
    }

    /**
     * Unlike Option::set(), which runs an UPDATE first, this takes no gap lock that a simultaneous first request for
     * another plugin could deadlock against.
     *
     * @param array<string, mixed> $request
     * @return bool false when a request is already pending
     */
    protected function insertStorage(array $request): bool
    {
        $result = Db::query(
            'INSERT IGNORE INTO `' . Common::prefixTable('option') . '` (option_name, option_value, autoload) VALUES (?, ?, 0)',
            [$this->optionName, json_encode($request)]
        );
        Option::clearCachedOption($this->optionName);

        return Db::get()->rowCount($result) > 0;
    }

    /**
     * Re-reads under lock, so a request made since is kept, and a dismissal Matomo before the update added since is
     * deleted too. No shared lock is held on the row yet, so taking an exclusive one cannot deadlock.
     */
    private function deleteStoredWithoutRequest(): void
    {
        if (!empty($this->readStored(self::LOCK_FOR_UPDATE)['requestTime'])) {
            return;
        }

        Db::query('DELETE FROM `' . Common::prefixTable('option') . '` WHERE option_name = ?', [$this->optionName]);
        Option::clearCachedOption($this->optionName);
    }

    /**
     * @param array<string, mixed> $storedRequest
     */
    private function recordInHistory(RequestHistory $history, array $storedRequest): void
    {
        if (!empty($storedRequest['requestTime'])) {
            $history->addIfMissing($this->pluginName, $storedRequest['requestedBy'] ?? null, (int) $storedRequest['requestTime']);
        }
    }

    /**
     * Removes the trial request from storage
     */
    public function clearStorage(): void
    {
        Option::delete($this->optionName);
    }

    /**
     * Returns the names of plugins where trial requests are stored for, sorted by request time descending
     *
     * @return array
     */
    public static function getPluginsInStorage(): array
    {
        $plugins = [];
        $trialRequests = Option::getLike(sprintf(self::OPTION_NAME, '%'));

        foreach ($trialRequests as $trialRequest => $data) {
            $data = json_decode($data, true);
            $plugins[str_replace(sprintf(self::OPTION_NAME, ''), '', $trialRequest)] = $data['requestTime'] ?? 0;
        }

        arsort($plugins);

        return array_keys($plugins);
    }

    protected function loadStorage(): void
    {
        $this->storage = json_decode(Option::get($this->optionName) ?: '[]', true);
    }

    /**
     * Keeps the history in step with the option: an exception from either write rolls back both.
     */
    private function writeWithHistory(callable $write): void
    {
        $db = Db::get();
        if (!$db instanceof \Zend_Db_Adapter_Abstract) {
            throw new Exception('Trial requests can only be written through the regular database connection.');
        }

        $db->beginTransaction();

        try {
            $write(StaticContainer::get(RequestHistory::class));
            $db->commit();
        } catch (\Throwable $e) {
            try {
                $db->rollBack();
            } catch (\Throwable $rollbackError) {
                // a dropped connection has already discarded the transaction; report what caused the failure
            }
            Option::clearCachedOption($this->optionName);
            $this->loadStorage();

            throw $e;
        }
    }

    protected function saveStorage(): void
    {
        Option::set($this->optionName, json_encode($this->storage));
    }
}
