<?php

/**
 * Simple class to handle the cookies.
 * Its features are:
 * 
 * - read a cookie values
 * - edit an existing cookie and save it
 * - create a new cookie, set values, expiration date, etc. and save it
 * 
 * The cookie content is saved in an optimized way.
 */
class Piwik_LogStats_Cookie
{
	/**
	 * The name of the cookie 
	 */
	protected $name = null;
	
	/**
	 * The expire time for the cookie (expressed in UNIX Timestamp)
	 */
	protected $expire = null;
	
	/**
	 * The content of the cookie
	 */
	protected $value = array();
	
	const VALUE_SEPARATOR = ':';
	
	public function __construct( $cookieName, $expire = null)
	{
		$this->name = $cookieName;
		
		if(is_null($expire)
			|| !is_numeric($expire)
			|| $expire <= 0)
		{
			$this->expire = $this->getDefaultExpire();
		}
		
		if($this->isCookieFound())
		{
			$this->loadContentFromCookie();
		}
	}
	
	public function isCookieFound()
	{
		return isset($_COOKIE[$this->name]);
	}
	
	protected function getDefaultExpire()
	{
		return time() + 86400*365*10;
	}	
	
	/**
	 * taken from http://usphp.com/manual/en/function.setcookie.php
	 * fix expires bug for IE users (should i say expected to fix the bug in 2.3 b2)
	 * TODO setCookie: use the other parameters of the function
	 */
	protected function setCookie($Name, $Value, $Expires, $Path = '', $Domain = '', $Secure = false, $HTTPOnly = false)
	{
		if (!empty($Domain))
		{	
			// Fix the domain to accept domains with and without 'www.'.
			if (strtolower(substr($Domain, 0, 4)) == 'www.')  $Domain = substr($Domain, 4);
			
			$Domain = '.' . $Domain;
			
			// Remove port information.
			$Port = strpos($Domain, ':');
			if ($Port !== false)  $Domain = substr($Domain, 0, $Port);
		}
		
		$header = 'Set-Cookie: ' . rawurlencode($Name) . '=' . rawurlencode($Value)
					 . (empty($Expires) ? '' : '; expires=' . gmdate('D, d-M-Y H:i:s', $Expires) . ' GMT')
					 . (empty($Path) ? '' : '; path=' . $Path)
					 . (empty($Domain) ? '' : '; domain=' . $Domain)
					 . (!$Secure ? '' : '; secure')
					 . (!$HTTPOnly ? '' : '; HttpOnly');
		 
		 header($header, false);
	}
	
	protected function setP3PHeader()
	{
		header("P3P: CP='OTI DSP COR NID STP UNI OTPa OUR'");
	}
	
	public function deleteCookie()
	{
		$this->setP3PHeader();
		setcookie($this->name, false, time() - 86400);
	}
	
	public function save()
	{
		$this->setP3PHeader();
		$this->setCookie( $this->name, $this->generateContentString(), $this->expire);
	}
	
	/**
	 * Load the cookie content into a php array 
	 */
	protected function loadContentFromCookie()
	{
		$cookieStr = $_COOKIE[$this->name];
		
		$values = explode( self::VALUE_SEPARATOR, $cookieStr);
		foreach($values as $nameValue)
		{
			$equalPos = strpos($nameValue, '=');
			$varName = substr($nameValue,0,$equalPos);
			$varValue = substr($nameValue,$equalPos+1);
			
			// no numeric value are base64 encoded so we need to decode them
			if(!is_numeric($varValue))
			{
				$varValue = base64_decode($varValue);
				
				// some of the values may be serialized array so we try to unserialize it
				if( ($arrayValue = @unserialize($varValue)) !== false
					// we set the unserialized version only for arrays as you can have set a serialized string on purpose
					&& is_array($arrayValue) 
					)
				{
					$varValue = $arrayValue;
				}
			}
			
			$this->set($varName, $varValue);
		}
	}
	
	/**
	 * Returns the string to save in the cookie frpm the $this->value array of values
	 * 
	 */
	public function generateContentString()
	{
		$cookieStr = '';
		foreach($this->value as $name=>$value)
		{
			if(is_array($value))
			{
				$value = base64_encode(serialize($value));
			}
			elseif(is_string($value))
			{
				$value = base64_encode($value);
			}
			
			$cookieStr .= "$name=$value" . self::VALUE_SEPARATOR;
		}
		$cookieStr = substr($cookieStr, 0, strlen($cookieStr)-1);
		return $cookieStr;
	}
	
	/**
	 * Registers a new name => value association in the cookie.
	 * 
	 * Registering new values is optimal if the value is a numeric value.
	 * If the value is a string, it will be saved as a base64 encoded string.
	 * If the value is an array, it will be saved as a serialized and base64 encoded 
	 * string which is not very good in terms of bytes usage. 
	 * You should save arrays only when you are sure about their maximum data size.
	 * 
	 * @param string Name of the value to save; the name will be used to retrieve this value
	 * @param string|array|numeric Value to save
	 * 
 	 */
	public function set( $name, $value )
	{
		$name = self::escapeValue($name);
		$this->value[$name] = $value;
	}
	
	/**
	 * Returns the value defined by $name from the cookie.
	 * 
	 * @param string|integer Index name of the value to return
	 * @return mixed The value if found, false if the value is not found
	 */
	public function get( $name )
	{
		$name = self::escapeValue($name);
		return isset($this->value[$name]) ? self::escapeValue($this->value[$name]) : false;
	}
	
	public function __toString()
	{
		$str = "<-- Content of the cookie '{$this->name}' <br>\n";
		foreach($this->value as $name => $value )
		{
			$str .= $name . " = " . var_export($this->get($name), true) . "<br>\n";
		}
		$str .= "--> <br>\n";
		return $str;
	}
	
	static protected function escapeValue( $value )
	{
		return Piwik_Common::sanitizeInputValues($value);
	}	
}

//
//$c = new Piwik_LogStats_Cookie( 'piwik_logstats', 86400);
//echo $c;
//$c->set(1,1);
//$c->set('test',1);
//$c->set('test2','test=432:gea785');
//$c->set('test3',array('test=432:gea785'));
//$c->set('test4',array(array(0=>1),1=>'test'));
//echo $c;
//echo "<br>";
//echo $c->generateContentString();
//echo "<br>";
//$v=$c->get('more!');
//if(empty($v)) $c->set('more!',1);
//$c->set('more!', array($c->get('more!')));
//$c->save();
//$c->deleteCookie();

?>
