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
 * Schema abstraction
 *
 * Note: no relation to the ZF proposals for Zend_Db_Schema_Manager
 *
 * @package Piwik
 */
class Piwik_Db_Schema
{
	static private $instance = null;

	private $schema = null;

	/**
	 * Returns the singleton Piwik_Db_Schema
	 *
	 * @return Piwik_Db_Schema
	 */
	static public function getInstance()
	{
		if (self::$instance === null)
		{
			$c = __CLASS__;
			self::$instance = new $c();
		}
		return self::$instance;
	}

	/**
	 * Get schema class name
	 *
	 * @param string $schemaName
	 * @return string
	 */
	private static function getSchemaClassName($schemaName)
	{
		return 'Piwik_Db_Schema_' . str_replace(' ', '_', ucwords(str_replace('_', ' ', strtolower($schemaName))));
	}

	/**
	 * Get list of schemas
	 *
	 * @return array
	 */
	public static function getSchemas($adapterName)
	{
		static $allSchemaNames = array(
			// MySQL storage engines
			'MYSQL' => array(
				'Myisam',
//				'Sharding',
//				'Innodb',
//				'Infinidb',
			),

			// Microsoft SQL Server
//			'MSSQL' => array( 'Mssql' ),

			// PostgreSQL
//			'PDO_PGSQL' => array( 'Pgsql' ),

			// IBM DB2
//			'IBM' => array( 'Ibm' ),

			// Oracle
//			'OCI' => array( 'Oci' ),
		);

		$adapterName = strtoupper($adapterName);
		switch($adapterName)
		{
			case 'PDO_MYSQL':
			case 'MYSQLI':
				$adapterName = 'MYSQL';
				break;

			case 'PDO_MSSQL':
			case 'SQLSRV':
				$adapterName = 'MSSQL';
				break;

			case 'PDO_IBM':
			case 'DB2':
				$adapterName = 'IBM';
				break;

			case 'PDO_OCI':
			case 'ORACLE':
				$adapterName = 'OCI';
				break;
		}
		$schemaNames = $allSchemaNames[$adapterName];

		$schemas = array();

		foreach($schemaNamess as $schemaName)
		{
			$className = 'Piwik_Db_Schema_'.$schemaName;
			if(call_user_func(array($className, 'isAvailable')))
			{
				$schemas[] = $schemaName;
			}
		}

		return $schemas;
	}

	/**
	 * Load schema
	 */
	private function loadSchema()
	{
		$config = Zend_Registry::get('config');
		$dbInfos = $config->database->toArray();
		if(isset($dbInfos['schema']))
		{
			$schemaName = $dbInfos['schema'];
		}
		else
		{
			$schemaName = 'Myisam';
		}
		$className = self::getSchemaClassName($schemaName);
		$this->schema = new $className();
	}

	/**
	 * Returns an instance that subclasses Piwik_Db_Schema
	 *
	 * @return Piwik_Db_Schema_Interface
	 */
	private function getSchema()
	{
		if ($this->schema === null)
		{
			$this->loadSchema();
		}
		return $this->schema;
	}

	/**
	 * Get the SQL to create a specific Piwik table
	 *
	 * @return string SQL
	 */
	public function getTableCreateSql( $tableName )
	{
		return $this->getSchema()->getTableCreateSql($tableName);
	}

	/**
	 * Get the SQL to create Piwik tables
	 *
	 * @return array of strings containing SQL
	 */
	public function getTablesCreateSql()
	{
		return $this->getSchema()->getTablesCreateSql();
	}

	/**
	 * Create database
	 */
	public function createDatabase( $dbName = null )
	{
		$this->getSchema()->createDatabase($dbName);
	}

	/**
	 * Drop database
	 */
	public function dropDatabase()
	{
		$this->getSchema()->dropDatabase();
	}

	/**
	 * Create all tables
	 */
	public function createTables()
	{
		$this->getSchema()->createTables();
	}

	/**
	 * Creates an entry in the User table for the "anonymous" user.
	 */
	public function createAnonymousUser()
	{
		$this->getSchema()->createAnonymousUser();
	}

	/**
	 * Truncate all tables
	 */
	public function truncateAllTables()
	{
		$this->getSchema()->truncateAllTables();
	}

	/**
	 * Drop specific tables
	 */
	public function dropTables( $doNotDelete = array() )
	{
		$this->getSchema()->dropTables($doNotDelete);
	}

	/**
	 * Names of all the prefixed tables in piwik
	 * Doesn't use the DB
	 *
	 * @return array Table names
	 */
	public function getTablesNames()
	{
		return $this->getSchema()->getTablesNames();
	}

	/**
	 * Get list of tables installed
	 *
	 * @param bool $forceReload Invalidate cache
	 * @param string $idSite
	 * @return array Tables installed
	 */
	public function getTablesInstalled($forceReload = true,  $idSite = null)
	{
		return $this->getSchema()->getTablesInstalled($forceReload, $idSite);
	}

	/**
	 * Returns true if Piwik tables exist
	 *
	 * @return bool True if tables exist; false otherwise
	 */
	public function hasTables()
	{
		return $this->getSchema()->hasTables();
	}
}

interface Piwik_Db_Schema_Interface
{
	static public function isAvailable();

	public function getTableCreateSql($tableName);
	public function getTablesCreateSql();

	public function createDatabase( $dbName = null );
	public function dropDatabase();

	public function createTables();
	public function createAnonymousUser();
	public function truncateAllTables();
	public function dropTables( $doNotDelete = array() );

	public function getTablesNames();
	public function getTablesInstalled($forceReload = true,  $idSite = null);
	public function hasTables();
}
