<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
 * 
 * @category Piwik
 * @package Piwik
 */

/**
 * @package Piwik
 * @subpackage Piwik_Db
 */
class Piwik_Db_Adapter_Pdo_Mysql extends Zend_Db_Adapter_Pdo_Mysql implements Piwik_Db_Adapter_Interface
{
	/**
	 * Returns connection handle
	 *
	 * @return resource
	 */
	public function getConnection()
	{
		if($this->_connection)
		{
			return $this->_connection;
		}
		// Enable LOAD DATA INFILE must be set at object creation time, no setter/getter
        $this->_config['driver_options'][PDO::MYSQL_ATTR_LOCAL_INFILE] = true; 

		$this->_connect();

		/**
		 * Before MySQL 5.1.17, server-side prepared statements
		 * do not use the query cache.
		 * @see http://dev.mysql.com/doc/refman/5.1/en/query-cache-operation.html
		 *
		 * MySQL also does not support preparing certain DDL and SHOW
		 * statements.
		 * @see http://framework.zend.com/issues/browse/ZF-1398
		 */
		$this->_connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
		$this->_connection->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);

		return $this->_connection;
	}

	/**
	 * Reset the configuration variables in this adapter.
	 */
	public function resetConfig()
	{
		$this->_config = array();
	}

	/**
	 * Return default port.
	 *
	 * @return int
	 */
	public static function getDefaultPort()
	{
		return 3306;
	}

	/**
	 * Check MySQL version
	 */
	public function checkServerVersion()
	{
		$serverVersion = $this->getServerVersion();
                $requiredVersion = Zend_Registry::get('config')->General->minimum_mysql_version;
                if(version_compare($serverVersion, $requiredVersion) === -1)
                {
                        throw new Exception(Piwik_TranslateException('General_ExceptionDatabaseVersion', array('MySQL', $serverVersion, $requiredVersion)));
                }
	}

	/**
	 * Check client version compatibility against database server
	 */
	public function checkClientVersion()
	{
		$serverVersion = $this->getServerVersion();
		$clientVersion = $this->getClientVersion();
		// incompatible change to DECIMAL implementation in 5.0.3
		if(version_compare($serverVersion, '5.0.3') >= 0
			&& version_compare($clientVersion, '5.0.3') < 0)
		{
			throw new Exception(Piwik_TranslateException('General_ExceptionIncompatibleClientServerVersions', array('MySQL', $clientVersion, $serverVersion)));
		}
	}

	/**
	 * Returns true if this adapter's required extensions are enabled
	 *
	 * @return bool
	 */
	public static function isEnabled()
	{
		$extensions = @get_loaded_extensions();
		return in_array('PDO', $extensions) && in_array('pdo_mysql', $extensions) && in_array('mysql', PDO::getAvailableDrivers());
	}

	/**
	 * Returns true if this adapter supports blobs as fields
	 *
	 * @return bool
	 */
	public function hasBlobDataType()
	{
		return true;
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
		if(preg_match('/(?:\[|\s)([0-9]{4})(?:\]|\s)/', $e->getMessage(), $match))
		{
			return $match[1] == $errno;
		}
		return false;
	}

	/**
	 * Is the connection character set equal to utf8?
	 *
	 * @return bool
	 */
	public function isConnectionUTF8()
	{
		$charsetInfo = $this->fetchAll('SHOW VARIABLES LIKE ?', array('character_set_connection'));
		$charset = $charsetInfo[0]['Value'];
		return $charset === 'utf8';
	}

	/**
	 * Retrieve client version in PHP style
	 *
	 * @return string
	 */
	public function getClientVersion()
	{
		$this->_connect();
		try {
			$version = $this->_connection->getAttribute(PDO::ATTR_CLIENT_VERSION);
			$matches = null;
			if (preg_match('/((?:[0-9]{1,2}\.){1,3}[0-9]{1,2})/', $version, $matches)) {
				return $matches[1];
			}
		} catch (PDOException $e) {
			// In case of the driver doesn't support getting attributes
		}
		return null;
	}
	
	private $cachePreparedStatement = array();

	/**
	 * Prepares and executes an SQL statement with bound data.
	 * Caches prepared statements to avoid preparing the same query more than once
	 *
	 * @param mixed $sql
	 * @param mixed $bind
	 * @return Zend_Db_Statement_Interface
	 */
	public function query($sql, $bind = array())
	{
		if(isset($this->cachePreparedStatement[$sql]))
		{
			if (!is_array($bind)) {
				$bind = array($bind);
			}

			$stmt = $this->cachePreparedStatement[$sql];
			$stmt->execute($bind);
			return $stmt;
		}

		$stmt = parent::query($sql, $bind);
		$this->cachePreparedStatement[$sql] = $stmt;
		return $stmt;
	}
}
