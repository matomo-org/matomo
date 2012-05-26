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
 * Unzip wrapper around ZipArchive and PclZip
 *
 * @package Piwik
 */
class Piwik_Unzip
{
	/**
	 * Factory method to create an unarchiver
	 *
	 * @param string  $name      Name of unarchiver
	 * @param string  $filename  Name of .zip archive
	 * @return Piwik_Unzip_Interface
	 */
	static public function factory($name, $filename)
	{
		switch($name)
		{
			case 'ZipArchive':
				if(class_exists('ZipArchive', false))
					return new Piwik_Unzip_ZipArchive($filename);

			case 'PclZip':
			default:
				return new Piwik_Unzip_PclZip($filename);
		}
	}
}
