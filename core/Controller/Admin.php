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
 * Parent class of all plugins Controllers with admin functions
 * 
 * @package Piwik
 */
abstract class Piwik_Controller_Admin extends Piwik_Controller
{
	/**
	 * Used by Admin screens
	 * 
	 * @param Piwik_View $view
	 */
	protected function setBasicVariablesView($view)
	{
		parent::setBasicVariablesView($view);

		$view->currentAdminMenuName = Piwik_GetCurrentAdminMenuName();

		$view->enableFrames = Zend_Registry::get('config')->General->enable_framed_settings;
		if(!$view->enableFrames)
		{
			$view->setXFrameOptions('sameorigin');
		}
	}
}
