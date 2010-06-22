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
class Piwik_Db_Adapter
{
	/**
	 * Create adapter
	 *
	 * @param string $adapterName database adapter name
	 * @param array $dbInfos database connection info
	 * @return mixed (Piwik_Db_Adapter_Mysqli, Piwik_Db_Adapter_Pdo_Mysql, etc)
	 */
	public static function factory($adapterName, & $dbInfos)
	{
		if($dbInfos['port'][0] == '/')
		{
			$dbInfos['unix_socket'] = $dbInfos['port'];
			unset($dbInfos['host']);
			unset($dbInfos['port']);
		}

		// not used by Zend Framework
		unset($dbInfos['tables_prefix']);
		unset($dbInfos['adapter']);
		unset($dbInfos['schema']);

		$className = self::getAdapterClassName($adapterName);
		$adapter = new $className($dbInfos);
		$adapter->getConnection();

		Zend_Db_Table::setDefaultAdapter($adapter);

		// we don't want the connection information to appear in the logs
		$adapter->resetConfig();

		return $adapter;
	}

	/**
	 * Get adapter class name
	 *
	 * @param string $adapterName
	 * @return string
	 */
	private static function getAdapterClassName($adapterName)
	{
		return 'Piwik_Db_Adapter_' . str_replace(' ', '_', ucwords(str_replace('_', ' ', strtolower($adapterName))));
	}

	/**
	 * Get default port for named adapter
	 *
	 * @param string $adapterName
	 * @return int
	 */
	public static function getDefaultPortForAdapter($adapterName)
	{
		$className = self::getAdapterClassName($adapterName);
		return call_user_func(array($className, 'getDefaultPort'));
	}

	/**
	 * Get list of adapters
	 *
	 * @return array
	 */
	public static function getAdapters()
	{
		static $adapterNames = array(
			// currently supported by Piwik
			'Pdo_Mysql',
			'Mysqli',

			// other adapters supported by Zend_Db
//			'Pdo_Pgsql',
//			'Pdo_Mssql',
//			'Sqlsrv',
//			'Pdo_Ibm',
//			'Db2',
//			'Pdo_Oci',
//			'Oracle',
		);

		$adapters = array();

		foreach($adapterNames as $adapterName)
		{
			$className = 'Piwik_Db_Adapter_'.$adapterName;
			if(call_user_func(array($className, 'isEnabled')))
			{
				$adapters[strtoupper($adapterName)] = call_user_func(array($className, 'getDefaultPort'));
			}
		}

		return $adapters;
	}
}

/**
 * @package Piwik
 */
interface Piwik_Db_Adapter_Interface
{
	/**
	 * Reset the configuration variables in this adapter.
	 */
	public function resetConfig();

	/**
	 * Return default port.
	 *
	 * @return int
	 */
	public static function getDefaultPort();

	/**
	 * Check database server version
	 *
	 * @throws Exception if database version is less than required version
	 */
	public function checkServerVersion();

	/**
	 * Returns true if this adapter's required extensions are enabled
	 *
	 * @return bool
	 */
	public static function isEnabled();

	/**
	 * Returns true if this adapter supports blobs as fields
	 *
	 * @return bool
	 */
	public function hasBlobDataType();

	/**
	 * Test error number
	 *
	 * @param Exception $e
	 * @param string $errno
	 * @return bool
	 */
	public function isErrNo($e, $errno);

	/**
	 * Is the connection character set equal to utf8?
	 *
	 * @return bool
	 */
	public function isConnectionUTF8();
}
