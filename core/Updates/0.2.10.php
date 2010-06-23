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
class Piwik_Updates_0_2_10 implements Piwik_iUpdate
{
	static function update()
	{
		$tables = Piwik::getTablesCreateSql();
		Piwik_Updater::updateDatabase(__FILE__, array(
			$tables['option'] => false,
		));

		$obsoleteDirectories = array(
			'/plugins/AdminHome',
			'/plugins/Home',
			'/plugins/PluginsAdmin',
		);
		foreach($obsoleteDirectories as $dir)
		{
			if(file_exists(PIWIK_INCLUDE_PATH . $dir))
			{
				Piwik::unlinkRecursive(PIWIK_INCLUDE_PATH . $dir, true);
			}
		}
	}
}
