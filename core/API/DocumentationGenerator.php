<?php

class Piwik_API_DocumentationGenerator
{
	protected $countPluginsLoaded = 0;

	/**
	 * trigger loading all plugins with an API.php file in the Proxy 
	 */
	public function __construct()
	{
		$plugins = Piwik_PluginsManager::getInstance()->getLoadedPluginsName();
		foreach( $plugins as $plugin )
		{		
			$plugin = Piwik::unprefixClass($plugin);
			try {
				Piwik_API_Proxy::getInstance()->registerClass("Piwik_".$plugin."_API");
			}
			catch(Exception $e){
			}
		}
	}
	
	/**
	 * Returns a HTML page containing help for all the successfully loaded APIs.
	 *  For each module it will return a mini help with the method names, parameters to give, 
	 * links to get the result in Xml/Csv/etc
	 *
	 * @return string
	 */
	public function getAllInterfaceString( $outputExampleUrls = true, $prefixUrls = '' )
	{
		$str = '';
		$token_auth = "&token_auth=" . Piwik::getCurrentUserTokenAuth();
		$parametersToSet = array(
								'idSite' 	=> Piwik_Common::getRequestVar('idSite', 1, 'int'),
								'period' 	=> Piwik_Common::getRequestVar('period', 'day', 'string'),
								'date'		=> Piwik_Common::getRequestVar('date', 'today', 'string')
							);
		
		foreach(Piwik_API_Proxy::getInstance()->getMetadata() as $class => $info)
		{
			$moduleName = Piwik_API_Proxy::getInstance()->getModuleNameFromClassName($class);
			$str .= "\n<h2 id='$moduleName'>Module ".$moduleName."</h2>";
			
			foreach($info as $methodName => $infoMethod)
			{
				$params = $this->getStrListParameters($class, $methodName);
				$str .= "\n" . "- <b>$moduleName.$methodName " . $params . "</b>";
				$str .= '<small>';
				
				if($outputExampleUrls)
				{
					// we prefix all URLs with $prefixUrls
					// used when we include this output in the Piwik official documentation for example
					$str .= "<span class=\"example\">";
					$exampleUrl = $this->getExampleUrl($class, $methodName, $parametersToSet);
					if($exampleUrl !== false)
					{
						$lastNUrls = '';
						if( ereg('(&period)|(&date)',$exampleUrl))
						{
							$exampleUrlRss1 = $prefixUrls . $this->getExampleUrl($class, $methodName, array('date' => 'last10') + $parametersToSet) ;
							$exampleUrlRss2 = $prefixUrls . $this->getExampleUrl($class, $methodName, array('date' => 'last5','period' => 'week',) + $parametersToSet );
							$lastNUrls = ",	RSS of the last <a target=_blank href='$exampleUrlRss1&format=rss$token_auth'>10 days</a>, <a target=_blank href='$exampleUrlRss2&format=Rss'>5 weeks</a>,
									XML of the <a target=_blank href='$exampleUrlRss1&format=xml$token_auth'>last 10 days</a>";
						}
						$exampleUrl = $prefixUrls . $exampleUrl ;
						$str .= " [ Example in  
									<a target=_blank href='$exampleUrl&format=xml$token_auth'>XML</a>, 
									<a target=_blank href='$exampleUrl&format=PHP&prettyDisplay=true$token_auth'>PHP</a>, 
									<a target=_blank href='$exampleUrl&format=JSON$token_auth'>Json</a>, 
									<a target=_blank href='$exampleUrl&format=Csv$token_auth'>Csv</a>, 
									<a target=_blank href='$exampleUrl&format=Html$token_auth'>Basic html</a> 
									$lastNUrls
									]";
					}
					else
					{
						$str .= " [ No example available ]";
					}
					$str .= "</span>";
				}
				$str .= '</small>';
				$str .= "\n<br>";
			}
		}
		return $str;
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
	protected function getExampleUrl($class, $methodName, $parametersToSet = array())
	{
		$knowExampleDefaultParametersValues = array(
			'access' => 'view',
			'userLogin' => 'test',
			'password' => 'passwordExample',
			'passwordMd5ied' => 'passwordExample',
			'email' => 'test@example.org',
		
			'siteName' => 'new example website',
			'urls' => 'http://example.org', // used in addSite, updateSite
		
			'languageCode' => 'fr',
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
		$aParameters = Piwik_API_Proxy::getInstance()->getParametersList($class, $methodName);
		$moduleName = Piwik_API_Proxy::getInstance()->getModuleNameFromClassName($class);
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
	 * Returns the methods $class.$name parameters (and default value if provided) as a string.
	 * 
	 * @param string The class name
	 * @param string The method name
	 * @return string For example "(idSite, period, date = 'today')"
	 */
	protected function getStrListParameters($class, $name)
	{
		$aParameters = Piwik_API_Proxy::getInstance()->getParametersList($class, $name);
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
	
}
