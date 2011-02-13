<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
 * 
 * @category Piwik_Plugins
 * @package Piwik_API
 */

/**
 * 
 * @package Piwik_API
 */
class Piwik_API_Controller extends Piwik_Controller
{
	function index()
	{
		// when calling the API through http, we limit the number of returned results
		if(!isset($_GET['filter_limit']))
		{
			$_GET['filter_limit'] = Zend_Registry::get('config')->General->API_datatable_default_limit;
		}
		$request = new Piwik_API_Request('token_auth='.Piwik_Common::getRequestVar('token_auth', 'anonymous', 'string'));
		echo $request->process();
	}

	public function listAllMethods()
	{
		$ApiDocumentation = new Piwik_API_DocumentationGenerator();
		echo $ApiDocumentation->getAllInterfaceString( $outputExampleUrls = true, $prefixUrls = Piwik_Common::getRequestVar('prefixUrl', '') );
	}
	
	public function listAllAPI()
	{
		$view = Piwik_View::factory("listAllAPI");
		$this->setGeneralVariablesView($view);
		
		$ApiDocumentation = new Piwik_API_DocumentationGenerator();
		$view->countLoadedAPI = Piwik_API_Proxy::getInstance()->getCountRegisteredClasses();
		$view->list_api_methods_with_links = $ApiDocumentation->getAllInterfaceString();
		echo $view->render();
	}
	
	public function listSegments()
	{
		$segments = Piwik_API_API::getInstance()->getSegmentsMetadata($this->idSite);
		
		$tableDimensions = $tableMetrics = '';
		$customVariables=0;
		$lastCategory=array();
		foreach($segments as $segment)
		{
			$customVariableWillBeDisplayed = in_array($segment['segment'], $onlyDisplay = array('customVariableName1', 'customVariableName2', 'customVariableValue1', 'customVariableValue2'));
			// Don't display more than 4 custom variables name/value rows
			if($segment['category'] == 'Custom Variables'
				&& !$customVariableWillBeDisplayed)
			{ 
				continue;
			}
			
			$thisCategory = $segment['category'];
			$output = '';
			if(empty($lastCategory[$segment['type']]) 
				|| $lastCategory[$segment['type']] != $thisCategory)
			{
				$output .= '<tr><td colspan="2"><b>'.$thisCategory.'</b></td></tr>';
			}
			
			$lastCategory[$segment['type']] = $thisCategory;
			
			$exampleValues = isset($segment['acceptedValues']) 
								? 'Example values: <code>'.$segment['acceptedValues'].'</code>' 
								: '';
			$output .= '<tr>
							<td>'.$segment['segment'].'</td>
							<td>'.$segment['name'] .'<br/>'.$exampleValues.' </td>
						</tr>';
			
			// Show only 2 custom variables and display message for rest
			if($customVariableWillBeDisplayed)
			{
				$customVariables++;
    			if($customVariables == 4)
    			{
    				$output .= '<tr><td> There are 5 custom variables available, so you can segment across any segment name and value range.
    						<br/>For example, <code>customVariableName1==Type;customVariableValue1==Customer</code>
    						<br/>Returns all visitors that have the Custom Variable "Type" set to "Customer".
    						</td></tr>';
    			}
			}
			
			
			if($segment['type'] == 'dimension') {
				$tableDimensions .= $output;
			} else {
				$tableMetrics .= $output;
			}
		}
		
		echo "
		<b>Dimensions</b>
		<table>
		$tableDimensions
		</table>
		<br/>
		<b>Metrics</b>
		<table>
		$tableMetrics
		</table>
		";
	}
}
