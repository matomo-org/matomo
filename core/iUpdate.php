<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id: $
 * 
 * @package Piwik
 */

/**
 * @package Piwik
 */
interface Piwik_iUpdate
{
	/**
	 * Incremental version update
	 */
	static function update();
}
