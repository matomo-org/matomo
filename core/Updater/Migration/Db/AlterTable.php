<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Updater\Migration\Db;

use Piwik\Db;
use Piwik\Db\Schema;
use Piwik\Db\Sqlite\Connection;

/**
 * Changes a table with MySQL `ALTER TABLE` specifications.
 *
 * On MySQL and MariaDB, it runs the MySQL statement (one per specification where the server lacks
 * complex column updates), or the statement given. On SQLite, `Schema::alterTable()` runs the
 * specifications when the migration runs, reading the table then, so an earlier migration of the
 * same update is seen; changing a column or the primary key rebuilds the table. The migration
 * shows the MySQL statement it stands for.
 *
 * @ignore
 */
class AlterTable extends Statements
{
    /**
     * @var string
     */
    private $tableName;

    /**
     * @var string[]
     */
    private $specifications;

    /**
     * @param string $tableName the prefixed table name
     * @param string[] $specifications
     * @param int|int[] $errorCodesToIgnore
     * @param string|null $sql the MySQL statement, when it isn't `ALTER TABLE` and the specifications
     */
    public function __construct(string $tableName, array $specifications, $errorCodesToIgnore, ?string $sql = null)
    {
        $this->tableName = $tableName;
        $this->specifications = array_values($specifications);
        if ($sql !== null) {
            $statements = [$sql];
        } elseif ($this->specifications === [] && Connection::isSqliteConnection(Db::get())) {
            $statements = [];
        } elseif (count($this->specifications) === 1 || Schema::getInstance()->supportsComplexColumnUpdates()) {
            // MySQL's statement, whatever the schema configured: nothing is read from the table here
            $statements = [sprintf('ALTER TABLE `%s` %s', $tableName, implode(', ', $this->specifications))];
        } else {
            $statements = array_map(function ($specification) use ($tableName) {
                return sprintf('ALTER TABLE `%s` %s', $tableName, $specification);
            }, $this->specifications);
        }

        parent::__construct($statements, $errorCodesToIgnore);
    }

    public function exec()
    {
        // the connection in use decides, whatever the configuration file says
        if (!Connection::isSqliteConnection(Db::get())) {
            parent::exec();
            return;
        }

        // all or nothing, as one MySQL statement: the error, with MySQL's number, goes to the caller
        // (the updater ignores those the migration expects)
        if ($this->specifications !== []) {
            (new Schema\Sqlite())->alterTable($this->tableName, $this->specifications);
        }
    }
}
