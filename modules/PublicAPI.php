<?php
class Piwik_PublicAPI
{
	static $classCalled = null;
	private $api = null;
	
	private $methodsNotToPublish = array('getMinimumRoleRequired');
	
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
	
	private function getIdSitesParameter($class, $name, $parameters)
	{
		$paramsDefaultValues = $this->getParametersList($class, $name);
		$parametersNames = array_keys($paramsDefaultValues);
		$parametersNames = array_map("strtolower", $parametersNames);
		
		$sitesIdToLookFor = array("idsites", "idsite", "sitesid", "siteid", "siteids");
		
		$newlyFound = false;
		$found = false;
		foreach($sitesIdToLookFor as $strIdSite)
		{
			$newlyFound = array_search($strIdSite, $parametersNames);
			if($newlyFound !== false 
				&& $found !== false)
			{
				throw new Exception("
					It seems that the parameters list ".$this->getStrListParameters($class, $name)." contains two potential IdSite parameters. 
					Please rename the method parameters so that only one IdSite can be found in the method parameters list.
					The following string are considered as being idSite parameter names : [".implode(", ", $sitesIdToLookFor)."]" );
			}
			elseif($newlyFound !== false)
			{
				$found = $newlyFound;
			}
		}

		if($found===false)
		{
			return false;
		}
		else
		{
			if(isset($parameters[$found]))
			{
				$parameters[$found];
			}
			else
			{
				$values = array_values($paramsDefaultValues);
				if(isset($values[$found]))
				{
					return $values[$found];
				}
				else
				{
					exit("Must test this case and the other ones...");
				}
			}
		}
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
	
	
	
	public function __call($methodName, $parameters )
	{
		assert(!is_null(self::$classCalled));
		
		$args = @implode(", ", $parameters);
		
		$className = Piwik::prefixClass(self::$classCalled);
		if(!method_exists("$className", "getInstance"))
		{
			throw new Exception("Objects that provide an API must be Singleton and have a 'static public function getInstance()' method.");
		}
		$object = null;
		eval("\$object = $className::getInstance();");
		
		// check method exists
		if(!$this->isMethodAvailable($className, $methodName))
		{
			throw new Exception("The method '$methodName' does not exist or is not available in the module '".self::$classCalled."'.");
		}
		Piwik::log("Calling ".self::$classCalled.".$methodName [$args]");
		
		try {
			// first check number of parameters do match
			$this->checkNumberOfParametersMatch($className, $methodName, $parameters);

			$idSites = $this->getIdSitesParameter($className, $methodName, $parameters);

			$access = Zend_Registry::get('access');
			$access->isAllowed( $object->getMinimumRoleRequired($methodName), $idSites);
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