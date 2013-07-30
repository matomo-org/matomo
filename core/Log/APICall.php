<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package Piwik
 */
namespace Piwik\Log;

use Piwik\IP;
use Piwik\Log;

/**
 * Class used to log all the API Calls information (class / method / parameters / returned value / time spent)
 *
 * @package Piwik
 * @subpackage Log
 */
class APICall extends Log
{
    const ID = 'logger_api_call';

    /**
     * Constructor
     */
    function __construct()
    {
        $logToFileFilename = self::ID;
        $logToDatabaseTableName = self::ID;
        $logToDatabaseColumnMapping = array(
            'class_name'                     => 'class_name',
            'method_name'                    => 'method_name',
            'parameter_names_default_values' => 'parameter_names_default_values',
            'parameter_values'               => 'parameter_values',
            'execution_time'                 => 'execution_time',
            'caller_ip'                      => 'caller_ip',
            'timestamp'                      => 'timestamp',
            'returned_value'                 => 'returned_value'
        );
        $screenFormatter = new APICallScreenFormatter();
        $fileFormatter = new FileFormatter();

        parent::__construct($logToFileFilename,
            $fileFormatter,
            $screenFormatter,
            $logToDatabaseTableName,
            $logToDatabaseColumnMapping);

        $this->setEventItem('caller_ip', IP::P2N(IP::getIpFromHeader()));
    }

    /**
     * Logs the given api call event with the parameters
     *
     * @param string $className
     * @param string $methodName
     * @param array $parameterNames
     * @param array $parameterValues
     * @param number $executionTime
     * @param mixed $returnedValue
     */
    public function logEvent($className, $methodName, $parameterNames, $parameterValues, $executionTime, $returnedValue)
    {
        $event = array();
        $event['class_name'] = $className;
        $event['method_name'] = $methodName;
        $event['parameter_names_default_values'] = serialize($parameterNames);
        $event['parameter_values'] = serialize($parameterValues);
        $event['execution_time'] = $executionTime;
        $event['returned_value'] = is_array($returnedValue) ? serialize($returnedValue) : $returnedValue;
        parent::log($event, Log::INFO, null);
    }
}

