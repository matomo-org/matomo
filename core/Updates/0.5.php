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
class Piwik_Updates_0_5 implements Piwik_iUpdate
{
	static function update()
	{
		$obsoleteFile = '/libs/open-flash-chart/php-ofc-library/ofc_upload_image.php';
		if(file_exists(PIWIK_DOCUMENT_ROOT . $obsoleteFile))
		{
			@unlink(PIWIK_DOCUMENT_ROOT . $obsoleteFile);
		}
	}
}
