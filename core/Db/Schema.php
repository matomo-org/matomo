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
 * Schema
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

	private function __construct()
	{
	}

	/**
	 * Returns an instance that subclasses Piwik_Db_Schema
	 *
	 * @return Piwik_Db_Schema
	 */
	private function getSchema()
	{
		if ($this->schema === null)
		{
			$this->schema = new Piwik_Db_Schema_MySQL();
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
