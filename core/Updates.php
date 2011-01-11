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
 * Abstract class for update scripts
 *
 * @example core/Updates/0.4.2.php
 * @package Piwik
 */
abstract class Piwik_Updates
{
	/**
	 * Return SQL to be executed in this update
	 *
	 * @param string Schema name
	 * @return array( 
	 * 		'INSERT .... ' => true,// if an error occurs during the query, it will be ignored 
	 * 		'ALTER .... ' => false // if an error occurs, the update will stop and fail 
	 *                             // and user will have to manually runthe query 
	 * )
	 */
	static function getSql($schema = 'Myisam')
	{
		return array();
	}

	/**
	 * Incremental version update
	 */
	static function update()
	{
	}
}
