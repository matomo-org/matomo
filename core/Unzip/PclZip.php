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

	public function extract($pathExtracted) {
		$pathExtracted = str_replace('\\', '/', $pathExtracted);
		$list = $this->pclzip->listContent();
		if (empty($list))
		{
			return 0;
		}

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

		// PCLZIP_CB_PRE_EXTRACT callback returns 0 to skip, 1 to resume, or 2 to abort
		return $this->pclzip->extract(
				PCLZIP_OPT_PATH, $pathExtracted,
				PCLZIP_OPT_STOP_ON_ERROR,
				PCLZIP_OPT_REPLACE_NEWER,
				PCLZIP_CB_PRE_EXTRACT, create_function(
					'$p_event, &$p_header',
					"return strncmp(\$p_header['filename'], '$pathExtracted', strlen('$pathExtracted')) ? 0 : 1;"
				)
		);
	}

	public function errorInfo() {
		return $this->pclzip->errorInfo(true);
	}
}
