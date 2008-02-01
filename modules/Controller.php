<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @package Piwik
 */

/**
 * 
 * @package Piwik_
 */
abstract class Piwik_Controller
{
	function __construct()
	{
	
		$this->strDate = Piwik_Common::getRequestVar('date', 'yesterday','string');
		
		// the date looks like YYYY-MM-DD we can build it
		try{
			$this->date = Piwik_Date::factory($this->strDate);
			$this->strDate = $this->date->toString();
		} catch(Exception $e){
		// the date looks like YYYY-MM-DD,YYYY-MM-DD or other format
			// case the date looks like a range
			$this->date = null;
		}
	}
	
	function getDefaultAction()
	{
		return 'index';
	}
	
	/* FACTORING // COPIED FROM Home_Controller */
	protected function renderView($view, $fetch)
	{
		$view->main();
		$rendered = $view->getView()->render();
		if($fetch)
		{
			return $rendered;
		}
		echo $rendered;
	}
	

	
	protected function getLastUnitGraph($currentModuleName, $currentControllerAction, $apiMethod)
	{
		require_once "ViewDataTable/Graph.php";
		$view = Piwik_ViewDataTable::factory(null, 'graphEvolution');
		$view->init( $currentModuleName, $currentControllerAction, $apiMethod );
		
		// if the date is not yet a nicely formatted date range ie. YYYY-MM-DD,YYYY-MM-DD we build it
		// otherwise the current controller action is being called with the good date format already so it's fine
		// see constructor
		if( !is_null($this->date))
		{
			$view->setParametersToModify( $this->getGraphParamsModified( array('date'=>$this->strDate)));
		}
		
		return $view;
	}
	
	protected function getNumericValue( $methodToCall )
	{
		$requestString = 'method='.$methodToCall.'&format=original';
		$request = new Piwik_API_Request($requestString);
		return $request->process();
	}

	protected function getUrlSparkline( $action )
	{
		$params = $this->getGraphParamsModified( 
					array(	'viewDataTable' => 'sparkline', 
							'action' => $action)
				);
		$url = Piwik_Url::getCurrentQueryStringWithParametersModified($params);
		return $url;
	}
	
	
	
	/**
	 * 
	 * @param array  paramsToSet = array( 'date' => 'last50', 'viewDataTable' =>'sparkline' )
	 */
	protected function getGraphParamsModified($paramsToSet = array())
	{
		if(!isset($paramsToSet['range']))
		{
			$range = 'last30';
		}
		else
		{
			$range = $paramsToSet['range'];
		}
		
		if(!isset($paramsToSet['date']))
		{
			$endDate = $this->strDate;
		}
		else
		{
			$endDate = $paramsToSet['date'];
		}
		
		if(!isset($paramsToSet['period']))
		{
			$period = Piwik_Common::getRequestVar('period');
		}
		else
		{
			$period = $paramsToSet['period'];
		}
		
		$last30Relative = new Piwik_Period_Range($period, $range );
		
		$last30Relative->setDefaultEndDate(new Piwik_Date($endDate));
		
		$paramDate = $last30Relative->getDateStart()->toString() . "," . $last30Relative->getDateEnd()->toString();
		
		$params = array_merge($paramsToSet , array(	'date' => $paramDate ) );
		
		return $params;
	}
	
}