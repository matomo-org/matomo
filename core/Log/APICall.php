<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id: APICall.php 485 2008-05-19 22:29:55Z matt $
 * 
 * @package Piwik_Log
 * @subpackage Piwik_Log_APICall
 */

/**
 * Class used to log all the API Calls information (class / method / parameters / returned value / time spent)
 * 
 * @package Piwik_Log
 * @subpackage Piwik_Log_APICall
 */
class Piwik_Log_APICall extends Piwik_Log
{
	const ID = 'logger_api_call';

	function __construct()
	{
		$logToFileFilename = self::ID;
		$logToDatabaseTableName = self::ID;
		$logToDatabaseColumnMapping = null;
		$screenFormatter = new Piwik_Log_Formatter_APICall_ScreenFormatter;
		$fileFormatter = new Piwik_Log_Formatter_FileFormatter;
		
		parent::__construct($logToFileFilename, 
							$fileFormatter,
							$screenFormatter,
							$logToDatabaseTableName, 
							$logToDatabaseColumnMapping );
		
		$this->setEventItem('caller_ip', ip2long( Piwik_Common::getIp() ) );
	}
	
	function log( $className, $methodName, $parameterNames,	$parameterValues, $executionTime, $returnedValue)
	{
		$event = array();
		$event['class_name'] = $className;
		$event['method_name'] = $methodName;
		$event['parameter_names_default_values'] = serialize($parameterNames);
		$event['parameter_values'] = serialize($parameterValues);
		$event['execution_time'] = $executionTime;
		$event['returned_value'] = is_array($returnedValue) ? serialize($returnedValue) : $returnedValue;
		
		parent::log($event);
	}
}

/**
 * Class used to format the API Call log on the screen. 
 * 
 * @package Piwik_Log
 * @subpackage Piwik_Log_APICall
 */
class Piwik_Log_Formatter_APICall_ScreenFormatter extends Piwik_Log_Formatter_ScreenFormatter 
{
	/**
     * Formats data into a single line to be written by the writer.
     *
     * @param  array    $event    event data
     * @return string             formatted line to write to the log
     */
    public function format($event)
    {
    	$str =  "\n<br> ";
    	$str .= "Called: {$event['class_name']}.{$event['method_name']} (took {$event['execution_time']}ms) \n<br>";
    	$str .= "Parameters: ";
    	$parameterNamesAndDefault = unserialize($event['parameter_names_default_values']);
    	$parameterValues = unserialize($event['parameter_values']);
    	
    	$i = 0; 
    	foreach($parameterNamesAndDefault as $pName => $pDefault)
    	{
    		if(isset($parameterValues[$i]))
    		{
	    		$currentValue = $parameterValues[$i];
    		}
    		else
    		{
    			$currentValue = $pDefault;
    		}
    		
    		$currentValue = $this->formatValue($currentValue);
    		$str .= "$pName = $currentValue, ";
    		
    		$i++;
    	}
    	$str .=  "\n<br> ";
    	
//    	$str .= "Returned: ".$this->formatValue($event['returned_value']);
    	$str .=  "\n<br> ";
    	return parent::format($str);
    }
    
    private function formatValue( $value )
    {
    	if(is_string($value))
		{
			$value = "'$value'";
		}
		if(is_null($value))
		{
			$value= 'null';
		}
		if(is_array($value))
		{
			$value = "array( ".implode(", ", $value). ")";
		}
		return $value;
		
    }
}

