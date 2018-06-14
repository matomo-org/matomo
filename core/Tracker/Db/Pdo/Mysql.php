<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Tracker\Db\Pdo;

use Exception;
use PDO;
use PDOException;
use PDOStatement;
use Piwik\Tracker\Db;
use Piwik\Tracker\Db\DbException;

/**
 * PDO MySQL wrapper
 *
 */
class Mysql extends Db
{
    /**
     * @var PDO
     */
    protected $connection = null;
    protected $dsn;
    protected $username;
    protected $password;
    protected $charset;

    protected $mysqlOptions = array();

    
    protected $activeTransaction = false;

    /**
     * Builds the DB object
     *
     * @param array $dbInfo
     * @param string $driverName
     */
    public function __construct($dbInfo, $driverName = 'mysql')
    {
        if (isset($dbInfo['unix_socket']) && $dbInfo['unix_socket'][0] == '/') {
            $this->dsn = $driverName . ':dbname=' . $dbInfo['dbname'] . ';unix_socket=' . $dbInfo['unix_socket'];
        } elseif (!empty($dbInfo['port']) && $dbInfo['port'][0] == '/') {
            $this->dsn = $driverName . ':dbname=' . $dbInfo['dbname'] . ';unix_socket=' . $dbInfo['port'];
        } else {
            $this->dsn = $driverName . ':dbname=' . $dbInfo['dbname'] . ';host=' . $dbInfo['host'] . ';port=' . $dbInfo['port'];
        }

        $this->username = $dbInfo['username'];
        $this->password = $dbInfo['password'];

        if (isset($dbInfo['charset'])) {
            $this->charset = $dbInfo['charset'];
            $this->dsn .= ';charset=' . $this->charset;
        }


        if (isset($dbInfo['enable_ssl']) && $dbInfo['enable_ssl']) {

            if (!empty($dbInfo['ssl_key'])) {
                $this->mysqlOptions[PDO::MYSQL_ATTR_SSL_KEY] = $dbInfo['ssl_key'];
            }
            if (!empty($dbInfo['ssl_cert'])) {
                $this->mysqlOptions[PDO::MYSQL_ATTR_SSL_CERT] = $dbInfo['ssl_cert'];
            }
            if (!empty($dbInfo['ssl_ca'])) {
                $this->mysqlOptions[PDO::MYSQL_ATTR_SSL_CA] = $dbInfo['ssl_ca'];
            }
            if (!empty($dbInfo['ssl_ca_path'])) {
                $this->mysqlOptions[PDO::MYSQL_ATTR_SSL_CAPATH] = $dbInfo['ssl_ca_path'];
            }
            if (!empty($dbInfo['ssl_cipher'])) {
                $this->mysqlOptions[PDO::MYSQL_ATTR_SSL_CIPHER] = $dbInfo['ssl_cipher'];
            }
            if (!empty($dbInfo['ssl_no_verify']) && defined('PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT')) {
                $this->mysqlOptions[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
            }
        }

    }

    public function __destruct()
    {
        $this->connection = null;
    }

    /**
     * Connects to the DB
     *
     * @throws Exception if there was an error connecting the DB
     */
    public function connect()
    {
        if (self::$profiling) {
            $timer = $this->initProfiler();
        }

        // Make sure MySQL returns all matched rows on update queries including
        // rows that actually didn't have to be updated because the values didn't
        // change. This matches common behaviour among other database systems.
        // See #6296 why this is important in tracker
        $this->mysqlOptions[PDO::MYSQL_ATTR_FOUND_ROWS] = true;
        $this->mysqlOptions[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;
        


        $this->connection = @new PDO($this->dsn, $this->username, $this->password, $this->mysqlOptions);

        // we may want to setAttribute(PDO::ATTR_TIMEOUT ) to a few seconds (default is 60) in case the DB is locked
        // the piwik.php would stay waiting for the database... bad!
        // we delete the password from this object "just in case" it could be printed
        $this->password = '';

        /*
         * Lazy initialization via MYSQL_ATTR_INIT_COMMAND depends
         * on mysqlnd support, PHP version, and OS.
         * see ZF-7428 and http://bugs.php.net/bug.php?id=47224
         */
        if (!empty($this->charset)) {
            $sql = "SET NAMES '" . $this->charset . "'";
            $this->connection->exec($sql);
        }

        if (self::$profiling && isset($timer)) {
            $this->recordQueryProfile('connect', $timer);
        }
    }

    /**
     * Disconnects from the server
     */
    public function disconnect()
    {
        $this->connection = null;
    }

    /**
     * Returns an array containing all the rows of a query result, using optional bound parameters.
     *
     * @param string $query Query
     * @param array $parameters Parameters to bind
     * @return array|bool
     * @see query()
     * @throws Exception|DbException if an exception occurred
     */
    public function fetchAll($query, $parameters = array())
    {
        try {
            $sth = $this->query($query, $parameters);
            if ($sth === false) {
                return false;
            }
            return $sth->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new DbException("Error query: " . $e->getMessage());
        }
    }

    /**
     * Fetches the first column of all SQL result rows as an array.
     *
     * @param string $sql An SQL SELECT statement.
     * @param mixed $bind Data to bind into SELECT placeholders.
     * @throws \Piwik\Tracker\Db\DbException
     * @return string
     */
    public function fetchCol($sql, $bind = array())
    {
        try {
            $sth = $this->query($sql, $bind);
            if ($sth === false) {
                return false;
            }
            $result = $sth->fetchAll(PDO::FETCH_COLUMN, 0);
            return $result;
        } catch (PDOException $e) {
            throw new DbException("Error query: " . $e->getMessage());
        }
    }

    /**
     * Returns the first row of a query result, using optional bound parameters.
     *
     * @param string $query Query
     * @param array $parameters Parameters to bind
     * @return bool|mixed
     * @see query()
     * @throws Exception|DbException if an exception occurred
     */
    public function fetch($query, $parameters = array())
    {
        try {
            $sth = $this->query($query, $parameters);
            if ($sth === false) {
                return false;
            }
            return $sth->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new DbException("Error query: " . $e->getMessage());
        }
    }

    /**
     * Executes a query, using optional bound parameters.
     *
     * @param string $query Query
     * @param array|string $parameters Parameters to bind array('idsite'=> 1)
     * @return PDOStatement|bool  PDOStatement or false if failed
     * @throws DbException if an exception occurred
     */
    public function query($query, $parameters = array())
    {
        if (is_null($this->connection)) {
            return false;
        }

        try {
            if (self::$profiling) {
                $timer = $this->initProfiler();
            }

            if (!is_array($parameters)) {
                $parameters = array($parameters);
            }
            $sth = $this->connection->prepare($query);
            $sth->execute($parameters);

            if (self::$profiling && isset($timer)) {
                $this->recordQueryProfile($query, $timer);
            }
            return $sth;
        } catch (PDOException $e) {
            $message = $e->getMessage() . " In query: $query Parameters: " . var_export($parameters, true);
            throw new DbException("Error query: " . $message, (int) $e->getCode());
        }
    }

    /**
     * Returns the last inserted ID in the DB
     * Wrapper of PDO::lastInsertId()
     *
     * @return int
     */
    public function lastInsertId()
    {
        return $this->connection->lastInsertId();
    }

    /**
     * Test error number
     *
     * @param Exception $e
     * @param string $errno
     * @return bool
     */
    public function isErrNo($e, $errno)
    {
        if (preg_match('/([0-9]{4})/', $e->getMessage(), $match)) {
            return $match[1] == $errno;
        }
        return false;
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

    /**
     * Start Transaction
     * @return string TransactionID
     */
    public function beginTransaction()
    {
        if (!$this->activeTransaction === false) {
            return;
        }

        if ($this->connection->beginTransaction()) {
            $this->activeTransaction = uniqid();
            return $this->activeTransaction;
        }
    }

    /**
     * Commit Transaction
     * @param $xid
     * @throws DbException
     * @internal param TransactionID $string from beginTransaction
     */
    public function commit($xid)
    {
        if ($this->activeTransaction != $xid || $this->activeTransaction === false) {
            return;
        }

        $this->activeTransaction = false;

        if (!$this->connection->commit()) {
            throw new DbException("Commit failed");
        }
    }

    /**
     * Rollback Transaction
     * @param $xid
     * @throws DbException
     * @internal param TransactionID $string from beginTransaction
     */
    public function rollBack($xid)
    {
        if ($this->activeTransaction != $xid || $this->activeTransaction === false) {
            return;
        }

        $this->activeTransaction = false;

        if (!$this->connection->rollBack()) {
            throw new DbException("Rollback failed");
        }
    }
}
