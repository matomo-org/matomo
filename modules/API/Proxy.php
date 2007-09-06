<?php
/**
 * 
 * The API Proxy receives all the API calls requests and forwards them to the given module.
 *  
 * It registers all the APIable modules (@see Piwik_Apiable)
 * The class checks that a call to the API has to correct number of parameters.
 * The proxy has the knowledge of every method available, and their parameters and default value.
 * It also logs the calls performances (time spent, parameter values, etc.)
 * 
 * @package Piwik_API
 */
class Piwik_API_Proxy
{
	static $classCalled = null;
	protected $alreadyRegistered = array();
	private $api = array();
	
	const NO_DEFAULT_VALUE = null;
		
	static private $instance = null;
	protected function __construct()
	{}
	
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
	 * Registers the API information of a given module.
	 * 
	 * The module to register must be
	 * - extending the Piwik_Apiable class
	 * - a singleton (providing a getInstance() method)
	 * - the API file must be located in plugins/MODULE/API.php
	 *   for example plugins/Referers/API.php
	 * 
	 * The method will introspect the methods, their parameters, etc. 
	 */
	public function registerClass( $fileName )
	{		
		if(isset($this->alreadyRegistered[$fileName]))
		{
			return;
		}
		
		$potentialPaths = array(
			 PIWIK_INCLUDE_PATH . "/plugins/". $fileName ."/API.php",
			 PIWIK_INCLUDE_PATH . "/modules/". $fileName .".php",
	  	);
		
		$found = false;
		foreach($potentialPaths as $path)
		{
			if(is_file($path))
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

		$class= $this->getClassNameFromModule($fileName);
			
		$rClass = new ReflectionClass($class);
		
		// check that it is a subclass of Piwik_APIable
		if(!$rClass->isSubclassOf(new ReflectionClass("Piwik_Apiable")))
		{
			throw new Exception("To publish its public methods in the API, the class '$class' must be a subclass of 'Piwik_Apiable'.");
		}
		
		// check that is is singleton
		$this->checkClassIsSingleton($class);
		

		
		$rMethods = $rClass->getMethods();
		foreach($rMethods as $method)
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
				$this->api[$class][$name]['parameters'] = $aParameters;
				$this->api[$class][$name]['numberOfRequiredParameters'] = $method->getNumberOfRequiredParameters();
			}
		}
		
		$this->alreadyRegistered[$fileName] = true;
	}
	
	public function getAllInterfaceString()
	{
		$str = '';
		foreach($this->api as $class => $info)
		{
			$str .= "\n<br>" . "List of the public methods for the class ".$class;
			
			foreach($info as $methodName => $infoMethod)
			{
				$params = $this->getStrListParameters($class, $methodName);
				$str .= "\n<br>" . "- $methodName : " . $params;
			}
			$str.="\n<br>";
		}
		return $str;
	}
	
	/**
	 * Returns the methods $class.$name parameters (and default value if provided)
	 * as a string.
	 * 
	 * Example: [ idSite, period, date = today ]
	 */
	private function getStrListParameters($class, $name)
	{
		$aParameters = $this->getParametersList($class, $name);
		$asParameters = array();
		foreach($aParameters as $nameVariable=> $defaultValue)
		{
			$str = $nameVariable;
			if($defaultValue !== Piwik_API_Proxy::NO_DEFAULT_VALUE)
			{
				$str .= " = $defaultValue";
			}
			$asParameters[] = $str;
		}
		$sParameters = implode(", ", $asParameters);
		return "[$sParameters]";
	}
	
	/**
	 * Returns the parameters names and default values for the method $name 
	 * of the class $class
	 * 
	 * @return array Format array(
	 * 					'parameter1Name'	=> '',
	 * 					'life'				=> 42, // default value = 42
	 * 					'date'				=> 'yesterday',
	 * 				);
	 */
	public function getParametersList($class, $name)
	{
		return $this->api[$class][$name]['parameters'];
	}
	
	/**
	 * Returns the number of required parameters (parameters without default values).
	 */
	private function getNumberOfRequiredParameters($class, $name)
	{
		return $this->api[$class][$name]['numberOfRequiredParameters'];
	}
	
	/**
	 * Returns true if the method is found in the API
	 */
	private function isMethodAvailable( $className, $methodName)
	{
		return isset($this->api[$className][$methodName]);
	}
	
	public function __get($name)
	{
		self::$classCalled = $name;
		return $this;
	}	
	
	/**
	 * 
	 */
	private function checkNumberOfParametersMatch($className, $methodName, $parameters)
	{
		$nbParamsGiven = count($parameters);
		$nbParamsRequired = $this->getNumberOfRequiredParameters($className, $methodName);
		
		if($nbParamsGiven < $nbParamsRequired)
		{
			throw new Exception("The number of parameters provided ($nbParamsGiven) is less than the number of required parameters ($nbParamsRequired) for this method.
							Please check the method API.");
		}
	}
	
	/**
	 * Checks that the class is a Singleton (presence of the getInstance() method)
	 * 
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
	 * Checks that the method exists in the class 
	 * 
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
	 * Returns the API class name given the module name.
	 * 
	 * For exemple for $module = 'Referers' it returns 'Piwik_Referers_API' 
	 * Piwik_Referers_API is the class that extends Piwik_Apiable 
	 * and that contains the methods to be published in the API.
	 */
	protected  function getClassNameFromModule($module)
	{
		$class = Piwik::prefixClass($module ."_API");
		return $class;
	}
	
	/**
	 * Method always called when making an API request.
	 * It checks several things before actually calling the real method on the given module.
	 * It also logs the API calls, with the parameters values, the returned value, the performance, etc.
	 */
	public function __call($methodName, $parameterValues )
	{
		$returnedValue = null;
		
		try {
			assert(!is_null(self::$classCalled));

			$this->registerClass(self::$classCalled);
			
			$className = $this->getClassNameFromModule(self::$classCalled);

			// instanciate the object
			$object = call_user_func(array($className, "getInstance"));

			// check method exists
			$this->checkMethodExists($className, $methodName);
			
			// first check number of parameters do match
			$this->checkNumberOfParametersMatch($className, $methodName, $parameterValues);
			
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
}
