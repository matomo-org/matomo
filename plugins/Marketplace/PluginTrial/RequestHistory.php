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

    /**
     * @var array<string, string[]|null> the plugins each login has requested, so the Marketplace catalogue checks
     * all of its plugins with one query
     */
    private $requestedPluginsByLogin = [];

    /**
     * Returns false when the login has already requested the plugin: each user's request is recorded once, which the
     * unique key enforces even for two requests made at the same moment.
     */
    public function add(string $pluginName, string $login, int $requestTime): bool
    {
        return $this->write(
            'INSERT INTO ' . $this->getTable() . ' (plugin_name, login, ts_requested) VALUES (?, ?, ?)',
            [$pluginName, $login, Date::factory($requestTime)->getDatetime()]
        );
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
        $this->write(
            'INSERT INTO ' . $this->getTable() . ' (plugin_name, login, ts_requested) SELECT ?, ?, ? FROM DUAL'
            . ' WHERE NOT EXISTS (SELECT 1 FROM ' . $this->getTable() . ' WHERE plugin_name = ? AND ts_requested = ? AND (login <=> ? OR login IS NULL))',
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
