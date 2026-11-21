<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Db\Sqlite;

/**
 * An error SQLite can't raise itself, reported with the number MySQL gives it, so that the callers
 * checking `isErrNo()` (e.g. a migration ignoring an existing primary key) behave as on MySQL.
 *
 * @internal
 */
final class MysqlError extends \RuntimeException
{
    /**
     * @var int
     */
    private $mysqlErrorNumber;

    public function __construct(string $message, int $mysqlErrorNumber)
    {
        parent::__construct($message);
        $this->mysqlErrorNumber = $mysqlErrorNumber;
    }

    public function getMysqlErrorNumber(): int
    {
        return $this->mysqlErrorNumber;
    }
}
