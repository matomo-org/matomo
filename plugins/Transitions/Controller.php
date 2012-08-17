<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
 * 
 * @category Piwik_Plugins
 * @package Piwik_Transitions
 */

/**
 * @package Piwik_Transitions
 */
class Piwik_Transitions_Controller extends Piwik_Controller
{
	
	public function renderPopover()
	{
		$view = Piwik_View::factory('transitions');
		echo $view->render();
	}
	
}
