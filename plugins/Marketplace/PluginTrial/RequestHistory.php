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
 * Keeps every trial request, unlike Storage, which only holds the one currently pending per plugin.
 */
class RequestHistory
{
    public const TABLE_NAME = 'plugin_trial_request';

    public function add(string $pluginName, string $login, int $requestTime): void
    {
        $this->write(
            'INSERT INTO ' . $this->getTable() . ' (plugin_name, login, ts_requested) VALUES (?, ?, ?)',
            [$pluginName, $login, Date::factory($requestTime)->getDatetime()]
        );
    }

    /**
     * Records a request that only the option holds, as one stored by Matomo before the update still does while it
     * keeps serving alongside the updated code.
     */
    public function addIfMissing(string $pluginName, ?string $login, int $requestTime): void
    {
        // Date::factory() throws for anything this old
        if ($requestTime < Date::FIRST_WEBSITE_TIMESTAMP) {
            return;
        }

        $requestedAt = Date::factory($requestTime)->getDatetime();
        $this->write(
            'INSERT INTO ' . $this->getTable() . ' (plugin_name, login, ts_requested) SELECT ?, ?, ? FROM DUAL'
            . ' WHERE NOT EXISTS (SELECT 1 FROM ' . $this->getTable() . ' WHERE plugin_name = ? AND ts_requested = ? AND login <=> ?)',
            [$pluginName, $login, $requestedAt, $pluginName, $requestedAt, $login]
        );
    }

    /**
     * Marks the request as ended by the plugin being installed or activated.
     */
    public function markFulfilled(string $pluginName): void
    {
        $this->markEnded('ts_fulfilled', $pluginName);
    }

    /**
     * Marks the request as having lapsed without the plugin being installed or activated.
     */
    public function markExpired(string $pluginName): void
    {
        $this->markEnded('ts_expired', $pluginName);
    }

    /**
     * @return array<int, array{plugin_name: string, login: string|null, ts_requested: string, ts_fulfilled: string|null, ts_expired: string|null}> newest first
     */
    public function getRequests(string $pluginName): array
    {
        return Db::get()->fetchAll(
            'SELECT plugin_name, login, ts_requested, ts_fulfilled, ts_expired FROM ' . $this->getTable()
            . ' WHERE plugin_name = ? ORDER BY ts_requested DESC, idrequest DESC',
            [$pluginName]
        );
    }

    /**
     * Returns whether the login has a request for the plugin that is still open and was made at or after $since, or
     * null while the 6.0.0-b6 update has not yet created the table.
     */
    public function hasOpenRequest(string $pluginName, string $login, int $since): ?bool
    {
        try {
            return (bool) Db::get()->fetchOne(
                'SELECT 1 FROM ' . $this->getTable() . ' WHERE login = ? AND plugin_name = ? AND ts_requested >= ?'
                . ' AND ts_fulfilled IS NULL AND ts_expired IS NULL LIMIT 1',
                // Date::factory() throws for anything this old, which a very long expiry setting reaches
                [$login, $pluginName, Date::factory(max($since, Date::FIRST_WEBSITE_TIMESTAMP))->getDatetime()]
            );
        } catch (\Exception $e) {
            if (!Db::get()->isErrNo($e, Migration\Db::ERROR_CODE_TABLE_NOT_EXISTS)) {
                throw $e;
            }

            return null;
        }
    }

    /**
     * Keeps the requests so they still count, but forgets who made them.
     */
    public function anonymizeLogin(string $login): void
    {
        $this->write('UPDATE ' . $this->getTable() . ' SET login = NULL WHERE login = ?', [$login]);
    }

    /**
     * Ends every open request for the plugin, not only the option's: concurrent requests can each add a row, while the
     * option keeps only one of them.
     *
     * @param 'ts_fulfilled'|'ts_expired' $column
     */
    private function markEnded(string $column, string $pluginName): void
    {
        $this->write(
            'UPDATE ' . $this->getTable() . " SET $column = ? WHERE plugin_name = ? AND ts_fulfilled IS NULL AND ts_expired IS NULL",
            [Date::now()->getDatetime(), $pluginName]
        );
    }

    /**
     * Skips the write while the 6.0.0-b6 update has not yet created the table, so trial requests keep working when the
     * new code is deployed before core:update runs.
     *
     * @param list<mixed> $bind
     */
    private function write(string $sql, array $bind): void
    {
        try {
            Db::get()->query($sql, $bind);
        } catch (\Exception $e) {
            if (!Db::get()->isErrNo($e, Migration\Db::ERROR_CODE_TABLE_NOT_EXISTS)) {
                throw $e;
            }
        }
    }

    private function getTable(): string
    {
        return Common::prefixTable(self::TABLE_NAME);
    }
}
