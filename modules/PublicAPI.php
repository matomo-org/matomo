<?php
class Piwik_PublicAPI
{
	static $classCalled = null;
	private $api = null;
	
	private $methodsNotToPublish = array('getMinimumAccessRequired');
	
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

		$rClass = new ReflectionClass($class);
		
		if(!$rClass->isSubclassOf(new ReflectionClass("Piwik_Apiable")))
		{
			throw new Exception("To publish its public methods in the API, the class '$class' must be a subclass of 'Piwik_Apiable'.");
		}

		Piwik::log("List of the public methods for the class $class");

		$rMethods = $rClass->getMethods();
		foreach($rMethods as $method)
		{
			if($method->isPublic() 
				&& !$method->isConstructor()
				&& !in_array($method->getName(), $this->methodsNotToPublish )
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
		
		//TODO check that all the published method appear in the minimumAccess array
		// or throw exception
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
		return true;
	}
	/*
	public function setCallback($classRegex, $methodRegex, $callback)
	{
		
	}*/
	
	public function __call($methodName, $parameters )
	{
		if(ereg("MD", $methodName))
		assert(!is_null(self::$classCalled));

		$args = @implode(", ", $parameters);
		
		$className = Piwik::prefixClass(self::$classCalled);
		if(!method_exists("$className", "getInstance"))
		{
			throw new Exception("Objects that provide an API must be Singleton and have a 'static public function getInstance()' method.");
		}
		$object = call_user_func(array($className, "getInstance"));
		
		// check method exists
		if(!$this->isMethodAvailable($className, $methodName))
		{
			throw new Exception("The method '$methodName' does not exist or is not available in the module '".self::$classCalled."'.");
		}
		Piwik::log("Calling ".self::$classCalled.".$methodName [$args]");
		
		try {
			// first check number of parameters do match
			$this->checkNumberOfParametersMatch($className, $methodName, $parameters);

			//$idSites = $this->getIdSitesParameter($className, $methodName, $parameters);

			$access = Zend_Registry::get('access');
			//$access->isAllowed( $object->getMinimumAccessRequired($methodName), $idSites);
			Piwik::log('Access granted!');

			// call the method
			call_user_func(array($object, $methodName), $parameters);
		}
		catch( Exception $e)
		{
			Piwik::log("Error during API call...". $e->getMessage());
			exit;
		}

		self::$classCalled = null;
	}
}
?>