<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Db\Adapter\Pdo;

use Exception;
use Piwik\Db\AdapterInterface;
use Piwik\Db\Schema\Sqlite\NamedLock;
use Piwik\Db\Sqlite\Connection;
use Piwik\Db\Sqlite\ErrorCodes;
use Piwik\Piwik;
use Zend_Db_Adapter_Exception;
use Zend_Db_Adapter_Pdo_Abstract;

/**
 * SQLite adapter (`adapter = PDO\SQLITE`). `dbname` is the path of the database file; the host,
 * port and credential settings are ignored.
 */
class Sqlite extends Zend_Db_Adapter_Pdo_Abstract implements AdapterInterface
{
    protected $_pdoType = 'sqlite'; // phpcs:ignore PSR2.Classes.PropertyDeclaration.Underscore

    protected $_defaultStmtClass = SqliteStatement::class; // phpcs:ignore PSR2.Classes.PropertyDeclaration.Underscore

    /**
     * @var array the configuration, kept for the connection settings after resetConfig()
     */
    private $connectionConfig;

    public function __construct($config)
    {
        // Zend requires credentials, SQLite has none
        $config['username'] = null;
        $config['password'] = null;
        $this->connectionConfig = $config;

        parent::__construct($config);
    }

    /**
     * Backticks, as on MySQL: SQLite accepts them, and they keep the identifiers Matomo quotes out of
     * SQLite's double-quoted string fallback and its error messages, which quote only a column to drop.
     */
    public function getQuoteIdentifierSymbol()
    {
        return '`';
    }

    /**
     * The absolute path of the database file.
     */
    public function getDatabaseFile(): string
    {
        return Connection::getDatabaseFile($this->connectionConfig);
    }

    protected function _dsn() // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
    {
        return Connection::getDsn(Connection::getDatabaseFile($this->connectionConfig));
    }

    protected function _connect() // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
    {
        if ($this->_connection) {
            return;
        }

        $file = Connection::getDatabaseFile($this->connectionConfig);
        if (!is_file($file)) {
            throw new Zend_Db_Adapter_Exception(sprintf('SQLite: the database file %s does not exist.', $file));
        }

        $this->_config['driver_options'] = Connection::getDriverOptions();
        parent::_connect();

        Connection::configure($this->_connection, $this->connectionConfig);
    }

    /**
     * Closes the connection, releasing its named locks as a disconnect does on MySQL.
     */
    public function closeConnection()
    {
        NamedLock::releaseConnection($this);
        parent::closeConnection();
    }

    /**
     * Reset the configuration variables in this adapter.
     */
    public function resetConfig()
    {
        $this->_config = [];
    }

    public function checkServerVersion()
    {
        $version = $this->getServerVersion();
        if (version_compare($version, Connection::MINIMUM_VERSION) === -1) {
            throw new Exception(Piwik::translate('General_ExceptionDatabaseVersion', ['SQLite', $version, Connection::MINIMUM_VERSION]));
        }
    }

    public function getServerVersion()
    {
        return (string) $this->fetchOne('SELECT sqlite_version()');
    }

    public static function isEnabled()
    {
        return extension_loaded('PDO') && Connection::isEnabled();
    }

    public function hasBlobDataType()
    {
        return true;
    }

    public function hasBulkLoader()
    {
        return false;
    }

    public function isErrNo($e, $errno)
    {
        return ErrorCodes::isErrNo($e, $errno);
    }

    public function isConnectionUTF8()
    {
        // SQLite stores text as UTF-8
        return true;
    }

    /**
     * Return number of affected rows in last query
     *
     * @param mixed $queryResult Result from query()
     * @return int
     */
    public function rowCount($queryResult)
    {
        return $queryResult->rowCount();
    }

    public function getClientVersion()
    {
        return $this->getServerVersion();
    }

    public function listTables()
    {
        return $this->fetchCol("SELECT name FROM sqlite_schema WHERE type = 'table' AND name NOT LIKE 'sqlite\\_%' ESCAPE '\\' ORDER BY name");
    }

    /**
     * Returns the column descriptions in Zend's format.
     */
    public function describeTable($tableName, $schemaName = null)
    {
        $columns = $this->fetchAll('PRAGMA table_info(' . $this->quoteIdentifier($tableName) . ')');
        // a sole INTEGER primary key is the rowid, which SQLite assigns
        $primaryKeyColumns = array_filter($columns, function ($column) {
            return (int) $column['pk'] > 0;
        });
        $hasIdentity = count($primaryKeyColumns) === 1 && strtoupper(reset($primaryKeyColumns)['type']) === 'INTEGER';

        $description = [];
        foreach ($columns as $position => $column) {
            // pk is the column's position in the primary key, from 1
            $primaryPosition = (int) $column['pk'];
            $description[$column['name']] = [
                'SCHEMA_NAME'      => null,
                'TABLE_NAME'       => $tableName,
                'COLUMN_NAME'      => $column['name'],
                'COLUMN_POSITION'  => $position + 1,
                'DATA_TYPE'        => $column['type'],
                'DEFAULT'          => $column['dflt_value'],
                'NULLABLE'         => !$column['notnull'] && $primaryPosition === 0,
                'LENGTH'           => null,
                'SCALE'            => null,
                'PRECISION'        => null,
                'UNSIGNED'         => null,
                'PRIMARY'          => $primaryPosition > 0,
                'PRIMARY_POSITION' => $primaryPosition > 0 ? $primaryPosition : null,
                'IDENTITY'         => $primaryPosition > 0 && $hasIdentity,
            ];
        }

        return $description;
    }

    public function limit($sql, $count, $offset = 0)
    {
        $count = (int) $count;
        $offset = (int) $offset;
        if ($count <= 0) {
            throw new Zend_Db_Adapter_Exception("LIMIT argument count=$count is not valid");
        }
        if ($offset < 0) {
            throw new Zend_Db_Adapter_Exception("LIMIT argument offset=$offset is not valid");
        }

        return $sql . ' LIMIT ' . $count . ($offset > 0 ? ' OFFSET ' . $offset : '');
    }

    /**
     * Matomo's explicit transactions all write, so they take the write lock when they start instead
     * of failing on the first write when another connection has written in between.
     */
    protected function _beginTransaction() // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
    {
        $this->getPdo()->exec('BEGIN IMMEDIATE');
    }

    protected function _commit() // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
    {
        $this->getPdo()->exec('COMMIT');
    }

    protected function _rollBack() // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
    {
        $this->getPdo()->exec('ROLLBACK');
    }

    private function getPdo(): \PDO
    {
        $this->_connect();
        if (!$this->_connection instanceof \PDO) {
            throw new Zend_Db_Adapter_Exception('SQLite: not connected.');
        }

        return $this->_connection;
    }

    /**
     * SQLite transactions are serializable; there is no isolation level to change.
     */
    public function getCurrentTransactionIsolationLevelForSession(): string
    {
        return 'SERIALIZABLE';
    }

    public function setTransactionIsolationLevel(string $level): void
    {
    }

    public function getSupportsTransactionLevelForNonLockingReads(): ?bool
    {
        return false;
    }

    public function setSupportsTransactionLevelForNonLockingReads(?bool $supports = null): void
    {
    }
}
