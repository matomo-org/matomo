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
class Piwik_Updates_0_6 extends Piwik_Updates
{
	static function getSql()
	{
		return array(
			'ALTER TABLE ' . Piwik::prefixTable('site') . ' ADD `excluded_ips` TEXT NOT NULL AFTER `ts_created` ;' => false,
		);
	}

	static function update()
	{
		Piwik_Updater::updateDatabase(__FILE__, self::getSql());
	}
}
