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
class Piwik_Updates_0_6_2 extends Piwik_Updates
{
	static function update($adapter = 'PDO_MYSQL')
	{
		$obsoleteFiles = array(
			PIWIK_INCLUDE_PATH . '/core/Db/Mysqli.php',
		);
		foreach($obsoleteFiles as $obsoleteFile)
		{
			if(file_exists($obsoleteFile))
			{
				@unlink($obsoleteFile);
			}
		}

		$obsoleteDirectories = array(
			PIWIK_INCLUDE_PATH . '/core/Db/Pdo',
		);
		foreach($obsoleteDirectories as $dir)
		{
			if(file_exists($dir))
			{
				Piwik::unlinkRecursive($dir, true);
			}
		}
	}
}
