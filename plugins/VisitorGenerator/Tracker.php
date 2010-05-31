<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @category Piwik_Plugin
 * @package Piwik_VisitorGenerator
 */

/**
 * Fake Piwik_Tracker that:
 * - overwrite the sendHeader method so that no headers are sent.
 * - doesn't print the 1pixel transparent GIF at the end of the visit process
 * - overwrite the Tracker Visit object to use so we use our own Tracker_visit @see Piwik_Tracker_Generator_Visit
 * 
 * @package Piwik_VisitorGenerator
 */
class Piwik_VisitorGenerator_Tracker extends Piwik_Tracker
{
	/**
	 * Does nothing instead of sending headers
	 */
	protected function sendHeader($header)
	{
	}
	
	/**
	 * Does nothing instead of displaying a 1x1 transparent pixel GIF
	 */
	protected function end()
	{
	}
	
	/**
	 * Returns our 'generator home made' Piwik_VisitorGenerator_Visit object.
	 *
	 * @return Piwik_VisitorGenerator_Visit
	 */
	protected function getNewVisitObject()
	{
		$visit = new Piwik_VisitorGenerator_Visit();
		$visit->generateTimestamp();
		return $visit;
	}	
	
	static function disconnectDatabase()
	{
		return;
	}
}
