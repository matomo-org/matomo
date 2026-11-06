<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Marketplace\PluginTrial;

use Piwik\Common;
use Piwik\Date;
use Piwik\Db;
use Piwik\Updater\Migration;

/**
 * Keeps every plugin request and the decisions ending them, unlike Storage, which only holds the one currently pending per plugin.
 */
class RequestHistory
{
    public const TABLE_NAME = 'plugin_trial_request';
    public const DECISION_TABLE_NAME = 'plugin_request_decision';

    public const REQUEST_TYPE_TRIAL = 'trial';
    public const REQUEST_TYPE_INSTALL = 'install';

    public const DECISION_APPROVED = 'approved';
    public const DECISION_DECLINED = 'declined';

    /**
     * @var array<string, string[]|null> the plugins each login has requested, so the Marketplace catalogue checks
     * all of its plugins with one query
     */
    private $requestedPluginsByLogin = [];

    /**
     * Returns false when the login has already requested the plugin: each user's request is recorded once, which the
     * unique key enforces even for two requests made at the same moment.
     */
    public function add(
        string $pluginName,
        string $login,
        int $requestTime,
        string $requestType = self::REQUEST_TYPE_TRIAL,
        ?string $message = null
    ): bool {
        $requestedAt = Date::factory($requestTime)->getDatetime();

        try {
            return $this->write(
                'INSERT INTO ' . $this->getTable() . ' (plugin_name, login, ts_requested, request_type, message) VALUES (?, ?, ?, ?, ?)',
                [$pluginName, $login, $requestedAt, $requestType, $message]
            );
        } catch (\Exception $e) {
            if (!Db::get()->isErrNo($e, Migration\Db::ERROR_CODE_UNKNOWN_COLUMN)) {
                throw $e;
            }
        }

        // until the 6.1.0-b1 update adds the columns, a request is stored as every request was before
        return $this->write(
            'INSERT INTO ' . $this->getTable() . ' (plugin_name, login, ts_requested) VALUES (?, ?, ?)',
            [$pluginName, $login, $requestedAt]
        );
    }

    /**
     * Ends all of the plugin's pending requests with one decision. Run inside the transaction of the pending option's
     * row lock, so two decisions for the same plugin wait for each other and the second finds nothing left to decide.
     *
     * @param string $decision one of the DECISION_* constants
     * @param string|null $decidedBy the deciding super user, or null for a decision Matomo made itself
     * @return int the number of requests decided, 0 when none was pending or the 6.1.0-b1 update has not yet run
     */
    public function addDecision(string $pluginName, string $decision, ?string $decidedBy, ?string $note, int $decisionTime): int
    {
        $this->requestedPluginsByLogin = [];

        try {
            $requestIds = array_column(Db::get()->fetchAll(
                'SELECT idrequest FROM ' . $this->getTable() . ' WHERE plugin_name = ? AND iddecision IS NULL FOR UPDATE',
                [$pluginName]
            ), 'idrequest');
        } catch (\Exception $e) {
            if (
                !Db::get()->isErrNo($e, Migration\Db::ERROR_CODE_TABLE_NOT_EXISTS)
                && !Db::get()->isErrNo($e, Migration\Db::ERROR_CODE_UNKNOWN_COLUMN)
            ) {
                throw $e;
            }

            return 0;
        }

        if (empty($requestIds)) {
            return 0;
        }

        Db::get()->query(
            'INSERT INTO ' . Common::prefixTable(self::DECISION_TABLE_NAME)
            . ' (plugin_name, decision, decided_by, note, num_requests, ts_decided) VALUES (?, ?, ?, ?, ?, ?)',
            [$pluginName, $decision, $decidedBy, $note, count($requestIds), Date::factory($decisionTime)->getDatetime()]
        );
        $idDecision = (int) Db::get()->lastInsertId();

        Db::get()->query(
            'UPDATE ' . $this->getTable() . ' SET iddecision = ? WHERE idrequest IN (' . Common::getSqlStringFieldsArray($requestIds) . ')',
            array_merge([$idDecision], $requestIds)
        );

        return count($requestIds);
    }

    /**
     * Records a request that only the option holds, as one stored by Matomo before the update still does while it
     * keeps serving alongside the updated code. A row stored with a NULL login, as the update does for a requester
     * deleted since, counts as that request, as does an earlier request by the same login.
     */
    public function addIfMissing(string $pluginName, ?string $login, int $requestTime): void
    {
        // Date::factory() throws for anything this old
        if ($requestTime < Date::FIRST_WEBSITE_TIMESTAMP) {
            return;
        }

        $requestedAt = Date::factory($requestTime)->getDatetime();
        $matchesRequest = ' WHERE plugin_name = ? AND ts_requested = ? AND (login <=> ? OR login IS NULL)';

        // the INSERT ... SELECT below takes shared next-key locks, which two users' simultaneous requests for a pending
        // plugin would deadlock on, so a plain read first skips it in the usual case of an already recorded request
        try {
            if (Db::fetchOne('SELECT 1 FROM ' . $this->getTable() . $matchesRequest, [$pluginName, $requestedAt, $login])) {
                return;
            }
        } catch (\Exception $e) {
            if (!Db::get()->isErrNo($e, Migration\Db::ERROR_CODE_TABLE_NOT_EXISTS)) {
                throw $e;
            }

            return;
        }

        $this->write(
            'INSERT INTO ' . $this->getTable() . ' (plugin_name, login, ts_requested) SELECT ?, ?, ? FROM DUAL'
            . ' WHERE NOT EXISTS (SELECT 1 FROM ' . $this->getTable() . $matchesRequest . ')',
            [$pluginName, $login, $requestedAt, $pluginName, $requestedAt, $login]
        );
    }

    /**
     * @return array<int, array{plugin_name: string, login: string|null, ts_requested: string}> newest first
     */
    public function getRequests(string $pluginName): array
    {
        return Db::get()->fetchAll(
            'SELECT plugin_name, login, ts_requested FROM ' . $this->getTable()
            . ' WHERE plugin_name = ? ORDER BY ts_requested DESC, idrequest DESC',
            [$pluginName]
        );
    }

    /**
     * Returns whether the login has ever requested the plugin, or null while the 6.0.0-b6 update has not yet created
     * the table.
     */
    public function hasRequested(string $pluginName, string $login): ?bool
    {
        if (!array_key_exists($login, $this->requestedPluginsByLogin)) {
            $this->requestedPluginsByLogin[$login] = $this->fetchRequestedPlugins($login);
        }

        $requestedPlugins = $this->requestedPluginsByLogin[$login];

        return $requestedPlugins === null ? null : in_array($pluginName, $requestedPlugins, true);
    }

    /**
     * Returns whether any of the plugin's requests has not been decided yet, false until the 6.1.0-b1 update has run
     */
    public function hasPendingRequests(string $pluginName): bool
    {
        try {
            return (bool) Db::fetchOne(
                'SELECT 1 FROM ' . $this->getTable() . ' WHERE plugin_name = ? AND iddecision IS NULL LIMIT 1',
                [$pluginName]
            );
        } catch (\Exception $e) {
            if (
                !Db::get()->isErrNo($e, Migration\Db::ERROR_CODE_TABLE_NOT_EXISTS)
                && !Db::get()->isErrNo($e, Migration\Db::ERROR_CODE_UNKNOWN_COLUMN)
            ) {
                throw $e;
            }

            return false;
        }
    }

    /**
     * Keeps the requests and decisions so they still count, but forgets who made them.
     */
    public function anonymizeLogin(string $login): void
    {
        $this->write('UPDATE ' . $this->getTable() . ' SET login = NULL WHERE login = ?', [$login]);
        $this->write('UPDATE ' . Common::prefixTable(self::DECISION_TABLE_NAME) . ' SET decided_by = NULL WHERE decided_by = ?', [$login]);
    }

    /**
     * @return string[] logins still recorded for users who no longer exist
     */
    public function getDeletedLogins(): array
    {
        try {
            $deletedLogins = array_column(Db::get()->fetchAll(
                'SELECT DISTINCT login FROM ' . $this->getTable() . ' WHERE login IS NOT NULL'
                . ' AND login NOT IN (SELECT login FROM ' . Common::prefixTable('user') . ')'
            ), 'login');
        } catch (\Exception $e) {
            if (!Db::get()->isErrNo($e, Migration\Db::ERROR_CODE_TABLE_NOT_EXISTS)) {
                throw $e;
            }

            return [];
        }

        try {
            $deletedDeciders = array_column(Db::get()->fetchAll(
                'SELECT DISTINCT decided_by FROM ' . Common::prefixTable(self::DECISION_TABLE_NAME) . ' WHERE decided_by IS NOT NULL'
                . ' AND decided_by NOT IN (SELECT login FROM ' . Common::prefixTable('user') . ')'
            ), 'decided_by');
        } catch (\Exception $e) {
            if (!Db::get()->isErrNo($e, Migration\Db::ERROR_CODE_TABLE_NOT_EXISTS)) {
                throw $e;
            }

            $deletedDeciders = [];
        }

        return array_values(array_unique(array_merge($deletedLogins, $deletedDeciders)));
    }

    /**
     * Skips the write while the 6.0.0-b6 update has not yet created the table, so trial requests keep working when the
     * new code is deployed before core:update runs.
     *
     * @param list<mixed> $bind
     * @return bool false when the row duplicates an existing one
     */
    private function write(string $sql, array $bind): bool
    {
        $this->requestedPluginsByLogin = [];

        try {
            Db::get()->query($sql, $bind);
        } catch (\Exception $e) {
            if (Db::get()->isErrNo($e, Migration\Db::ERROR_CODE_DUPLICATE_ENTRY)) {
                return false;
            }

            if (!Db::get()->isErrNo($e, Migration\Db::ERROR_CODE_TABLE_NOT_EXISTS)) {
                throw $e;
            }
        }

        return true;
    }

    /**
     * @return string[]|null null while the 6.0.0-b6 update has not yet created the table
     */
    private function fetchRequestedPlugins(string $login): ?array
    {
        try {
            return array_column(Db::get()->fetchAll(
                'SELECT DISTINCT plugin_name FROM ' . $this->getTable() . ' WHERE login = ?',
                [$login]
            ), 'plugin_name');
        } catch (\Exception $e) {
            if (!Db::get()->isErrNo($e, Migration\Db::ERROR_CODE_TABLE_NOT_EXISTS)) {
                throw $e;
            }

            return null;
        }
    }

    private function getTable(): string
    {
        return Common::prefixTable(self::TABLE_NAME);
    }
}
