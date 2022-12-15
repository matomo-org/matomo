<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Tracker\Db;

use Exception;
use mysqli_stmt;
use Piwik\Db\Adapter\MysqlAdapterCommon;
use Piwik\Tracker\Db;

/**
 * mysqli wrapper
 *
 */
class Mysqli extends Db
{
    protected $connection = null;
    protected $host;
    protected $port;
    protected $socket;
    protected $dbname;
    protected $username;
    protected $password;
    protected $charset;
    protected $activeTransaction = false;

    protected $enable_ssl;
    protected $ssl_key;
    protected $ssl_cert;
    protected $ssl_ca;
    protected $ssl_ca_path;
    protected $ssl_cipher;
    protected $ssl_no_verify;

    /**
     * Builds the DB object
     *
     * @param array $dbInfo
     * @param string $driverName
     */
    public function __construct(array $dbInfo, string $driverName = 'mysql')
    {
        if (isset($dbInfo['unix_socket']) && substr($dbInfo['unix_socket'], 0, 1) == '/') {
            $this->host = null;
            $this->port = null;
            $this->socket = $dbInfo['unix_socket'];
        } elseif (isset($dbInfo['port']) && substr($dbInfo['port'], 0, 1) == '/') {
            $this->host = null;
            $this->port = null;
            $this->socket = $dbInfo['port'];
        } else {
            $this->host = $dbInfo['host'];
            $this->port = (int)$dbInfo['port'];
            $this->socket = null;
        }
        $this->dbname = $dbInfo['dbname'];
        $this->username = $dbInfo['username'];
        $this->password = $dbInfo['password'];
        $this->charset = isset($dbInfo['charset']) ? $dbInfo['charset'] : null;


        if(!empty($dbInfo['enable_ssl'])){
            $this->enable_ssl = $dbInfo['enable_ssl'];
        }
        if(!empty($dbInfo['ssl_key'])){
            $this->ssl_key = $dbInfo['ssl_key'];
        }
        if(!empty($dbInfo['ssl_cert'])){
            $this->ssl_cert = $dbInfo['ssl_cert'];
        }
        if(!empty($dbInfo['ssl_ca'])){
            $this->ssl_ca = $dbInfo['ssl_ca'];
        }
        if(!empty($dbInfo['ssl_ca_path'])){
            $this->ssl_ca_path = $dbInfo['ssl_ca_path'];
        }
        if(!empty($dbInfo['ssl_cipher'])){
            $this->ssl_cipher = $dbInfo['ssl_cipher'];
        }
        if(!empty($dbInfo['ssl_no_verify'])){
            $this->ssl_no_verify = $dbInfo['ssl_no_verify'];
        }

    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        $this->connection = null;
    }

    /**
     * Connects to the DB
     *
     * @return void
     *
     * @throws Exception|DbException  if there was an error connecting the DB
     */
    public function connect(): void
    {
        if (self::$profiling) {
            $timer = $this->initProfiler();
        }

        // The default error reporting of mysqli changed in PHP 8.1. To circumvent problems in our error handling we set
        // the erroring reporting to the default that was used prior PHP 8.1
        // See https://php.watch/versions/8.1/mysqli-error-mode for more details
        mysqli_report(MYSQLI_REPORT_OFF);

        $this->connection = mysqli_init();


        if($this->enable_ssl){
            mysqli_ssl_set($this->connection, $this->ssl_key, $this->ssl_cert, $this->ssl_ca, $this->ssl_ca_path, $this->ssl_cipher);
        }

        // Make sure MySQL returns all matched rows on update queries including
        // rows that actually didn't have to be updated because the values didn't
        // change. This matches common behaviour among other database systems.
        // See #6296 why this is important in tracker
        $flags = MYSQLI_CLIENT_FOUND_ROWS;
        if ($this->enable_ssl){
            $flags = $flags | MYSQLI_CLIENT_SSL;
        }
        if ($this->ssl_no_verify && defined('MYSQLI_CLIENT_SSL_DONT_VERIFY_SERVER_CERT')){
            $flags = $flags | MYSQLI_CLIENT_SSL_DONT_VERIFY_SERVER_CERT;
        }
        mysqli_real_connect($this->connection, $this->host, $this->username, $this->password, $this->dbname, $this->port, $this->socket, $flags);
        if (!$this->connection || mysqli_connect_errno()) {
            throw new DbException("Connect failed: " . mysqli_connect_error());
        }

        if ($this->charset && !mysqli_set_charset($this->connection, $this->charset)) {
            throw new DbException("Set Charset failed: " . mysqli_error($this->connection));
        }

        $this->password = '';

        if (self::$profiling && isset($timer)) {
            $this->recordQueryProfile('connect', $timer);
        }
    }

    /**
     * Disconnects from the server
     */
    public function disconnect(): void
    {
        mysqli_close($this->connection);
        $this->connection = null;
    }

    /**
     * Fetch a result from a statement
     *
     * @param mysqli_stmt $stmt
     * @param array $fields
     *
     * @return array|bool|false
     */
    private function fetchResult(mysqli_stmt $stmt, array $fields)
    {

        $values = array_fill(0, count($fields), null);

        $refs = array();
        foreach ($values as $i => &$f) {
            $refs[$i] = &$f;
        }

        call_user_func_array(array($stmt, 'bind_result'), $values);

        $result = $stmt->fetch();

        if ($result === null || $result === false) {
            $stmt->reset();
            return false;
        }

        return array_combine($fields, $values);
    }

    /**
     * Returns an array containing all the rows of a query result, using optional bound parameters.
     *
     * @see query()
     *
     * @param string $query Query
     * @param array $parameters Parameters to bind
     * @return array
     * @throws Exception|DbException if an exception occurred
     */
    public function fetchAll(string $query, array $parameters = []): array
    {
        try {
            if (self::$profiling) {
                $timer = $this->initProfiler();
            }

            list($stmt, $fields) = $this->executeQuery($query, $parameters);

            $rows = array();
            while ($row = $this->fetchResult($stmt, $fields)) {
                $rows[] = $row;
            }

            $stmt->free_result();
            $stmt->close();

            if (self::$profiling && isset($timer)) {
                $this->recordQueryProfile($query, $timer);
            }
            return $rows;
        } catch (Exception $e) {
            throw new DbException("Error query: " . $e->getMessage());
        }
    }

    /**
     * Returns the first row of a query result, using optional bound parameters.
     *
     * @see query()
     *
     * @param string $query Query
     * @param array $parameters Parameters to bind
     *
     * @return array
     *
     * @throws DbException if an exception occurred
     */
    public function fetch($query, array $parameters = []): array
    {
        try {
            if (self::$profiling) {
                $timer = $this->initProfiler();
            }

            list($stmt, $fields) = $this->executeQuery($query, $parameters);

            $row = $this->fetchResult($stmt, $fields);

            $stmt->free_result();
            $stmt->close();

            if (self::$profiling && isset($timer)) {
                $this->recordQueryProfile($query, $timer);
            }
            if ($row === null) {
                $row = false;
            }
            return $row;
        } catch (Exception $e) {
            throw new DbException("Error query: " . $e->getMessage());
        }
    }

    /**
     * Executes a query, using optional bound parameters.
     *
     * @param string $query Query
     * @param array|string $parameters Parameters to bind array('idsite'=> 1)
     *
     * @return bool|resource  false if failed
     * @throws DbException  if an exception occurred
     */
    public function query(string $query, $parameters = [])
    {
        if (is_null($this->connection)) {
            return false;
        }

        try {
            if (self::$profiling) {
                $timer = $this->initProfiler();
            }

            list($stmt, $fields) = $this->executeQuery($query, $parameters);

            if (self::$profiling && isset($timer)) {
                $this->recordQueryProfile($query, $timer);
            }
            return $stmt;
        } catch (Exception $e) {
            throw new DbException("Error query: " . $e->getMessage() . "
                                   In query: $query
                                   Parameters: " . var_export($parameters, true), $e->getCode());
        }
    }

    /**
     * Returns the last inserted ID in the DB
     *
     * @return string
     */
    public function lastInsertId(): string
    {
        return mysqli_insert_id($this->connection);
    }

    /**
     * Execute a query
     *
     * @param $sql
     * @param $bind
     *
     * @return array
     * @throws DbException
     */
    private function executeQuery($sql, $bind): array
    {

        $stmt = mysqli_prepare($this->connection, $sql);

        if (!$stmt) {
            throw new DbException('preparing query failed: ' . mysqli_error($this->connection) . ' : ' . $sql);
        }

        if (!is_array($bind)) {
            $bind = array($bind);
        }

        if (!empty($bind)) {
            array_unshift($bind, str_repeat('s', count($bind)));
            $refs = array();
            foreach ($bind as $key => $value) {
                $refs[$key] = $value;
            }

            call_user_func_array(array($stmt, 'bind_param'), $refs);
        }

        $stmtResult = $stmt->execute();

        if ($stmtResult === false) {
            throw new DbException("Mysqli statement execute error : " . $stmt->error, $stmt->errno);
        }

        if (!empty($stmt->error)) {
            throw new DbException('executeQuery() failed: ' . mysqli_error($this->connection) . ' : ' . $sql);
        }

        $metaResults = $stmt->result_metadata();

        if ($stmt->errno) {
            throw new DbException("Mysqli statement metadata error: " . $stmt->error, $stmt->errno);
        }

        $fields = array();
        if ($metaResults) {
            $fetchedFields = $metaResults->fetch_fields();
            foreach ($fetchedFields as $fetchedField) {
                $fields[] = $fetchedField->name;
            }
            $stmt->store_result();
        }

        return [$stmt, $fields];
    }

    /**
     * Test error number
     *
     * @param Exception $e
     * @param string $errno
     *
     * @return bool
     */
    public function isErrNo(Exception $e, string $errno): bool
    {
        return \Piwik\Db\Adapter\Mysqli::isMysqliErrorNumber($e, $this->connection, $errno);
    }

    /**
     * Return number of affected rows in last query
     *
     * @param mixed $queryResult Result from query()
     *
     * @return int
     */
    public function rowCount($queryResult): int
    {
        if (!empty($queryResult) && $queryResult instanceof mysqli_stmt) {
            return $queryResult->affected_rows;
        }
        return mysqli_affected_rows($this->connection);
    }

    /**
     * Start Transaction
     *
     * @return string TransactionID
     */
    public function beginTransaction(): string
    {
        if (!$this->activeTransaction === false) {
            return;
        }

        if ($this->connection->autocommit(false)) {
            $this->activeTransaction = uniqid();
            return $this->activeTransaction;
        }
    }

    /**
     * Commit Transaction
     *
     * @param $xid
     *
     * @return void
     * @throws DbException
     * @internal param TransactionID $string from beginTransaction
     */
    public function commit($xid): void
    {
        if ($this->activeTransaction != $xid || $this->activeTransaction === false) {
            return;
        }

        $this->activeTransaction = false;

        if (!$this->connection->commit()) {
            throw new DbException("Commit failed");
        }

        $this->connection->autocommit(true);
    }

    /**
     * Rollback Transaction
     *
     * @param $xid
     *
     * @return void
     * @throws DbException
     * @internal param TransactionID $string from beginTransaction
     */
    public function rollBack($xid): void
    {
        if ($this->activeTransaction != $xid || $this->activeTransaction === false) {
            return;
        }

        $this->activeTransaction = false;

        if (!$this->connection->rollback()) {
            throw new DbException("Rollback failed");
        }

        $this->connection->autocommit(true);
    }

    /**
     * Execute any additional session setup that should happen after the database connection is established
     *
     * This method should be overridden by descendent db adapters as needed
     *
     * @param array $trackerConfig
     * @param Db    $db
     *
     * @throws DbException
     */
    protected static function doPostConnectionSetup(array $trackerConfig, Db $db): void
    {
        MysqlAdapterCommon::doTrackerPostConnectionSetup($trackerConfig, $db);
    }

    /**
     * Override exception messages to hide sensitive data
     *
     * This method should be overridden by descendent db adapters if they use different error codes or need
     * to handle additional exceptions
     *
     * @param string $msg
     *
     * @return string
     */
    protected static function overriddenExceptionMessage(string $msg): string
    {
        return MysqlAdapterCommon::overriddenExceptionMessage($msg);
    }

}
