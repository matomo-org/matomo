<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id: APIable.php 162 2008-01-14 04:27:21Z matt $
 * 
 * @package Piwik_Visualization
 */

/**
 * Piwik_ViewDataTable must create a $view attribute which implements this interface.
 * 
 * @package Piwik_Visualization
 */
interface Piwik_iView
{
	/**
	 * Outputs the data.
	 * Either outputs html, xml, an image, nothing, etc.
	 * 
	 * @return mixed
	 *
	 */
	function render();
}