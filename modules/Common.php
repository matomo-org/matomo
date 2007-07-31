<?php
/**
 * Static class providing functions used by both the CORE of Piwik and the
 * visitor logging engine. 
 * This is the only external class loaded by the Piwik.php file.
 */
class Piwik_Common 
{
	/**
	 * Returns the variable after cleaning operations.
	 * 
	 * If an array is passed the cleaning is done recursively on all the sub-arrays. \
	 * The keys of the array are filtered as well!
	 * 
	 * How this method works:
	 * - The variable returned has been htmlspecialchars to avoid the XSS security problem.
	 * - The single quotes are not protected so "Piwik's amazing" will still be "Piwik's amazing".
	 * - Transformations are:
	 * 		- '&' (ampersand) becomes '&amp;'
	 *  	- '"'(double quote) becomes '&quot;' 
	 * 		- '<' (less than) becomes '&lt;'
	 * 		- '>' (greater than) becomes '&gt;'
	 * - It handles the magic_quotes setting.
	 * - A non string value is returned without modification
	 * 
	 * @param mixed The variable to be cleaned
	 * @return mixed The variable after cleaning
	 */
	static public function sanitizeInputValues($value) 
	{
		if (is_array($value)) 
		{
			foreach (array_keys($value) as $key) 
			{
				$newKey = $key;
				$newKey = Piwik_Common::sanitizeInputValues($newKey);
				if ($key != $newKey) 
				{
				    $value[$newKey] = $value[$key];
				    unset($value[$key]);
				}
				
				$value[$newKey] = Piwik_Common::sanitizeInputValues($value[$newKey]);
			}
		}
		else 
		{
			if(is_string($value))
			{
				$value = htmlspecialchars($value, ENT_COMPAT, 'UTF-8');
	
				/* Undo the damage caused by magic_quotes */
				if (get_magic_quotes_gpc()) 
				{
				    $value = stripslashes($value);
				}
			}
		}
		return $value;
    }

	/**
	 * Returns a variable from the $_REQUEST superglobal.
	 * If the variable doesn't have a value or an empty value, returns the defaultValue if specified.
	 * If the variable doesn't have neither a value nor a default value provided, an exception is raised.
	 * 
	 * @param string $varName name of the variable
	 * @param string $varDefault default value. If '', and if the type doesn't match, exit() !
	 * @param string $varType Expected type, the value must be one of the following: array, numeric, int, integer, string
	 * 
	 * @exception if the variable type is not known
	 * @exception if the variable we want to read doesn't have neither a value nor a default value specified
	 * 
	 * @return mixed The variable after cleaning
	 */
	static public function getRequestVar($varName, $varDefault = null, $varType = null)
	{
		$varDefault = self::sanitizeInputValues( $varDefault );
		
		if($varType == 'int')
		{
			//settype accepts only integer
			$varType = 'integer';
		}
		
		// there is no value $varName in the REQUEST so we try to use the default value	
		if(empty($varName)
			|| !isset($_REQUEST[$varName]) 
			|| empty($_REQUEST[$varName]))
		{
			if( is_null($varDefault))
			{
				throw new Exception("\$varName '$varName' doesn't have value in \$_REQUEST and doesn't have a" .
						" \$varDefault value");
			}
			else
			{
				if( !is_null($varType) 
					&& in_array($varType, array('string', 'integer', 'array'))
				)
				{
					settype($varDefault, $varType);
				}
				return $varDefault;
			}
		}
		
		// Normal case, there is a value available in REQUEST for the requested varName
		$value = self::sanitizeInputValues( $_REQUEST[$varName] );
		
		if( !is_null($varType))
		{			
			$ok = false;
			
			if($varType == 'string')
			{
				if(is_string($value)) $ok = true;
			}			
			elseif($varType == 'numeric')
			{
					if(is_numeric($value)) $ok = true;
			}
			elseif($varType == 'integer')
			{
					if(is_int($value)) $ok = true;
			}
			elseif($varType == 'array')
			{
					if(is_array($value)) $ok = true;
			}
			else
			{
				throw new Exception("\$varType specified is not known. It should be one of the following: array, numeric, int, integer, float, string");
			}
			
			// The type is not correct
			if($ok === false)
			{
				if($varDefault === null) 
				{	
					throw new Exception("\$varName '$varName' doesn't have a correct type in \$_REQUEST and doesn't " .
							"have a \$varDefault value");
				}
				// we return the default value with the good type set
				else
				{
					settype($varDefault, $varType);
					return $varDefault;
				}
			}
		}
				
		return $value;
	}
}
?>
