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
class Piwik_Db_Pdo_Mysql extends Zend_Db_Adapter_Pdo_Mysql implements Piwik_Db_iAdapter
{
	public function __construct($config)
	{
		$config['driver_options'] = array(PDO::MYSQL_ATTR_INIT_COMMAND, "SET NAMES 'utf8'");
		parent::__construct($config);
	}

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

		$this->_connect();

		// see http://framework.zend.com/issues/browse/ZF-1398
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
//		$databaseVersion = $this->getServerVersion();
                $databaseVersion = $this->fetchOne('SELECT VERSION()', array());
                $requiredVersion = Zend_Registry::get('config')->General->minimum_mysql_version;
                if(version_compare($databaseVersion, $requiredVersion) === -1)
                {
                        throw new Exception(Piwik_TranslateException('Core_ExceptionDatabaseVersion', array('MySQL', $databaseVersion, $requiredVersion)));
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
		return in_array('PDO', $extensions) && in_array('pdo_mysql', $extensions);
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
	 * @param string $errno
	 * @return bool
	 */
	public function isErrNo($errno)
	{
		$errInfo = $this->errorInfo();
		return $errInfo[1] == $errno;
	}
}
