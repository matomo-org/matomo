<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
 *
 * @category Piwik
 * @package Piwik
 */

/**
 * @see libs/PclZip
 */
require_once PIWIK_INCLUDE_PATH . '/libs/PclZip/pclzip.lib.php';

/**
 * Unzip wrapper around PclZip
 *
 * @package Piwik
 * @subpackage Piwik_Unzip
 */
class Piwik_Unzip_PclZip implements Piwik_Unzip_Interface
{
	private $pclzip;
	public $filename;

	function __construct($filename) {
		$this->pclzip = new PclZip($filename);
		$this->filename = $filename;
	}

	/**
	 * A callback extract path
	 *
	 * @param string $p_event
	 * @param array &$p_header
	 * @return int 0 to skip, 1 to resme, or 2 to abort
	 */
	private static $pathExtracted;
	public static function extractPath($p_event, &$p_header)
	{
		return strncmp($p_header['filename'], self::$pathExtracted, strlen(self::$pathExtracted)) ? 0 : 1;
	}

	public function extract($pathExtracted) {
		$pathExtracted = str_replace('\\', '/', $pathExtracted);
		$list = $this->pclzip->listContent();
		foreach($list as $entry) {
			$filename = str_replace('\\', '/', $entry['stored_filename']);
			$parts = explode('/', $filename);

			if(!strncmp($filename, '/', 1) ||
				array_search('..', $parts) !== false ||
				strpos($filename, ':') !== false)
			{
				return 0;
			}
		}

		self::$pathExtracted = $pathExtracted;
		return $this->pclzip->extract(
				PCLZIP_OPT_PATH, $pathExtracted,
				PCLZIP_OPT_STOP_ON_ERROR,
				PCLZIP_CB_PRE_EXTRACT, array('Piwik_Unzip_PclZip', 'extractPath')
		);
	}

	public function errorInfo() {
		return $this->pclzip->errorInfo(true);
	}
}
