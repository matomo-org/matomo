<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
 * 
 * @category Piwik_Plugins
 * @package Piwik_ImageGraph
 */

class Piwik_ImageGraph extends Piwik_Plugin
{

	public function getInformation()
	{
		return array(
			'description' => Piwik_Translate('ImageGraph_PluginDescription') 
					. ' Debug: <a href="'.Piwik_Url::getCurrentQueryStringWithParametersModified(
							array('module'=> 'ImageGraph', 'action' => 'index'))
					. '">All images</a>',
			'author' => 'Piwik',
			'author_homepage' => 'http://piwik.org/',
			'version' => Piwik_Version::VERSION
		);
	}
	
	function getListHooksRegistered()
	{
		$hooks = array(
			'API.getReportMetadata.end.end' => 'getReportMetadata',
		);
		return $hooks;
	}
	
	// Number of periods to plot on an evolution graph
	const GRAPH_EVOLUTION_LAST_PERIODS = 30;
	
	public function getReportMetadata($notification)
	{
		$info = $notification->getNotificationInfo();
		$reports = &$notification->getNotificationObject();
		$idSites = $info['idSites'];
		// If only one website is selected, we add the Graph URL
		if(count($idSites) != 1)
		{
			return;
		}
		$idSite = reset($idSites);
	
		// in case API.getReportMetadata was not called with date/period we use sane defaults 
		if(empty($info['period'])) 
		{
			$info['period'] = 'day';
		}
		if(empty($info['date'])) 
		{
			$info['date'] = 'today';
		}
		// process the date parameter that will allow to plot the Evolution graph over multiple periods 
		// rather than for just 1 day
		$lastN = 'last' . self::GRAPH_EVOLUTION_LAST_PERIODS;
		$dateLastN = $info['date'];
		
		// If the date is not already a range, then we process the range to plot on Graph
		if($info['period'] != 'range')
		{
			if(!Piwik_Archive::isMultiplePeriod($info['date'], $info['period']))
			{
				$dateLastN = Piwik_Controller::getDateRangeRelativeToEndDate($info['period'], $lastN, $info['date'], new Piwik_Site($idSite));
			}
			// Period is not range, but date is already date1,date2 format
			// so we draw the graph over the requested range
			else
			{
				$info['period'] = 'range';
			}
		}
		$token_auth = Piwik_Common::getRequestVar('token_auth', false);
		
		$urlPrefix = "index.php?";
		foreach($reports as &$report)
		{
			$parameters = array();
			$parameters['module'] = 'API';
			$parameters['method'] = 'ImageGraph.get';
			$parameters['idSite'] = $idSite;
			$parameters['apiModule'] = $report['module'];
			$parameters['apiAction'] = $report['action'];
			if(!empty($token_auth))
			{
				$parameters['token_auth'] = $token_auth;
			}
			$parameters['graphType'] = 'verticalBar';
			$parameters['period'] = $info['period'];
			$parameters['date'] = $info['date'];
			
			// Forward custom Report parameters to the graph URL 
			if(!empty($report['parameters']))
			{
				$parameters = array_merge($parameters, $report['parameters']);
			}
			if(empty($report['dimension']))
			{
				$parameters['graphType'] = 'evolution';
				
				// If period == range, then date is already a date range
				if($info['period'] != 'range')
				{
					$parameters['date'] = $dateLastN;
				}
			}
			
			$report['imageGraphUrl'] = $urlPrefix . Piwik_Url::getQueryStringFromParameters($parameters);
		}
			
	}
}