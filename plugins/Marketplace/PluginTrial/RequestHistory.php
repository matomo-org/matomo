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
     * keeps serving alongside the updated code. A row stored with a NULL login, as the update does for a requester
     * deleted since, counts as that request.
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
            . ' WHERE NOT EXISTS (SELECT 1 FROM ' . $this->getTable() . ' WHERE plugin_name = ? AND ts_requested = ? AND (login <=> ? OR login IS NULL))',
            [$pluginName, $login, $requestedAt, $pluginName, $requestedAt, $login]
        );
    }

    /**
     * Marks every open request for the plugin as ended by the plugin being installed or activated, not only the
     * option's: concurrent requests can each add a row, while the option keeps only one of them.
     */
    public function markFulfilled(string $pluginName): void
    {
        $this->write(
            'UPDATE ' . $this->getTable() . ' SET ts_fulfilled = ? WHERE plugin_name = ? AND ts_fulfilled IS NULL',
            [Date::now()->getDatetime(), $pluginName]
        );
    }

    /**
     * @return array<int, array{plugin_name: string, login: string|null, ts_requested: string, ts_fulfilled: string|null}> newest first
     */
    public function getRequests(string $pluginName): array
    {
        return Db::get()->fetchAll(
            'SELECT plugin_name, login, ts_requested, ts_fulfilled FROM ' . $this->getTable()
            . ' WHERE plugin_name = ? ORDER BY ts_requested DESC, idrequest DESC',
            [$pluginName]
        );
    }

    /**
     * Returns whether the login has ever requested the plugin, fulfilled or not, or null while the 6.0.0-b6 update has
     * not yet created the table.
     */
    public function hasRequested(string $pluginName, string $login): ?bool
    {
        try {
            return (bool) Db::get()->fetchOne(
                'SELECT 1 FROM ' . $this->getTable() . ' WHERE login = ? AND plugin_name = ? LIMIT 1',
                [$login, $pluginName]
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
     * @return string[] logins still recorded for users who no longer exist
     */
    public function getDeletedLogins(): array
    {
        try {
            return array_column(Db::get()->fetchAll(
                'SELECT DISTINCT login FROM ' . $this->getTable() . ' WHERE login IS NOT NULL'
                . ' AND login NOT IN (SELECT login FROM ' . Common::prefixTable('user') . ')'
            ), 'login');
        } catch (\Exception $e) {
            if (!Db::get()->isErrNo($e, Migration\Db::ERROR_CODE_TABLE_NOT_EXISTS)) {
                throw $e;
            }

            return [];
        }
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
