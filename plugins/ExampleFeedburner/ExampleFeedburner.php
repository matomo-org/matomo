<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @category Piwik_Plugins
 * @package Piwik_ExampleFeedburner
 */

/**
 *
 * @package Piwik_ExampleFeedburner
 */
class Piwik_ExampleFeedburner extends Piwik_Plugin
{
	public function getInformation()
	{
		return array(
			'name' => 'ExampleFeedburner',
			'description' => Piwik_Translate('ExampleFeedburner_PluginDescription'),
			'author' => 'Piwik',
			'author_homepage' => 'http://piwik.org/',
			'version' => '0.1',
		);
	}

	function install()
	{
		try{
			Piwik_Exec('ALTER TABLE '.Piwik::prefixTable('site'). " ADD `feedburnerName` VARCHAR( 100 ) DEFAULT NULL");
		} catch(Exception $e){
			// mysql code error 1060: column already exists
			// if there is another error we throw the exception, otherwise it is OK as we are simply reinstalling the plugin
			if(!Zend_Registry::get('db')->isErrNo($e, '1060'))
			{
				throw $e;
			}
		}
	}
	
	function uninstall()
	{
		Piwik_Query('ALTER TABLE '.Piwik::prefixTable('site'). " DROP `feedburnerName`");
	}
}

Piwik_AddWidget('Example Widgets', 'Feedburner statistics', 'ExampleFeedburner', 'feedburner');

/**
 *
 * @package Piwik_ExampleFeedburner
 */
class Piwik_ExampleFeedburner_Controller extends Piwik_Controller
{

	/**
	 * Simple feedburner statistics output
	 *
	 */
	function feedburner()
	{
		$view = Piwik_View::factory('feedburner');
		$idSite = Piwik_Common::getRequestVar('idSite',1,'int');
		$feedburnerFeedName = Piwik_FetchOne('SELECT feedburnerName FROM '.Piwik::prefixTable('site').
								' WHERE idsite = ?', $idSite );
		if(empty($feedburnerFeedName))
		{
			$feedburnerFeedName = 'Piwik';
		}
		$view->feedburnerFeedName = $feedburnerFeedName;
		$view->idSite = $idSite;
		$view->fbStats = $this->getFeedData($feedburnerFeedName);
		echo $view->render();
	}
	

	/**
	 * Returns array of counts and images based on Feedburner URI
	 * 
	 * @param string $uri
	 * @return array()
	 */
	protected function getFeedData($uri)
	{
		// Awareness API only supports yesterday and back   
		// we get stats for previous two days
		// http://code.google.com/apis/feedburner/awareness_api.html#dates
		$yesterday = date('Y-m-d',mktime(0, 0, 0, date("m"), date("d")-1,   date("Y")));
		$beforeYesterday = date('Y-m-d',mktime(0, 0, 0, date("m"), date("d")-2,   date("Y")));
		
		//create url to gather XML feed from
		$url = 'http://feedburner.google.com/api/awareness/1.0/GetFeedData?uri='.$uri.'&dates='.$beforeYesterday.','.$yesterday.'';
		$data = Piwik::sendHttpRequest($url, 5);
		try {
			$xml = new SimpleXMLElement($data);
		} catch(Exception $e) {
			return "Error parsing the data for feed $uri. Fetched data was: \n'". $data."'";
		}
		
		if(count($xml->feed->entry) != 2) {
			return "Error fetching the Feedburner stats. Expected XML, Got: \n" . strip_tags($data);
		}
		$data = array();
		$i = 0;
		foreach($xml->feed->entry as $feedDay){
			$data[0][$i] = (int)$feedDay['circulation'];
			$data[1][$i] = (int)$feedDay['hits'];
			$data[2][$i] = (int)$feedDay['reach'];
			$i++;
		}
	
		foreach($data as $key => $value) {
			if( $value[0] == $value[1]) {
				$img = 'stop.png';
			} else if($value[0] < $value[1]) {
				$img = 'arrow_up.png';
			} else {
				$img = 'arrow_down.png';
			}
			
			$prefixImage = '<img alt="" src="./plugins/MultiSites/images/';
			$suffixImage = '" />';
			$data[$key][2] = $prefixImage . $img . $suffixImage;
		}
		return $data;
	}
	
	/**
	 * Function called to save the Feedburner ID entered in the form
	 *
	 */
	function saveFeedburnerName()
	{
		// we save the value in the DB for an authenticated user
		if(Piwik::getCurrentUserLogin() != 'anonymous')
		{
			Piwik_Query('UPDATE '.Piwik::prefixTable('site').' 
						 SET feedburnerName = ? WHERE idsite = ?', 
				array(Piwik_Common::getRequestVar('name','','string'), Piwik_Common::getRequestVar('idSite',1,'int'))
				);
		}
	}
}
