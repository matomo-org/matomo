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
class Piwik_Db
{
	/**
	 * Create adapter
	 *
	 * @return mixed (Piwik_Db_Mysqli, Piwik_Db_Pdo_Mysql, etc)
	 */
	public static function factory($adapterName, $config)
	{
		$adapterName = 'Piwik_Db_' . str_replace(' ', '_', ucwords(str_replace('_', ' ', strtolower($adapterName))));
		$adapter = new $adapterName($config);
		return $adapter;
	}


	/**
	 * Get list of adapters
	 *
	 * @return array
	 */
	public static function getAdapters()
	{
		$path = PIWIK_INCLUDE_PATH . '/core/Db';
		$pathLength = strlen($path) + 1;
		$adapters = Piwik::globr($path, '*.php');
		$adapterNames = array();
		foreach($adapters as $adapter)
		{
			$adapterName = str_replace('/', '_', substr($adapter, $pathLength, -strlen('.php')));
			$className = 'Piwik_Db_'.$adapterName;
			if(call_user_func(array($className, 'isEnabled')))
			{
				$adapterNames[strtoupper($adapterName)] = call_user_func(array($className, 'getDefaultPort'));
			}
		}
		return $adapterNames;
	}
}

interface Piwik_Db_iAdapter
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
}
