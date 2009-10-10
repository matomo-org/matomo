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
                        throw new Exception(Piwik_TranslateException('Core_ExceptionDatabaseVersion', array('PostgreSQL', $databaseVersion, $requiredVersion)));
                }
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
	 * Returns a list of the tables in the database.
	 *
	 * Replaces parent::listTables() which uses subqueries.
	 * @see ZF-????
	 *
	 * @return array
	 */
	public function listTables()
	{
		$sql = "SELECT c.relname  AS table_name "
			. "FROM pg_catalog.pg_class c "
			. "JOIN pg_catalog.pg_roles r ON r.oid = c.relowner "
			. "LEFT JOIN pg_catalog.pg_namespace n ON n.oid = c.relnamespace "
			. "WHERE n.nspname <> 'pg_catalog' "
			. "AND n.nspname !~ '^pg_toast' "
			. "AND pg_catalog.pg_table_is_visible(c.oid) "
			. "AND c.relkind = 'r' ";
 
         return $this->fetchCol($sql);
	}
}
