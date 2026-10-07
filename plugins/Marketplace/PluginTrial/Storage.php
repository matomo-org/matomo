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
     * Creates a trial request for the current user
     */
    public function setRequested(string $pluginDisplayName = ''): void
    {
        $requestTime = time();

        $this->storage = [
            'requestTime' => $requestTime,
            'displayName' => $pluginDisplayName,
            'dismissed' => [],
            'requestedBy' => Piwik::getCurrentUserLogin(),
        ];

        $this->writeWithHistory(function (RequestHistory $history) use ($requestTime) {
            // the option first, the same lock order as setFulfilled()
            $this->recordInHistory($history, $this->readStoredForUpdate());
            $this->saveStorage();
            $history->add($this->pluginName, $this->storage['requestedBy'], $requestTime);
        });
    }

    /**
     * Ends the pending trial request because the plugin was installed or activated
     */
    public function setFulfilled(): void
    {
        $this->writeWithHistory(function (RequestHistory $history) {
            $this->recordInHistory($history, $this->readStoredForUpdate());
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
            $storedRequest = $this->readStoredForUpdate();
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
            $storedRequest = $this->readStoredForUpdate();
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
    private function readStoredForUpdate(): array
    {
        $stored = Db::fetchOne(
            'SELECT option_value FROM `' . Common::prefixTable('option') . '` WHERE option_name = ? FOR UPDATE',
            [$this->optionName]
        );
        $storedRequest = json_decode($stored ?: '[]', true);

        return is_array($storedRequest) ? $storedRequest : [];
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
