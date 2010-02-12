<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 *
 * @category Piwik
 * @package Updates
 */

/**
 * @package Updates
 */
class Piwik_Updates_0_2_13 extends Piwik_Updates
{
	static function getSql()
	{
		$tables = Piwik::getTablesCreateSql();

		return array(
			'DROP TABLE IF EXISTS `'. Piwik::prefixTable('option') .'`' => false,
			$tables['option'] => false,
		);
	}

	static function update()
	{
		Piwik_Updater::updateDatabase(__FILE__, self::getSql());
	}
}
