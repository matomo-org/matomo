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
class Piwik_Updates_0_5_5 implements Piwik_iUpdate
{
	static function update()
	{
		Piwik_Updater::updateDatabase(__FILE__, array(
			'DROP INDEX index_idsite_date ON ' . Piwik::prefixTable('log_visit') => '1091',
			'CREATE INDEX index_idsite_date_config ON ' . Piwik::prefixTable('log_visit') . ' (idsite, visit_server_date, config_md5config(8))' => '1061',
		));
		
	}
}
