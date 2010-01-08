<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @category Piwik
 * @package Piwik
 */

/**
 * @package Piwik
 */
class Piwik_Db_Pdo_Pgsql extends Zend_Db_Adapter_Pdo_Pgsql implements Piwik_Db_iAdapter
{
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
		return 5432;
	}

	/**
	 * Check PostgreSQL version
	 */
	public function checkServerVersion()
	{
		$databaseVersion = $this->getServerVersion();
		$requiredVersion = Zend_Registry::get('config')->General->minimum_pgsql_version;
		if(version_compare($databaseVersion, $requiredVersion) === -1)
		{
			throw new Exception(Piwik_TranslateException('General_ExceptionDatabaseVersion', array('PostgreSQL', $databaseVersion, $requiredVersion)));
		}
	}

	/**
	 * Check client version compatibility against database server
	 */
	public function checkClientVersion()
	{
	}

	/**
	 * Returns true if this adapter's required extensions are enabled
	 *
	 * @return bool
	 */
	public static function isEnabled()
	{
		/**
		 * @todo This adapter is incomplete.
		 */
		return false;
		$extensions = @get_loaded_extensions();
		return in_array('PDO', $extensions) && in_array('pdo_pgsql', $extensions);
	}

	/**
	 * Returns true if this adapter supports blobs as fields
	 *
	 * @return bool
	 */
	public function hasBlobDataType()
	{
		// large objects must be loaded from a file using a non-SQL API
		// and then referenced by the object ID (oid);
		// the alternative, bytea fields, incur a space and time
		// penalty for encoding/decoding
		return false;
	}

	/**
	 * Pre-process SQL to handle MySQL-isms
	 *
	 * @return string
	 */
	public function preprocessSql($query)
	{
		$search = array(
			// In MySQL, OPTION is still a reserved keyword; Piwik uses 
			// backticking in case table_prefix is empty.
			'`',

			// MySQL implicitly does 'ORDER BY column' when there's a
			// 'GROUP BY column'; Piwik uses 'ORDER BY NULL' when order
			// doesn't matter, for better performance.
			'ORDER BY NULL',
		);

		$replace = array(
			'',
			'',
		);

		$query = str_replace($search, $replace, $query);
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
		// map MySQL driver-specific error codes to PostgreSQL SQLSTATE
		$map = array(
			// MySQL: Unknown database '%s'
			// PostgreSQL: database "%s" does not exist
			'1049' => '08006',

			// MySQL: Table '%s' already exists
			// PostgreSQL: relation "%s" already exists
			'1050' => '42P07',

			// MySQL: Unknown column '%s' in '%s'
			// PostgreSQL: column "%s" does not exist
			'1054' => '42703',

			// MySQL: Duplicate column name '%s'
			// PostgreSQL: column "%s" of relation "%s" already exists
			'1060' => '42701',

			// MySQL: Duplicate key name '%s'
			// PostgreSQL: relation "%s" already exists
			'1061' => '42P07',

			// MySQL: Duplicate entry '%s' for key '%s'
			// PostgreSQL: duplicate key violates unique constraint
			'1062' => '23505',

			// MySQL: Can't DROP '%s'; check that column/key exists
			// PostgreSQL: index "%s" does not exist
			'1091' => '42704',

			// MySQL: Table '%s.%s' doesn't exist
			// PostgreSQL: relation "%s" does not exist
			'1146' => '42P01',
		);

		if(preg_match('/([0-9]{2}[0-9P][0-9]{2})/', $e->getMessage(), $match))
		{
			return $match[1] == $map[$errno];
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
		$charset = $this->fetchOne('SHOW client_encoding');
		return strtolower($charset) === 'utf8';
	}

	/**
	 * Get server timezone offset in seconds
	 *
	 * @return string
	 */
	public function getCurrentTimezone()
	{
		$tzOffset = $this->fetchOne('SELECT extract(timezone FROM now())');
		return $tzOffset;
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
}
