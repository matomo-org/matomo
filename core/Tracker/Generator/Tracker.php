<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id: Generator.php 404 2008-03-23 01:09:59Z matt $
 * 
 * @package Piwik_Tracker
 */


/**
 * Fake Piwik_Tracker that:
 * - overwrite the sendHeader method so that no headers are sent.
 * - doesn't print the 1pixel transparent GIF at the end of the visit process
 * - overwrite the Tracker Visit object to use so we use our own Tracker_visit @see Piwik_Tracker_Generator_Visit
 * 
 * @package Piwik_Tracker
 * @subpackage Piwik_Tracker_Generator
 */
class Piwik_Tracker_Generator_Tracker extends Piwik_Tracker
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
	protected function end()
	{
	}
	
	/**
	 * Returns our 'generator home made' Piwik_Tracker_Generator_Visit object.
	 *
	 * @return Piwik_Tracker_Generator_Visit
	 */
	protected function getNewVisitObject()
	{
		$visit = new Piwik_Tracker_Generator_Visit();
		return $visit;
	}	
	
	static function disconnectDatabase()
	{
		return;
	}
}