<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @category Piwik_Plugins
 * @package Piwik_SEO
 */

/**
 * @package Piwik_SEO
 */
class Piwik_SEO extends Piwik_Plugin
{
	public function getInformation()
	{
		return array(
			'description' => 'This Plugin extracts and displays SEO metrics: Alexa web ranking, Google Pagerank, number of Indexed pages and backlinks of the currently selected website.',
			'author' => 'Piwik',
			'author_homepage' => 'http://piwik.org/',
			'version' => Piwik_Version::VERSION,
		);
	}
	
	function getListHooksRegistered()
	{
		$hooks = array( 'WidgetsList.add' => 'addWidgets' );
		return $hooks;
	}	
	
	function addWidgets()
	{
		Piwik_AddWidget('SEO', 'SEO_SeoRankings', 'SEO', 'getRank');
	}
}
