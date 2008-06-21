<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id: Generator.php 404 2008-03-23 01:09:59Z matt $
 * 
 * @package Piwik_LogStats
 */


/**
 * Fake Piwik_LogStats that:
 * - overwrite the sendHeader method so that no headers are sent.
 * - doesn't print the 1pixel transparent GIF at the end of the visit process
 * - overwrite the logstat_visit object to use so we use our own logstats_visit @see Piwik_LogStats_Generator_Visit
 * 
 * @package Piwik_LogStats
 * @subpackage Piwik_LogStats_Generator
 */
class Piwik_LogStats_Generator_LogStats extends Piwik_LogStats
{
	/**
	 * Does nothing instead of sending headers
	 *
	 * @return void
	 */
	protected function sendHeader($header)
	{
	}
	
	/**
	 * Does nothing instead of displaying a 1x1 transparent pixel GIF
	 *
	 * @return void
	 */
	protected function endProcess()
	{
	}
	
	/**
	 * Returns our 'generator home made' Piwik_LogStats_Generator_Visit object.
	 *
	 * @return Piwik_LogStats_Generator_Visit
	 */
	protected function getNewVisitObject()
	{
		$visit = new Piwik_LogStats_Generator_Visit();
		$visit->setDb(self::$db);
		return $visit;
	}	
	
	static function disconnectDb()
	{
		return;
	}
}