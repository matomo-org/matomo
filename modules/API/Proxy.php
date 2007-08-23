<?php
class Piwik_API_Proxy
{
	static $classCalled = null;
	protected $alreadyRegistered = array();
	private $api = null;
		
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
		

		Piwik::log("List of the public methods for the class $class");

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
					
					$defaultValue = '';
					if($parameter->isDefaultValueAvailable())
					{
						$defaultValue = $parameter->getDefaultValue();
					}
					
					$aParameters[$nameVariable] = $defaultValue;
				}
				$this->api[$class][$name]['parameters'] = $aParameters;
				$this->api[$class][$name]['numberOfRequiredParameters'] = $method->getNumberOfRequiredParameters();
				
				Piwik::log("- $name is public ".$this->getStrListParameters($class, $name));				
			}
		}
		
		$this->alreadyRegistered[$fileName] = true;
	}
	
	private function getStrListParameters($class, $name)
	{
		$aParameters = $this->getParametersList($class, $name);
		$asParameters = array();
		foreach($aParameters as $nameVariable=> $defaultValue)
		{
			$str = $nameVariable;
			if(!empty($defaultValue))
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
	
	private function getNumberOfRequiredParameters($class, $name)
	{
		return $this->api[$class][$name]['numberOfRequiredParameters'];
	}
	
	private function isMethodAvailable( $className, $methodName)
	{
		return isset($this->api[$className][$methodName]);
	}
	
	public function __get($name)
	{
		self::$classCalled = $name;
		return $this;
	}	
	
	private function checkNumberOfParametersMatch($className, $methodName, $parameters)
	{
		$nbParamsGiven = count($parameters);
		$nbParamsRequired = $this->getNumberOfRequiredParameters($className, $methodName);
		
		if($nbParamsGiven < $nbParamsRequired)
		{
			throw new Exception("The number of parameters provided ($nbParamsGiven) is less than the number of required parameters ($nbParamsRequired) for this method.
							Please check the method API.");
		}
		elseif($nbParamsGiven > $nbParamsRequired)
		{
			throw new Exception("The number of parameters provided ($nbParamsGiven) is greater than the number of required parameters ($nbParamsRequired) for this method.
							Please check the method API.");
		}
	}
	
	private function checkClassIsSingleton($className)
	{
		if(!method_exists($className, "getInstance"))
		{
			throw new Exception("Objects that provide an API must be Singleton and have a 'static public function getInstance()' method.");
		}
	}
	
	public function checkMethodExists($className, $methodName)
	{
		if(!$this->isMethodAvailable($className, $methodName))
		{
			throw new Exception("The method '$methodName' does not exist or is not available in the module '".$className."'.");
		}
	}
		
	public function getClassNameFromModule($module)
	{
		$class = Piwik::prefixClass($module ."_API");
		return $class;
	}
	public function __call($methodName, $parameterValues )
	{
		$returnedValue = null;
		
		try {
			assert(!is_null(self::$classCalled));

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
		catch( Exception $e)
		{
			Piwik::log("<br>\n Error during API call {$className}.{$methodName}... 
					<br>\n => ". $e->getMessage());

		}

		self::$classCalled = null;
		
		return $returnedValue;
	}
}
?>