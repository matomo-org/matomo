<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ExampleLogTables\Dao;

use Piwik\Common;
use Piwik\Db;
use Piwik\DbHelper;

/**
 * Owns the user table: its schema, and reading and writing one row per user.
 *
 * The DAO owns the schema, not the Dimension. `DbHelper::createTable()` writes
 * `CREATE TABLE IF NOT EXISTS`, applies the engine, charset and collation the install uses, and
 * swallows the "table exists" error, so `install()` is safe to call twice.
 */
class CustomUserLog
{
    /**
     * The unprefixed table name, public because every other class that names this table reads it from
     * here: the plugin class, both log table declarations, the dimension, the record builder and the
     * tests. A literal repeated in all of them can drift, and a table Matomo cannot find under the
     * name it was declared with fails silently.
     *
     * Prefix it with your plugin name, as `TagManager`'s `tagmanager_*` tables do. `log_custom` or
     * `log_user` would collide with the next core table of that name. Keep the `log_` prefix too --
     * `plugins/BotTracking/Dao/BotRequestsDao.php` does with `log_bot_request` -- because that is what
     * marks the table as log data to a human reading the schema.
     */
    public const TABLE_NAME = 'log_examplelogtables_user';

    /**
     * The width of the user id, in characters.
     *
     * It matches `log_visit.user_id`, because that is the column this table joins on. The number is
     * repeated here rather than imported from `CoreHome\Columns\UserId::MAXLENGTH`: nothing outside
     * that plugin reads the constant, and reaching into another plugin's classes for a value is the
     * habit this example set exists to discourage. Copy the number and name its source.
     */
    public const MAX_LENGTH_USER_ID = 200;

    /**
     * The width of the plan attribute, in characters.
     */
    public const MAX_LENGTH_PLAN = 30;

    private string $tablePrefixed;

    public function __construct()
    {
        $this->tablePrefixed = Common::prefixTable(self::TABLE_NAME);
    }

    public function install(): void
    {
        // Two column decisions carry more weight than they look.
        //
        // `user_id` is VARCHAR(200) because that is what `log_visit.user_id` is
        // (`CoreHome\Columns\UserId::MAXLENGTH`, which the dimension also truncates to). A column
        // you join on has to hold the joined value byte for byte: make it shorter and the write
        // either fails outright, on the strict `sql_mode` a default MySQL gives the tracker
        // connection, or truncates with a warning nobody reads on a server configured the other way
        // -- and a truncated user id matches no row in `log_visit`, so no join, and therefore no
        // GDPR deletion or export, ever reaches it again.
        //
        // Both attribute columns carry an explicit `DEFAULT ''`, because a tracking request that
        // mentions only one of them inserts only that column, and the tracker's strict `sql_mode`
        // rejects an INSERT that omits a NOT NULL column with no default.
        DbHelper::createTable(self::TABLE_NAME, sprintf(
            "
                  `user_id` VARCHAR(%d) NOT NULL,
                  `plan` VARCHAR(%d) NOT NULL DEFAULT '',
                  `account_name` VARCHAR(%d) NOT NULL DEFAULT '',
                  PRIMARY KEY (user_id)",
            self::MAX_LENGTH_USER_ID,
            self::MAX_LENGTH_PLAN,
            CustomAccountLog::MAX_LENGTH_ACCOUNT_NAME
        ));
    }

    public function uninstall(): void
    {
        Db::query(sprintf('DROP TABLE IF EXISTS `%s`', $this->tablePrefixed));
    }

    /**
     * Returns the attributes recorded for one user, or an empty array if the user is unknown.
     *
     * @return array<string, string>
     */
    public function getUserInformation(string $userId): array
    {
        $sql = 'SELECT plan, account_name FROM `' . $this->tablePrefixed . '` WHERE user_id = ?';

        $row = Db::fetchRow($sql, [$userId]);

        return $row ?: [];
    }

    /**
     * Records the plan one tracking request carried for one user.
     *
     * The tracker sees the same user on every one of their visits, so this has to be an upsert
     * rather than an insert: the primary key on `user_id` is what keeps the table at one row per
     * user instead of one row per request. On the insert branch the *other* attribute takes the
     * default declared for its column, which is what makes those defaults load-bearing.
     *
     * **One method per attribute is the whole mechanism behind "store only what the request
     * carried".** A request that says nothing about the account calls nothing that writes the account
     * column, so it cannot erase what an earlier request stored, and the SQL says which column it
     * writes. The alternative -- one method taking an array and assembling the column list from its
     * keys -- saves a round trip and costs an unwritten contract between this class and its caller
     * over which keys are legal.
     *
     * Core writes column lists and `ON DUPLICATE KEY UPDATE` clauses literally. Runtime-built column
     * lists exist in about fifteen places, all of them schema-generic code that cannot know its
     * columns in advance; `core/Updater/Migration/Db/Insert.php` is the one to copy if you genuinely
     * need one, and note that it backticks every identifier it interpolates. A DAO for one known
     * table is not that case. Core has no partial upsert at all, so there is nothing to copy here
     * and the shape that invents least wins.
     */
    public function addOrUpdatePlan(string $userId, string $plan): void
    {
        $sql = sprintf(
            'INSERT INTO `%s` (user_id, plan) VALUES (?, ?) ON DUPLICATE KEY UPDATE plan = ?',
            $this->tablePrefixed
        );

        Db::query($sql, [$userId, $plan, $plan]);
    }

    /**
     * Records the account one tracking request said a user belongs to.
     *
     * @see addOrUpdatePlan() for why each attribute has its own method.
     */
    public function addOrUpdateAccountName(string $userId, string $accountName): void
    {
        $sql = sprintf(
            'INSERT INTO `%s` (user_id, account_name) VALUES (?, ?) ON DUPLICATE KEY UPDATE account_name = ?',
            $this->tablePrefixed
        );

        Db::query($sql, [$userId, $accountName, $accountName]);
    }
}
