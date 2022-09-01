<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Tracker;

use Exception;
use Piwik\Common;
use Piwik\Config;
use Piwik\Db\Adapter;
use Piwik\Piwik;
use Piwik\Timer;
use Piwik\Tracker\Db\DbException;

/**
 * Simple database wrapper.
 * We can't afford to have a dependency with the Zend_Db module in Tracker.
 * We wrote this simple class
 *
 */
abstract class Db
{
    protected static $profiling = false;

    protected $queriesProfiling = array();

    protected $connection = null;

    // this is used for indicate TransactionLevel Cache
    public $supportsUncommitted = null;

    /**
     * Enables the SQL profiling.
     * For each query, saves in the DB the time spent on this query.
     * Very useful to see the slow query under heavy load.
     * You can then use Piwik::displayDbTrackerProfile();
     * to display the SQLProfiling report and see which queries take time, etc.
     */
    public static function enableProfiling()
    {
        self::$profiling = true;
    }

    /**
     * Disables the SQL profiling logging.
     */
    public static function disableProfiling()
    {
        self::$profiling = false;
    }

    /**
     * Returns true if the SQL profiler is enabled
     * Only used by the unit test that tests that the profiler is off on a  production server
     *
     * @return bool
     */
    public static function isProfilingEnabled()
    {
        return self::$profiling;
    }

    /**
     * Initialize profiler
     *
     * @return Timer
     */
    protected function initProfiler()
    {
        return new Timer;
    }

    /**
     * Record query profile
     *
     * @param string $query
     * @param Timer $timer
     */
    protected function recordQueryProfile($query, $timer)
    {
        if (!isset($this->queriesProfiling[$query])) {
            $this->queriesProfiling[$query] = array('sum_time_ms' => 0, 'count' => 0);
        }

        $time  = $timer->getTimeMs(2);
        $time += $this->queriesProfiling[$query]['sum_time_ms'];
        $count = $this->queriesProfiling[$query]['count'] + 1;

        $this->queriesProfiling[$query] = array('sum_time_ms' => $time, 'count' => $count);
    }

    /**
     * When destroyed, if SQL profiled enabled, logs the SQL profiling information
     */
    public function recordProfiling()
    {
        if (is_null($this->connection)) {
            return;
        }

        // turn off the profiler so we don't profile the following queries
        self::$profiling = false;

        foreach ($this->queriesProfiling as $query => $info) {
            $time  = $info['sum_time_ms'];
            $time  = Common::forceDotAsSeparatorForDecimalPoint($time);
            $count = $info['count'];

            $queryProfiling = "INSERT INTO " . Common::prefixTable('log_profiling') . "
						(query,count,sum_time_ms) VALUES (?,$count,$time)
						ON DUPLICATE KEY UPDATE count=count+$count,sum_time_ms=sum_time_ms+$time";
            $this->query($queryProfiling, array($query));
        }

        // turn back on profiling
        self::$profiling = true;
    }

    /**
     * Connects to the DB
     *
     * @throws \Piwik\Tracker\Db\DbException if there was an error connecting the DB
     */
    abstract public function connect();

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
     * @see query()
     * @throws \Piwik\Tracker\Db\DbException if an exception occurred
     */
    abstract public function fetchAll($query, $parameters = array());

    /**
     * Returns the first row of a query result, using optional bound parameters.
     *
     * @param string $query Query
     * @param array $parameters Parameters to bind
     * @see also query()
     *
     * @throws DbException if an exception occurred
     */
    abstract public function fetch($query, $parameters = array());

    /**
     * This function is a proxy to fetch(), used to maintain compatibility with Zend_Db interface
     *
     * @see fetch()
     * @param string $query Query
     * @param array $parameters Parameters to bind
     * @return
     */
    public function fetchRow($query, $parameters = array())
    {
        return $this->fetch($query, $parameters);
    }

    /**
     * This function is a proxy to fetch(), used to maintain compatibility with Zend_Db interface
     *
     * @see fetch()
     * @param string $query Query
     * @param array $parameters Parameters to bind
     * @return bool|mixed
     */
    public function fetchOne($query, $parameters = array())
    {
        $result = $this->fetch($query, $parameters);
        return is_array($result) && !empty($result) ? reset($result) : false;
    }

    /**
     * This function is a proxy to fetch(), used to maintain compatibility with Zend_Db + PDO interface
     *
     * @see fetch()
     * @param string $query Query
     * @param array $parameters Parameters to bind
     * @return
     */
    public function exec($query, $parameters = array())
    {
        return $this->fetch($query, $parameters);
    }

    /**
     * Return number of affected rows in last query
     *
     * @param mixed $queryResult Result from query()
     * @return int
     */
    abstract public function rowCount($queryResult);

    /**
     * Executes a query, using optional bound parameters.
     *
     * @param string $query Query
     * @param array $parameters Parameters to bind array('idsite'=> 1)
     *
     * @return PDOStatement or false if failed
     * @throws DbException if an exception occurred
     */
    abstract public function query($query, $parameters = array());

    /**
     * Returns the last inserted ID in the DB
     * Wrapper of PDO::lastInsertId()
     *
     * @return int
     */
    abstract public function lastInsertId();

    /**
     * Test error number
     *
     * @param Exception $e
     * @param string $errno
     * @return bool  True if error number matches; false otherwise
     */
    abstract public function isErrNo($e, $errno);

    /**
     * Factory to create database objects
     *
     * @param array $configDb Database configuration
     * @throws Exception
     * @return \Piwik\Tracker\Db\Mysqli|\Piwik\Tracker\Db\Pdo\Mysql
     */
    public static function factory($configDb)
    {
        /**
         * Triggered before a connection to the database is established by the Tracker.
         *
         * This event can be used to change the database connection settings used by the Tracker.
         *
         * @param array $dbInfos Reference to an array containing database connection info,
         *                       including:
         *
         *                       - **host**: The host name or IP address to the MySQL database.
         *                       - **username**: The username to use when connecting to the
         *                                       database.
         *                       - **password**: The password to use when connecting to the
         *                                       database.
         *                       - **dbname**: The name of the Piwik MySQL database.
         *                       - **port**: The MySQL database port to use.
         *                       - **adapter**: either `'PDO\MYSQL'` or `'MYSQLI'`
         *                       - **type**: The MySQL engine to use, for instance 'InnoDB'
         */
        Piwik::postEvent('Tracker.getDatabaseConfig', array(&$configDb));

        $className = 'Piwik\Tracker\Db\\' . str_replace(' ', '\\', ucwords(str_replace(array('_', '\\'), ' ', strtolower($configDb['adapter']))));

        if (!class_exists($className)) {
           throw new Exception('Unsupported database adapter ' . $configDb['adapter']);
        }

        return new $className($configDb);
    }

    public static function connectPiwikTrackerDb()
    {
        $db = null;
        $configDb = Config::getInstance()->database;

        if (!isset($configDb['port'])) {
            // before 0.2.4 there is no port specified in config file
            $configDb['port'] = '3306';
        }

        $db = self::factory($configDb);

        try {
            $db->connect();
        } catch (\Exception $e) {
            $msg = Adapter::overriddenExceptionMessage($e->getMessage());
            if ('' !== $msg) {
                throw new \Exception($msg);
            }
            throw $e;
        }

        $trackerConfig = Config::getInstance()->Tracker;
        if (!empty($trackerConfig['innodb_lock_wait_timeout']) && $trackerConfig['innodb_lock_wait_timeout'] > 0){
            // we set this here because we only want to set this config if a connection is actually created.
            $time = (int) $trackerConfig['innodb_lock_wait_timeout'];
            $db->query('SET @@innodb_lock_wait_timeout = ' . $time);
        }
        return $db;
    }
}
