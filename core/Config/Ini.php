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
 * Subclasses Zend_Config_Ini so we can use our own parse_ini_file() wrapper.
 *
 * @package Piwik
 * @subpackage Piwik_Config
 */
class Piwik_Config_Ini extends Zend_Config_Ini
{
	/**
	 * Handle any errors from parse_ini_file
	 *
	 * @param integer $errno
	 * @param string $errstr
	 * @param string $errfile
	 * @param integer $errline
	 */
	public function _parseFileErrorHandler($errno, $errstr, $errfile, $errline)
	{
		$this->_loadFileErrorHandler($errno, $errstr, $errfile, $errline);
	}

	/**
	 * Load ini file configuration
	 *
	 * Derived from Zend_Config_Ini->_loadIniFile() and Zend_Config_Ini->_parseIniFile()
	 * @license New BSD License
	 *
	 * @param string $filename
	 * @return array
	 */
	protected function _loadIniFile($filename)
	{
		set_error_handler(array($this, '_parseFileErrorHandler'));
		$iniArray = _parse_ini_file($filename, true);
		restore_error_handler();
		// Check if there was an error while loading the file
		if ($this->_loadFileErrorStr !== null) {
			throw new Zend_Config_Exception($this->_loadFileErrorStr);
		}

		return $iniArray;
	}
}
