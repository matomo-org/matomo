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
 * PDO PostgreSQL wrapper
 *
 * @package Piwik
 * @subpackage Piwik_Tracker
 */
class Piwik_Tracker_Db_Pdo_Pgsql extends Piwik_Tracker_Db_Pdo_Mysql
{
	/**
	 * Builds the DB object
	 */
	public function __construct( $dbInfo, $driverName = 'pgsql') 
	{
		parent::__construct( $dbInfo, $driverName );
	}

	/**
	 * Test error number
	 *
	 * @param string $errno
	 * @return bool
	 */
	public function isErrNo($errno)
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

		$errInfo = $this->errorInfo();
		return $errInfo[0] == $map[$errno];
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
}
