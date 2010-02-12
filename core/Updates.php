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
 * Abstract class for update scripts
 *
 * @example core/Updates/0.4.2.php
 * @package Piwik
 */
abstract class Piwik_Update
{
	/**
	 * Return SQL to be executed in this update
	 *
	 * @return array
	 */
	static function getSql()
	{
		return array();
	}

	/**
	 * Incremental version update
	 */
	abstract static function update();
}
