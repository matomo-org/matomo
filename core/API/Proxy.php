<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id: Proxy.php 581 2008-07-27 23:07:52Z matt $
 * 
 * @package Piwik_API
 */


/**
 * The API Proxy receives all the API calls requests and forwards them to the given module.
 *  
 * The class checks that a call to the API has the correct number of parameters.
 * The proxy is a singleton that has the knowledge of every method available, their parameters and default values.
 * 
 * It can also log the performances of the API calls (time spent, parameter values, etc.)
 * 
 * @package Piwik_API
 */
class Piwik_API_Proxy
{
	static $classCalled = null;
	
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
	 * @param string ModuleName eg. "UserSettings"
	 */
	public function registerClass( $moduleName )
	{
		if(isset($this->alreadyRegistered[$moduleName]))
		{
			return;
		}

		$this->includeApiFile($moduleName);
		$class = $this->getClassNameFromModule($moduleName);
		$this->checkClassIsSingleton($class);
			
		$rClass = new ReflectionClass($class);
		foreach($rClass->getMethods() as $method)
		{
			$this->loadMethodMetadata($class, $method);
		}
		
		$this->alreadyRegistered[$moduleName] = true;
	}
	

	/**
	 * Checks that the method exists in the class 
	 * 
	 * @param string The class name
	 * @param string The method name
	 * @throws exception If the method is not found
	 */	
	public function checkMethodExists($className, $methodName)
	{
		if(!$this->isMethodAvailable($className, $methodName))
		{
			throw new Exception("The method '$methodName' does not exist or is not available in the module '".$className."'.");
		}
	}

	/**
	 * Magic method used to set a flag telling the module named currently being called
	 *
	 */
	public function __get($name)
	{
		self::$classCalled = $name;
		return $this;
	}	

	/**
	 * Method always called when making an API request.
	 * It checks several things before actually calling the real method on the given module.
	 * 
	 * It also logs the API calls, with the parameters values, the returned value, the performance, etc.
	 * You can enable logging in config/global.ini.php (log_api_call)
	 * 
	 * @param string The method name
	 * @param array The parameters
	 * 
	 * @throws Piwik_Access_NoAccessException 
	 */
	public function __call($methodName, $parameterValues )
	{
		$returnedValue = null;
		
		try {
			$this->registerClass(self::$classCalled);
						
			$className = $this->getClassNameFromModule(self::$classCalled);

			// instanciate the object
			$object = call_user_func(array($className, "getInstance"));

			// check method exists
			$this->checkMethodExists($className, $methodName);
			
			// all parameters to the function call must be non null
			$this->checkParametersAreNotNull($className, $methodName, $parameterValues);
			
			// start the timer
			$timer = new Piwik_Timer;
			
			// call the method
			$returnedValue = call_user_func_array(array($object, $methodName), $parameterValues);
			
			// log the API Call
			$parameterNamesDefaultValues  = $this->getParametersList($className, $methodName);
			Zend_Registry::get('logger_api_call')->log(
								self::$classCalled,
								$methodName,
								$parameterNamesDefaultValues,
								$parameterValues,
								$timer->getTimeMs(),
								$returnedValue
							);
		}
		catch( Piwik_Access_NoAccessException $e) {
			throw $e;
		}

		self::$classCalled = null;
		
		return $returnedValue;
	}
	
	/**
	 * Returns the  'moduleName' part of 'Piwik_moduleName_API' classname 
	 * 
	 * @param string moduleName
	 * @return string className 
	 */ 
	public function getModuleNameFromClassName( $className )
	{
		$start = strpos($className, '_') + 1;
		return substr($className, $start , strrpos($className, '_') - $start);
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
	
	private function includeApiFile($fileName)
	{
		$potentialPaths = array( "plugins/". $fileName ."/API.php", );
		
		$found = false;
		foreach($potentialPaths as $path)
		{
			if(Zend_Loader::isReadable($path))
			{
				require_once $path;
				$found = true;
				break;
			}
		}
		
		if(!$found)
		{
			throw new Exception("API module $fileName not found.");
		}
	}
	
	private function loadMethodMetadata($class, $method)
	{
		// use this trick to read the static attribute of the class
		// $class::$methodsNotToPublish doesn't work
		$variablesClass = get_class_vars($class);
		$variablesClass['methodsNotToPublish'][] = 'getInstance';

		if($method->isPublic() 
			&& !$method->isConstructor()
			&& !in_array($method->getName(), $variablesClass['methodsNotToPublish'] )
		)
		{
			$name = $method->getName();
			$parameters = $method->getParameters();
			
			$aParameters = array();
			foreach($parameters as $parameter)
			{
				$nameVariable = $parameter->getName();
				
				$defaultValue = Piwik_API_Proxy::NO_DEFAULT_VALUE;
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
	 * Checks that all given parameters are not null
	 * 
	 * @param array of values
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
	
	/**
	 * Returns the API class name given the module name.
	 * 
	 * For exemple for $module = 'Referers' it returns 'Piwik_Referers_API' 
	 * Piwik_Referers_API contains the methods to be published in the API.
	 * 
	 * @param string module name
	 * @return string class name
	 */
	private function getClassNameFromModule($module)
	{
		$class = Piwik::prefixClass($module ."_API");
		return $class;
	}
}
