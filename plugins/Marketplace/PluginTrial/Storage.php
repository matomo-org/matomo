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
use Piwik\Option;
use Piwik\Piwik;
use Piwik\Plugin\Manager;

class Storage
{
    /** the user had already requested the plugin, so nothing was recorded */
    public const REQUEST_ALREADY_RECORDED = 'already_recorded';
    /** recorded for a plugin that another user's request had already made pending */
    public const REQUEST_ADDED_TO_PENDING = 'added_to_pending';
    /** recorded and made the plugin's request pending */
    public const REQUEST_PENDING = 'pending';

    private const OPTION_NAME = 'Marketplace.PluginTrialRequest.%s';
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
     * super users are notified once per plugin and keep their dismissals.
     *
     * @return string one of the REQUEST_* constants
     */
    public function setRequested(string $pluginDisplayName = ''): string
    {
        $requestTime = time();
        $login = Piwik::getCurrentUserLogin();
        $result = self::REQUEST_ALREADY_RECORDED;

        $this->writeWithHistory(function (RequestHistory $history) use ($pluginDisplayName, $requestTime, $login, &$result) {
            $this->recordInHistory($history, $this->readStored());

            if (!$history->add($this->pluginName, $login, $requestTime)) {
                return;
            }

            $request = [
                'requestTime' => $requestTime,
                'displayName' => $pluginDisplayName,
                'dismissed' => [],
                'requestedBy' => $login,
            ];

            if ($this->insertStorage($request)) {
                $this->storage = $request;
                $result = self::REQUEST_PENDING;
                return;
            }

            // a locking read, as the plain one above does not see a request made pending since it ran
            $this->storage = $this->readStored(true);
            $result = self::REQUEST_ADDED_TO_PENDING;
        });

        return $result;
    }

    /**
     * Ends the pending trial request because the plugin was installed or activated
     */
    public function setFulfilled(): void
    {
        $this->writeWithHistory(function (RequestHistory $history) {
            $this->recordInHistory($history, $this->readStored(true));
            $this->clearStorage();
        });
    }

    /**
     * Returns if the plugin has a pending trial request from any user
     */
    public function wasRequested(): bool
    {
        return !empty($this->storage);
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
            $storedRequest = $this->readStored(true);
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
     * Returns if the current user has dismissed the trial request
     */
    public function isNotificationDismissed(): bool
    {
        return !empty($this->storage['dismissed']) && in_array(Piwik::getCurrentUserLogin(), $this->storage['dismissed']);
    }

    /**
     * @return string[] the requester and the users who dismissed the notification
     */
    public function getLogins(): array
    {
        return array_values(array_filter(array_merge([$this->storage['requestedBy'] ?? null], $this->storage['dismissed'] ?? [])));
    }

    /**
     * Removes a deleted user's login from the pending request
     */
    public function anonymizeLogin(string $login): void
    {
        $this->writeWithHistory(function () use ($login) {
            $storedRequest = $this->readStored(true);
            if (empty($storedRequest)) {
                return;
            }

            $dismissed = $storedRequest['dismissed'] ?? [];
            $remainingDismissed = array_values(array_diff($dismissed, [$login]));
            $wasRequester = ($storedRequest['requestedBy'] ?? null) === $login;

            if (!$wasRequester && count($remainingDismissed) === count($dismissed)) {
                return;
            }

            if ($wasRequester) {
                $storedRequest['requestedBy'] = null;
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
    private function readStored(bool $forUpdate = false): array
    {
        $stored = Db::fetchOne(
            'SELECT option_value FROM `' . Common::prefixTable('option') . '` WHERE option_name = ?' . ($forUpdate ? ' FOR UPDATE' : ''),
            [$this->optionName]
        );
        $storedRequest = json_decode($stored ?: '[]', true);

        return is_array($storedRequest) ? $storedRequest : [];
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
            $plugins[str_replace(sprintf(self::OPTION_NAME, ''), '', $trialRequest)] = $data['requestTime'];
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
