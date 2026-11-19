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
     * @return Decision|null null when no request was pending or the 6.1.0-b1 update has not yet run
     */
    public function addDecision(string $pluginName, string $decision, ?string $decidedBy, ?string $note, int $decisionTime): ?Decision
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

            return null;
        }

        if (empty($requestIds)) {
            return null;
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

        return new Decision($idDecision, count($requestIds));
    }

    /**
     * Returns the login's requests with the decision that ended each, newest first
     *
     * @return list<array{plugin_name: string, request_type: string, message: string|null, ts_requested: string,
     *                    decision: string|null, note: string|null}> decision and note are null while pending
     */
    public function getRequestsOfLogin(string $login): array
    {
        return $this->readDecisionAware(
            'SELECT r.plugin_name, r.request_type, r.message, r.ts_requested, d.decision, d.note'
            . ' FROM ' . $this->getTable() . ' r'
            . ' LEFT JOIN ' . Common::prefixTable(self::DECISION_TABLE_NAME) . ' d ON d.iddecision = r.iddecision'
            . ' WHERE r.login = ? ORDER BY r.ts_requested DESC, r.idrequest DESC',
            [$login]
        );
    }

    /**
     * Returns the decisions on the login's requests made after the given one, oldest first
     *
     * @return list<array{plugin_name: string, iddecision: int|string, decision: string}>
     */
    public function getDecisionsOfLogin(string $login, int $afterIdDecision): array
    {
        return $this->readDecisionAware(
            'SELECT r.plugin_name, d.iddecision, d.decision'
            . ' FROM ' . $this->getTable() . ' r'
            . ' JOIN ' . Common::prefixTable(self::DECISION_TABLE_NAME) . ' d ON d.iddecision = r.iddecision'
            . ' WHERE r.login = ? AND r.iddecision > ? ORDER BY r.iddecision',
            [$login, $afterIdDecision]
        );
    }

    /**
     * Returns the newest decision's ID, 0 when none was made yet, or null before the decision table exists
     */
    public function getLastDecisionId(): ?int
    {
        $rows = $this->readDecisionAware(
            'SELECT MAX(iddecision) AS iddecision FROM ' . Common::prefixTable(self::DECISION_TABLE_NAME),
            []
        );

        return $rows === [] ? null : (int) $rows[0]['iddecision'];
    }

    /**
     * Returns all pending requests with the requester's email, by plugin and newest first. A requester deleted since
     * has neither login nor email.
     *
     * @return list<array{plugin_name: string, request_type: string, message: string|null, ts_requested: string,
     *                    login: string|null, email: string|null}>
     */
    public function getPendingRequests(): array
    {
        $requests = $this->readDecisionAware(
            'SELECT plugin_name, request_type, message, ts_requested, login FROM ' . $this->getTable()
            . ' WHERE iddecision IS NULL ORDER BY plugin_name, ts_requested DESC, idrequest DESC',
            []
        );

        // the user table matches logins case-insensitively, so they are looked up the same way here
        $emails = self::keyByLowerCaseLogin($this->getEmailsOfExistingUsers(array_column($requests, 'login')));
        foreach ($requests as &$request) {
            $request['email'] = $request['login'] === null ? null : ($emails[mb_strtolower($request['login'])] ?? null);
        }
        unset($request);

        return $requests;
    }

    /**
     * Returns one page of decisions, newest first
     *
     * @return list<array{plugin_name: string, decision: string, decided_by: string|null, note: string|null,
     *                    num_requests: int|string, ts_decided: string}>
     */
    public function getDecisions(int $offset, int $limit): array
    {
        return $this->readDecisionAware(
            'SELECT plugin_name, decision, decided_by, note, num_requests, ts_decided'
            . ' FROM ' . Common::prefixTable(self::DECISION_TABLE_NAME)
            . ' ORDER BY ts_decided DESC, iddecision DESC LIMIT ' . $offset . ', ' . $limit,
            []
        );
    }

    public function getNumDecisions(): int
    {
        $rows = $this->readDecisionAware('SELECT COUNT(*) AS num FROM ' . Common::prefixTable(self::DECISION_TABLE_NAME), []);

        return (int) ($rows[0]['num'] ?? 0);
    }

    /**
     * Returns the requesters a decision ended requests of who still have an account
     *
     * @return array<string, string> email addresses keyed by login
     */
    public function getRequesterEmails(Decision $decision): array
    {
        return $this->getEmailsOfExistingUsers(array_column(Db::get()->fetchAll(
            'SELECT login FROM ' . $this->getTable() . ' WHERE iddecision = ?',
            [$decision->id]
        ), 'login'));
    }

    /**
     * Looked up apart from this plugin's tables, as comparing their login columns with the user table's fails when
     * the tables were created with different collations
     *
     * @param array<string|null> $logins
     * @return array<string, string> email addresses keyed by login
     */
    private function getEmailsOfExistingUsers(array $logins): array
    {
        $emails = [];
        foreach (array_chunk(array_values(array_unique(array_filter($logins))), 500) as $chunk) {
            $rows = Db::get()->fetchAll(
                'SELECT login, email FROM ' . Common::prefixTable('user')
                . ' WHERE login IN (' . Common::getSqlStringFieldsArray($chunk) . ')',
                $chunk
            );
            foreach ($rows as $row) {
                $emails[$row['login']] = $row['email'];
            }
        }

        return $emails;
    }

    /**
     * @param array<string, string> $emails
     * @return array<string, string>
     */
    private static function keyByLowerCaseLogin(array $emails): array
    {
        $byLowerCaseLogin = [];
        foreach ($emails as $login => $email) {
            // a numeric login comes back as an int key
            $byLowerCaseLogin[mb_strtolower((string) $login)] = $email;
        }

        return $byLowerCaseLogin;
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
            $recordedLogins = array_column(Db::get()->fetchAll(
                'SELECT DISTINCT login FROM ' . $this->getTable() . ' WHERE login IS NOT NULL'
            ), 'login');
        } catch (\Exception $e) {
            if (!Db::get()->isErrNo($e, Migration\Db::ERROR_CODE_TABLE_NOT_EXISTS)) {
                throw $e;
            }

            return [];
        }

        try {
            $recordedLogins = array_merge($recordedLogins, array_column(Db::get()->fetchAll(
                'SELECT DISTINCT decided_by FROM ' . Common::prefixTable(self::DECISION_TABLE_NAME) . ' WHERE decided_by IS NOT NULL'
            ), 'decided_by'));
        } catch (\Exception $e) {
            if (!Db::get()->isErrNo($e, Migration\Db::ERROR_CODE_TABLE_NOT_EXISTS)) {
                throw $e;
            }
        }

        $recordedLogins = array_values(array_unique($recordedLogins));
        // the user table can match a login case-insensitively, and a login it still has must never be anonymized
        $existingLogins = self::keyByLowerCaseLogin($this->getEmailsOfExistingUsers($recordedLogins));

        return array_values(array_filter($recordedLogins, function (string $login) use ($existingLogins): bool {
            return !isset($existingLogins[mb_strtolower($login)]);
        }));
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

    /**
     * Reads nothing until the 6.1.0-b1 update has added the decisions
     *
     * @param list<mixed> $bind
     * @return list<array<string, mixed>>
     */
    private function readDecisionAware(string $sql, array $bind): array
    {
        try {
            return Db::get()->fetchAll($sql, $bind);
        } catch (\Exception $e) {
            if (
                !Db::get()->isErrNo($e, Migration\Db::ERROR_CODE_TABLE_NOT_EXISTS)
                && !Db::get()->isErrNo($e, Migration\Db::ERROR_CODE_UNKNOWN_COLUMN)
            ) {
                throw $e;
            }

            return [];
        }
    }

    private function getTable(): string
    {
        return Common::prefixTable(self::TABLE_NAME);
    }
}
