<?php
class Piwik_PublicAPI
{
	static $classCalled = null;
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
	
	public function registerClass( $class )
	{
		Zend_Loader::loadClass($class);

		// check that is is singleton
		$this->checkClassIsSingleton($class);
		
		$rClass = new ReflectionClass($class);
		
		// check that it is a subclass of Piwik_APIable
		if(!$rClass->isSubclassOf(new ReflectionClass("Piwik_Apiable")))
		{
			throw new Exception("To publish its public methods in the API, the class '$class' must be a subclass of 'Piwik_Apiable'.");
		}

		Piwik::log("List of the public methods for the class $class");

		$rMethods = $rClass->getMethods();
		foreach($rMethods as $method)
		{
			// use this trick to read the static attribute of the class
			// $class::$methodsNotToPublish doesn't work
			$variablesClass = get_class_vars($class);
						
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
	
	private function getParametersList($class, $name)
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
	
	private function checkMethodExists($className, $methodName)
	{
		if(!$this->isMethodAvailable($className, $methodName))
		{
			throw new Exception("The method '$methodName' does not exist or is not available in the module '".self::$classCalled."'.");
		}
	}
		
	public function __call($methodName, $parameters )
	{
		try {
			assert(!is_null(self::$classCalled));
				
			$className = Piwik::prefixClass(self::$classCalled);
						
			// instanciate the object
			$object = call_user_func(array($className, "getInstance"));
			
			// check method exists
			$this->checkMethodExists($className, $methodName);
						
			// first check number of parameters do match
			$this->checkNumberOfParametersMatch($className, $methodName, $parameters);
			
			$args = @implode(", ", $parameters);
			Piwik::log("Calling ".self::$classCalled.".$methodName [$args]");
		
			// call the method
			$returnedValue = call_user_func_array(array($object, $methodName), $parameters);
			
			Piwik_Log::dump($returnedValue);
		}
		catch( Exception $e)
		{
			Piwik::log("Error during API call... <br> => ". $e->getMessage());

		}

		self::$classCalled = null;
	}
}
?>