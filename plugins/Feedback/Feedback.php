<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @category Piwik_Plugins
 * @package Piwik_Feedback
 */

/**
 *
 * @package Piwik_Feedback
 */
class Piwik_Feedback extends Piwik_Plugin
{
	public function getInformation()
	{
		return array(
			'name' => 'Feedback',
			'description' => 'Send your Feedback to the Piwik Team in one click. Share your ideas and suggestions with us!',
			'author' => 'Piwik',
			'author_homepage' => 'http://piwik.org/',
			'version' => Piwik_Version::VERSION,
		);
	}

	function getListHooksRegistered()
	{
		return array(
			'template_js_import' => 'js',
		);
	}
	
	function js()
	{
		echo "<script type=\"text/javascript\" src=\"plugins/Feedback/templates/feedback.js\"></script>\n";
	}
}
