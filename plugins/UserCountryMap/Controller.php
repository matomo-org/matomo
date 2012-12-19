<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id: $
 *
 * @category Piwik_Plugins
 * @package Piwik_UserCountryMap
 */

/**
 *
 * @package Piwik_UserCountryMap
 */
class Piwik_UserCountryMap_Controller extends Piwik_Controller
{
	function worldMap()
	{
		if(!Piwik_PluginsManager::getInstance()->isPluginActivated('UserCountry'))
		{
			return '';
		}
		$idSite = Piwik_Common::getRequestVar('idSite', 1, 'int');
		Piwik::checkUserHasViewAccess($idSite);
		
		$period = Piwik_Common::getRequestVar('period');
		$date = Piwik_Common::getRequestVar('date');
		$token_auth = Piwik::getCurrentUserTokenAuth();
		
		$view = Piwik_View::factory('worldmap');

		// will be escaped in the template
		$view->dataUrl = "?module=API"
			. "&method=API.getProcessedReport&format=XML"
			. "&apiModule=UserCountry&apiAction=getCountry"
			. "&idSite=" . $idSite
			. "&period=" . $period
			. "&date=" . $date
			. "&token_auth=" . $token_auth
			. "&segment=" . Piwik_Common::unsanitizeInputValue(Piwik_Common::getRequestVar('segment', ''))
			. "&filter_limit=-1";
		
		// definition of the color scale
		$view->hueMin = 218; 	
		$view->hueMax = 216; 	
		$view->satMin = "0.285"; 	
		$view->satMax = "0.9";
		$view->lgtMin = ".97";
		$view->lgtMax = ".44";
		
		$request = new Piwik_API_Request(
			'method=API.getMetadata&format=PHP'
			. '&apiModule=UserCountry&apiAction=getCountry'
			. '&idSite=' . $idSite
			. '&period=' . $period
			. '&date=' . $date
			. '&token_auth=' . $token_auth
			. '&filter_limit=-1'
		);
		$metaData = $request->process();

		$metrics = array();
		if(!is_array($metaData))
		{
			throw new Exception("Error while requesting Map reports for website " . (int)$idSite);
		}
		else
		{
			foreach ($metaData[0]['metrics'] as $id => $val)
			{
				if (Piwik_Common::getRequestVar('period') == 'day' || $id != 'nb_uniq_visitors') {
					$metrics[] = array($id, $val);
				}
			}
			foreach ($metaData[0]['processedMetrics'] as $id => $val)
			{
				$metrics[] = array($id, $val);
			}
		}
		$view->metrics = $metrics;
		$view->defaultMetric = 'nb_visits';
		echo $view->render();
	}
	
	/*
	 * shows the traditional extra page where the user
	 * is able to download the exported image via right - click
	 *
	 * note: this is a fallback for older flashplayer versions
	 */
	function exportImage()
	{
		Piwik_Proxy_Controller::exportImageWindow();
	}
	
	/*
	 * this outputs the image straight forward and is directly called
	 * via flash download process
	 */
	function outputImage()
	{
		Piwik_Proxy_Controller::outputBinaryImage();
	}
	
	/*
	 * debug mode for worldmap
	 * helps to find JS bugs in IE8
	 */
	/*
	function debug()
	{
		echo '<html><head><title>DEBUG: world map</title>';
		echo '<script type="text/javascript" src="libs/jquery/jquery.js"></script>';
		echo '<script type="text/javascript" src="libs/swfobject/swfobject.js"></script>';
		echo '</head><body><div id="widgetUserCountryMapworldMap" style="width:600px;">';
		echo $this->worldMap();
		echo '</div></body></html>';
	} 
	// */
}
