<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 *
 * @category Piwik_Plugins
 * @package Piwik_MultiSites
 */

/**
 *
 * @package Piwik_MultiSites
 */
class Piwik_MultiSites extends Piwik_Plugin
{
	public function getInformation()
	{
		return array(
			'name' => 'MultiSites',
			'description' => 'Displays multi-site executive summary/statistics. Currently maintained as a core Piwik plugin.',
			'author' => 'Brand New Media LLC',
			'author_homepage' => "http://blogvertising.pl/",
			'version' => Piwik_Version::VERSION,
		);
	}

	public function getListHooksRegistered()
	{
		return array(
			'template_css_import' => 'css',
			'template_js_import' => 'js',
		);
	}

	public function css()
	{
		echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"plugins/MultiSites/templates/styles.css\" />\n";
	}

	public function js()
	{
		echo "<script type=\"text/javascript\" src=\"plugins/MultiSites/templates/common.js\"></script>\n";
	}
}
