<?php
/**
 * This is the abstract for user-defined Accessor methods. Accessors are used to 
 * retrieve values from a cage object. By extending this abstract, developers
 * can add their own accessor methods. Typically the only method they will need
 * to define is AccessorAbstract::inspekt(), which takes a value, examines it,
 * and returns a result. Array walking is automatically handled
 *
 * @package Inspekt
 * @author Ed Finkler
 */
abstract class AccessorAbstract {
	
	/**
	 * the cage object this is attached to, provided in the constructor
	 *
	 * @var string
	 */
	protected $cage;
	
	/**
	 * constructor
	 *
	 * @param Inspekt_Cage $cage 
	 * @param array $args optional
	 * @author Ed Finkler
	 */
	public function __construct(Inspekt_Cage $cage, $args=NULL) {
		$this->cage = $cage;
		$this->args = $args;
	}
	
	/**
	 * This executes the accessor on the key, either passed as the only argument,
	 * or the first value in $this->args;
	 *
	 * @param string $key 
	 * @return mixed
	 * @author Ed Finkler
	 */
	public function run($key = null) {
		if (!isset($key)) {
			$key = $this->args[0];
		}
		
		if (!$this->cage->keyExists($key)) {
			return false;
		}
		$val = $this->getValue($key);
		if (Inspekt::isArrayOrArrayObject($val)) {
			return $this->walkArray($val);
		} else {
			return $this->inspekt($val);
		}
	}
	
	/**
	 * Retrieves a value from the cage
	 *
	 * @param string $key 
	 * @return mixed
	 * @author Ed Finkler
	 */
	protected function getValue($key) {
		return $this->cage->_getValue($key);
	}
	
	/**
	 * If an array is the value of the given key, this method walks the array
	 * recursively, applying $this->inspekt on any non-array values
	 *
	 * @param mixed $input
	 * @param 
	 * @author Ed Finkler
	 */
	protected function walkArray($input) {
		if (!isset($classname)) {
			$classname = __CLASS__;
		}
				
		if (!Inspekt::isArrayOrArrayObject($input)) {
			Inspekt_Error::raiseError('$input must be an array or ArrayObject', E_USER_ERROR);
			return FALSE;
		}

		foreach($input as $key=>$val) {
			if (Inspekt::isArrayOrArrayObject($val)) {
				$input[$key]=$this->walkArray($val);
			} else {
				$val = $this->inspekt($val);
				$input[$key]=$val;
			}
		}
		return $input;
	}
	
	abstract protected function inspekt($val);
	
}




?>