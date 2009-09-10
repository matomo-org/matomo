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
	 * @return mixed (Piwik_Db_Mysqli, Piwik_Db_Pdo_Mysql, etc)
	 */
	public static function factory($adapterName, $config)
	{
		$adapterName = 'Piwik_Db_' . str_replace(' ', '_', ucwords(str_replace('_', ' ', strtolower($adapterName))));
		$adapter = new $adapterName($config);
		return $adapter;
	}
}
