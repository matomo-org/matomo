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
 * The API Proxy receives all the API calls requests and forwards them to the given module.
 *  
 * It registers all the APIable modules (@see Piwik_Apiable)
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
	
	// array of already registered modules names
	protected $alreadyRegistered = array();
	
	private $api = array();
	
	// when a parameter doesn't have a default value we use this constant
	const NO_DEFAULT_VALUE = null;

	static private $instance = null;
	protected function __construct()
	{}
	
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
	 * Registers the API information of a given module.
	 * 
	 * The module to be registered must be
	 * - extending the Piwik_Apiable class
	 * - a singleton (providing a getInstance() method)
	 * - the API file must be located in plugins/ModuleName/API.php
	 *   for example plugins/Referers/API.php
	 * 
	 * The method will introspect the methods, their parameters, etc. 
	 * 
	 * @param string ModuleName eg. "UserSettings"
	 */
	public function registerClass( $fileName )
	{
		if(isset($this->alreadyRegistered[$fileName]))
		{
			return;
		}
		
		$potentialPaths = array(
			 PIWIK_INCLUDE_PATH . "/plugins/". $fileName ."/API.php",
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
	
	/**
	 * Returns the  'moduleName' part of 'Piwik_moduleName_API' classname 
	 * 
	 * @param string moduleName
	 * @return string className 
	 */ 
	protected function getModuleNameFromClassName( $className )
	{
		$start = strpos($className, '_') + 1;
		return substr($className, $start , strrpos($className, '_') - $start);
	}
	
	/**
	 * Returns a string containing links to examples on how to call a given method on a given API
	 * It will export links to XML, CSV, HTML, JSON, PHP, etc.
	 * It will not export links for methods such as deleteSite or deleteUser 
	 *
	 * @param string the class 
	 * @param methodName the method
	 * @return string|false when not possible
	 */
	public function getExampleUrl($class, $methodName, $parametersToSet = array())
	{
		$knowExampleDefaultParametersValues = array(
			'access' => 'view',
			'idSite' => '1',
			'userLogin' => 'test',
			'password' => 'passwordExample',
			'passwordMd5ied' => 'passwordExample',
			'email' => 'test@example.org',
		
			'siteName' => 'new example website',
			'urls' => 'http://example.org', // used in addSite, updateSite

			'period' => 'day',
			'date' => 'today',
		);
		
		foreach($parametersToSet as $name => $value)
		{
			$knowExampleDefaultParametersValues[$name] = $value;
		}
		
		// no links for these method names
		$doNotPrintExampleForTheseMethods = array(
			'deleteSite',
			'deleteUser',
		);
		
		if(in_array($methodName,$doNotPrintExampleForTheseMethods))
		{
			return false;
		}
		
		
		// we try to give an URL example to call the API
		$aParameters = $this->getParametersList($class, $methodName);
		$moduleName = $this->getModuleNameFromClassName($class);
		$urlExample = '?module=API&method='.$moduleName.'.'.$methodName.'&';
		foreach($aParameters as $nameVariable=> $defaultValue)
		{
			// if there isn't a default value for a given parameter, 
			// we need a 'know default value' or we can't generate the link
			if($defaultValue === Piwik_API_Proxy::NO_DEFAULT_VALUE)
			{
				if(isset($knowExampleDefaultParametersValues[$nameVariable]))
				{
					$exampleValue = $knowExampleDefaultParametersValues[$nameVariable];
					$urlExample .= $nameVariable . '=' . $exampleValue . '&';
				}
				else
				{
					return false;
				}
			}
			
		}
		
		return substr($urlExample,0,-1);
	}
	
	/**
	 * Returns a HTML page containing help for all the successfully loaded APIs.
	 * 
	 * For each module it will return a mini help with the method names, parameters to give, 
	 * links to get the result in Xml/Csv/etc
	 *
	 * @return string
	 */
	public function getAllInterfaceString( $outputExampleUrls = true, $prefixUrls = '' )
	{
		$str = '';
		foreach($this->api as $class => $info)
		{
			$moduleName = $this->getModuleNameFromClassName($class);
			$str .= "\n<h3>Module ".$moduleName."</h3>";
			
			foreach($info as $methodName => $infoMethod)
			{

				
				$params = $this->getStrListParameters($class, $methodName);
				$str .= "\n" . "- <b>$moduleName.$methodName " . $params . "</b>";
				
				$str .= '<small>';
				
				if($outputExampleUrls)
				{
					// we prefix all URLs with $prefixUrls
					// used when we include this output in the Piwik official documentation for example
					$exampleUrl = $this->getExampleUrl($class, $methodName);
					if($exampleUrl !== false)
					{
						$lastNUrls = '';
						if( ereg('(&period)|(&date)',$exampleUrl))
						{
							$exampleUrlRss1 = $prefixUrls . $this->getExampleUrl($class, $methodName, array('date' => 'last10')) ;
							$exampleUrlRss2 = $prefixUrls . $this->getExampleUrl($class, $methodName, array('date' => 'last5','period' => 'week',));
							$lastNUrls = ",	RSS of the last <a target=_blank href='$exampleUrlRss1&format=rss'>10 days</a>, <a target=_blank href='$exampleUrlRss2&format=Rss'>5 weeks</a>,
									XML of the <a target=_blank href='$exampleUrlRss1&format=xml'>last 10 days</a>";
						}
						$exampleUrl = $prefixUrls . $exampleUrl ;
						$str .= " [ Example in  
									<a target=_blank href='$exampleUrl&format=xml'>XML</a>, 
									<a target=_blank href='$exampleUrl&format=PHP&prettyDisplay=true'>PHP</a>, 
									<a target=_blank href='$exampleUrl&format=JSON'>Json</a>, 
									<a target=_blank href='$exampleUrl&format=Csv'>Csv</a>, 
									<a target=_blank href='$exampleUrl&format=Html'>Basic html</a> 
									$lastNUrls
									]";
					}
					else
					{
						$str .= " [ No example available ]";
					}
				}
				$str .= '</small>';
				$str .= "\n<br>";
			}
		}
		return $str;
	}
	
	/**
	 * Returns the methods $class.$name parameters (and default value if provided) as a string.
	 * 
	 * @param string The class name
	 * @param string The method name
	 * @return string For example "(idSite, period, date = 'today')"
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
				$str .= " = '$defaultValue'";
			}
			$asParameters[] = $str;
		}
		$sParameters = implode(", ", $asParameters);
		return "($sParameters)";
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
		return $this->api[$class][$name]['parameters'];
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
		return $this->api[$class][$name]['numberOfRequiredParameters'];
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
		return isset($this->api[$className][$methodName]);
	}
	
	
	/**
	 * Checks that the count of the given parameters do match with the count of the required ones
	 * 
	 * @param string The class name
	 * @param string The method name
	 * @param array 
	 * @throws exception If less parameters than required were given
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
	 * Returns the API class name given the module name.
	 * 
	 * For exemple for $module = 'Referers' it returns 'Piwik_Referers_API' 
	 * Piwik_Referers_API is the class that extends Piwik_Apiable 
	 * and that contains the methods to be published in the API.
	 * 
	 * @param string module name
	 * @return string class name
	 */
	protected  function getClassNameFromModule($module)
	{
		$class = Piwik::prefixClass($module ."_API");
		return $class;
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
