<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 *
 * @category Piwik_Plugin
 * @package Piwik_VisitorGenerator
 */

/**
 * 
 * @package Piwik_VisitorGenerator
 */
class Piwik_VisitorGenerator extends Piwik_Plugin {

	public function getInformation() {
		$info = array(
				'description' => Piwik_Translate('VisitorGenerator_PluginDescription'),
				'author' => 'Piwik',
				'author_homepage' => 'http://piwik.org/',
				'version' => Piwik_Version::VERSION,
		);
		return $info;
	}

	public function getListHooksRegistered() {
		return array(
				'AdminMenu.add' => 'addMenu',
		);
	}

	public function addMenu() {
		Piwik_AddAdminMenu(
				'VisitorGenerator_VisitorGenerator',
				array('module' => 'VisitorGenerator', 'action' => 'index'),
				Piwik::isUserIsSuperUser(),
				$order = 10
		);
	}
}
?>
