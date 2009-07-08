<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @package Piwik_API
 */

/**
 * Proxy is a singleton that has the knowledge of every method available, their parameters 
 * and default values.
 * Proxy receives all the API calls requests via call() and forwards them to the right 
 * object, with the parameters in the right order. 
 * 
 * It will also log the performance of API calls (time spent, parameter values, etc.) if logger available
 * 
 * @package Piwik_API
 */
class Piwik_API_Proxy
{
	// array of already registered plugins names
	protected $alreadyRegistered = array();
	
	private $metadataArray = array();
	
	// when a parameter doesn't have a default value we use this constant
	const NO_DEFAULT_VALUE = null;

	static private $instance = null;
	protected function __construct() {}
	
	/**
	 * Singleton, returns instance
	 *
	 * @return Piwik_API_Proxy
	 */
	static public function getInstance()
	{
		if (self::$instance == null)
		{            
			$c = __CLASS__;
			self::$instance = new $c();
		}
		return self::$instance;
	}
	
	/**
	 * Returns array containing reflection meta data for all the loaded classes
	 * eg. number of parameters, method names, etc.
	 * 
	 * @return array
	 */
	public function getMetadata()
	{
		return $this->metadataArray;
	}
	
	/**
	 * Registers the API information of a given module.
	 * 
	 * The module to be registered must be
	 * - a singleton (providing a getInstance() method)
	 * - the API file must be located in plugins/ModuleName/API.php
	 *   for example plugins/Referers/API.php
	 * 
	 * The method will introspect the methods, their parameters, etc. 
	 * 
	 * @param string ModuleName eg. "Piwik_UserSettings_API"
	 */
	public function registerClass( $className )
	{
		if(isset($this->alreadyRegistered[$className]))
		{
			return;
		}
		$this->includeApiFile( $className );
		$this->checkClassIsSingleton($className);
			
		$rClass = new ReflectionClass($className);
		foreach($rClass->getMethods() as $method)
		{
			$this->loadMethodMetadata($className, $method);
		}
		
		$this->alreadyRegistered[$className] = true;
	}
	
	/**
	 * Returns number of classes already loaded 
	 * @return int
	 */
	public function getCountRegisteredClasses()
	{
		return count($this->alreadyRegistered);
	}

	/**
	 * Will execute $className->$methodName($parametersValues)
	 * If any error is detected (wrong number of parameters, method not found, class not found, etc.)
	 * it will throw an exception
	 * 
	 * It also logs the API calls, with the parameters values, the returned value, the performance, etc.
	 * You can enable logging in config/global.ini.php (log_api_call)
	 * 
	 * @param string The class name (eg. Piwik_Referers_API)
	 * @param string The method name
	 * @param array The parameters pairs (name=>value)
	 * 
	 * @throws Piwik_Access_NoAccessException 
	 */
	public function call($className, $methodName, $parametersRequest )
	{
		$returnedValue = null;
		
		try {
			$this->registerClass($className);

			// instanciate the object
			$object = call_user_func(array($className, "getInstance"));

			// check method exists
			$this->checkMethodExists($className, $methodName);
			
			// get the list of parameters required by the method
			$parameterNamesDefaultValues = $this->getParametersList($className, $methodName);
			
			// load parameters in the right order, etc.
			$finalParameters = $this->getRequestParametersArray( $parameterNamesDefaultValues, $parametersRequest );

			// all parameters to the function call must be non null
			$this->checkParametersAreNotNull($className, $methodName, $finalParameters);

			// start the timer
			$timer = new Piwik_Timer;
			
			// call the method
			$returnedValue = call_user_func_array(array($object, $methodName), $finalParameters);
			
			// log the API Call
			Zend_Registry::get('logger_api_call')->logEvent(
								$className,
								$methodName,
								$parameterNamesDefaultValues,
								$finalParameters,
								$timer->getTimeMs(),
								$returnedValue
							);
		}
		catch( Piwik_Access_NoAccessException $e) {
			throw $e;
		}

		return $returnedValue;
	}
	
	/**
	 * Returns the parameters names and default values for the method $name 
	 * of the class $class
	 * 
	 * @param string The class name
	 * @param string The method name
	 * @return array Format array(
	 * 					'testParameter'		=> null, // no default value
	 * 					'life'				=> 42, // default value = 42
	 * 					'date'				=> 'yesterday',
	 * 				);
	 */
	public function getParametersList($class, $name)
	{
		return $this->metadataArray[$class][$name]['parameters'];
	}
	
	/**
	 * Returns the 'moduleName' part of 'Piwik_moduleName_API' classname 
	 * @param string "Piwik_Referers_API"
	 * @return string "Referers"
	 */ 
	public function getModuleNameFromClassName( $className )
	{
		return str_replace(array('Piwik_', '_API'), '', $className);
	}
	
	/**
	 * Returns an array containing the values of the parameters to pass to the method to call
	 *
	 * @param array array of (parameter name, default value)
	 * @return array values to pass to the function call
	 * @throws exception If there is a parameter missing from the required function parameters
	 */
	private function getRequestParametersArray( $requiredParameters, $parametersRequest )
	{
		$finalParameters = array();
		foreach($requiredParameters as $name => $defaultValue)
		{
			try{
				if($defaultValue === Piwik_API_Proxy::NO_DEFAULT_VALUE)
				{
					try {
						$requestValue = Piwik_Common::getRequestVar($name, null, null, $parametersRequest);
					} catch(Exception $e) {
						$requestValue = null;
					}
				}
				else
				{
					$requestValue = Piwik_Common::getRequestVar($name, $defaultValue, null, $parametersRequest);
				}
			} catch(Exception $e) {
				throw new Exception("The required variable '$name' is not correct or has not been found in the API Request. Add the parameter '&$name=' (with a value) in the URL.");
			}			
			$finalParameters[] = $requestValue;
		}
		return $finalParameters;
	}
	
	/**
	 * Includes the class Piwik_UserSettings_API by looking up plugins/UserSettings/API.php
	 *
	 * @param string api class name eg. "Piwik_UserSettings_API"
	 */
	private function includeApiFile($fileName)
	{
		$module = self::getModuleNameFromClassName($fileName);
		$path = PIWIK_INCLUDE_PATH . '/plugins/' . $module . '/API.php';

		if(Zend_Loader::isReadable($path))
		{
			require_once $path; // prefixed by PIWIK_INCLUDE_PATH
		}
		else
		{
			throw new Exception("API module $module not found.");
		}
	}

	private function loadMethodMetadata($class, $method)
	{
		if($method->isPublic() 
			&& !$method->isConstructor()
			&& $method->getName() != 'getInstance' )
		{
			$name = $method->getName();
			$parameters = $method->getParameters();
			
			$aParameters = array();
			foreach($parameters as $parameter)
			{
				$nameVariable = $parameter->getName();
				
				$defaultValue = self::NO_DEFAULT_VALUE;
				if($parameter->isDefaultValueAvailable())
				{
					$defaultValue = $parameter->getDefaultValue();
				}
				
				$aParameters[$nameVariable] = $defaultValue;
			}
			$this->metadataArray[$class][$name]['parameters'] = $aParameters;
			$this->metadataArray[$class][$name]['numberOfRequiredParameters'] = $method->getNumberOfRequiredParameters();
		}
	}

	/**
	 * Checks that the method exists in the class 
	 * 
	 * @param string The class name
	 * @param string The method name
	 * @throws exception If the method is not found
	 */	
	private function checkMethodExists($className, $methodName)
	{
		if(!$this->isMethodAvailable($className, $methodName))
		{
			throw new Exception("The method '$methodName' does not exist or is not available in the module '".$className."'.");
		}
	}
	
	/**
	 * Returns the number of required parameters (parameters without default values).
	 * 
	 * @param string The class name
	 * @param string The method name
	 * @return int The number of required parameters
	 */
	private function getNumberOfRequiredParameters($class, $name)
	{
		return $this->metadataArray[$class][$name]['numberOfRequiredParameters'];
	}
	
	/**
	 * Returns true if the method is found in the API of the given class name. 
	 * 
	 * @param string The class name
	 * @param string The method name
	 * @return bool 
	 */
	private function isMethodAvailable( $className, $methodName)
	{
		return isset($this->metadataArray[$className][$methodName]);
	}
		
	/**
	 * @throws exception If any parameter value is null
	 */
	private function checkParametersAreNotNull($className, $methodName, $parametersValues)
	{
		foreach($parametersValues as $value)
		{
			if(is_null($value))
			{
				$nbParamsRequired = $this->getNumberOfRequiredParameters($className, $methodName);
				throw new Exception("The parameters are not valid. The method called requires $nbParamsRequired parameters. Please check your URL and the method API.");
			}
		}
	}
	
	/**
	 * Checks that the class is a Singleton (presence of the getInstance() method)
	 * 
	 * @param string The class name
	 * @throws exception If the class is not a Singleton
	 */
	private function checkClassIsSingleton($className)
	{
		if(!method_exists($className, "getInstance"))
		{
			throw new Exception("Objects that provide an API must be Singleton and have a 'static public function getInstance()' method.");
		}
	}
}
