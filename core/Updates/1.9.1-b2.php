<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id: 1.9.1-b19.php $
 *
 * @category Piwik
 * @package Updates
 */

/**
 * @package Updates
 */
class Piwik_Updates_1_9_1_b2 extends Piwik_Updates
{
	static function getSql($schema = 'Myisam')
	{
		return array(
			'ALTER TABLE '.Piwik_Common::prefixTable('site'). " DROP `feedburnerName`" => 1091
		);
	}
	
	static function update()
	{
		// manually remove ExampleFeedburner column
		Piwik_Updater::updateDatabase(__FILE__, self::getSql());

		// remove ExampleFeedburner plugin
		$pluginToDelete = 'ExampleFeedburner';
		self::deletePluginFromConfigFile($pluginToDelete);
	}
}
