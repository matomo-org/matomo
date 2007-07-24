<?php
/**
 *	base include file for SimpleTest
 *	@package	SimpleTest
 *	@subpackage	UnitTester
 *	@version	$Id: reflection_php5.php,v 1.32 2007/07/07 00:31:03 lastcraft Exp $
 */

/**
 *    Version specific reflection API.
 *    @package SimpleTest
 *    @subpackage UnitTester
 */
class SimpleReflection {
	var $_interface;

	/**
	 *    Stashes the class/interface.
	 *    @param string $interface    Class or interface
	 *                                to inspect.
	 */
	function SimpleReflection($interface) {
		$this->_interface = $interface;
	}

	/**
	 *    Checks that a class has been declared. Versions
	 *    before PHP5.0.2 need a check that it's not really
	 *    an interface.
	 *    @return boolean            True if defined.
	 *    @access public
	 */
	function classExists() {
		if (! class_exists($this->_interface)) {
			return false;
		}
		$reflection = new ReflectionClass($this->_interface);
		return ! $reflection->isInterface();
	}

	/**
	 *    Needed to kill the autoload feature in PHP5
	 *    for classes created dynamically.
	 *    @return boolean        True if defined.
	 *    @access public
	 */
	function classExistsSansAutoload() {
		return class_exists($this->_interface, false);
	}

	/**
	 *    Checks that a class or interface has been
	 *    declared.
	 *    @return boolean            True if defined.
	 *    @access public
	 */
	function classOrInterfaceExists() {
		return $this->_classOrInterfaceExistsWithAutoload($this->_interface, true);
	}

	/**
	 *    Needed to kill the autoload feature in PHP5
	 *    for classes created dynamically.
	 *    @return boolean        True if defined.
	 *    @access public
	 */
	function classOrInterfaceExistsSansAutoload() {
		return $this->_classOrInterfaceExistsWithAutoload($this->_interface, false);
	}

	/**
	 *    Needed to select the autoload feature in PHP5
	 *    for classes created dynamically.
	 *    @param string $interface       Class or interface name.
	 *    @param boolean $autoload       True totriggerautoload.
	 *    @return boolean                True if interface defined.
	 *    @access private
	 */
	function _classOrInterfaceExistsWithAutoload($interface, $autoload) {
		if (function_exists('interface_exists')) {
			if (interface_exists($this->_interface, $autoload)) {
				return true;
			}
		}
		return class_exists($this->_interface, $autoload);
	}

	/**
	 *    Gets the list of methods on a class or
	 *    interface.
	 *    @returns array              List of method names.
	 *    @access public
	 */
	function getMethods() {
		return array_unique(get_class_methods($this->_interface));
	}

	/**
	 *    Gets the list of interfaces from a class. If the
	 *    class name is actually an interface then just that
	 *    interface is returned.
	 *    @returns array          List of interfaces.
	 *    @access public
	 */
	function getInterfaces() {
		$reflection = new ReflectionClass($this->_interface);
		if ($reflection->isInterface()) {
			return array($this->_interface);
		}
		return $this->_onlyParents($reflection->getInterfaces());
	}

	/**
	 *    Gets the list of methods for the implemented
	 *    interfaces only.
	 *    @returns array      List of enforced method signatures.
	 *    @access public
	 */
	function getInterfaceMethods() {
		$methods = array();
		foreach ($this->getInterfaces() as $interface) {
			$methods = array_merge($methods, get_class_methods($interface));
		}
		return array_unique($methods);
	}

	/**
	 *    Checks to see if the method signature has to be tightly
	 *    specified.
	 *    @param string $method        Method name.
	 *    @returns boolean             True if enforced.
	 *    @access private
	 */
	function _isInterfaceMethod($method) {
		return in_array($method, $this->getInterfaceMethods());
	}

	/**
	 *    Finds the parent class name.
	 *    @returns string      Parent class name.
	 *    @access public
	 */
	function getParent() {
		$reflection = new ReflectionClass($this->_interface);
		$parent = $reflection->getParentClass();
		if ($parent) {
			return $parent->getName();
		}
		return false;
	}

	/**
	 *    Trivially determines if the class is abstract.
	 *    @returns boolean      True if abstract.
	 *    @access public
	 */
	function isAbstract() {
		$reflection = new ReflectionClass($this->_interface);
		return $reflection->isAbstract();
	}

	/**
	 *    Trivially determines if the class is an interface.
	 *    @returns boolean      True if interface.
	 *    @access public
	 */
	function isInterface() {
		$reflection = new ReflectionClass($this->_interface);
		return $reflection->isInterface();
	}

	/**
	 *	  Scans for final methods, as they screw up inherited
	 *    mocks by not allowing you to override them.
	 *    @returns boolean   True if the class has a final method.
	 *    @access public
	 */
	function hasFinal() {
		$reflection = new ReflectionClass($this->_interface);
		foreach ($reflection->getMethods() as $method) {
			if ($method->isFinal()) {
				return true;
			}
		}
		return false;
	}

	/**
	 *    Whittles a list of interfaces down to only the
	 *    necessary top level parents.
	 *    @param array $interfaces     Reflection API interfaces
	 *                                 to reduce.
	 *    @returns array               List of parent interface names.
	 *    @access private
	 */
	function _onlyParents($interfaces) {
		$parents = array();
		$blacklist = array();
		foreach ($interfaces as $interface) {
			foreach($interfaces as $possible_parent) {
				if ($interface->getName() == $possible_parent->getName()) {
					continue;
				}
				if ($interface->isSubClassOf($possible_parent)) {
					$blacklist[$possible_parent->getName()] = true;
				}
			}
			if (!isset($blacklist[$interface->getName()])) {
				$parents[] = $interface->getName();
			}
		}
		return $parents;
	}

    /**
     * Checks whether a method is abstract or not.
     * @param   string   $name  Method name.
     * @return  bool            true if method is abstract, else false
     * @access  private
     */
    function _isAbstractMethod($name) {
        $interface = new ReflectionClass($this->_interface);
        if (! $interface->hasMethod($name)) {
            return false;
        }
        return $interface->getMethod($name)->isAbstract();
	}

    /**
     * Checks whether a method is abstract in parent or not.
     * @param   string   $name  Method name.
     * @return  bool            true if method is abstract in parent, else false
     * @access  private
     */
    function _isAbstractMethodInParent($name) {
        $interface = new ReflectionClass($this->_interface);
        if (! $parent = $interface->getParentClass()) {
            return false;
        }
        if (! $parent->hasMethod($name)) {
            return false;
        }
        return $parent->getMethod($name)->isAbstract();
	}

	/**
	 * Checks whether a method is static or not.
	 * @param	string	$name	Method name
	 * @return	bool			true if method is static, else false
	 * @access	private
	 */
	function _isStaticMethod($name) {
		$interface = new ReflectionClass($this->_interface);
		if (! $interface->hasMethod($name)) {
			return false;
		}
		return $interface->getMethod($name)->isStatic();
	}

	/**
	 *    Gets the source code matching the declaration
	 *    of a method.
	 *    @param string $name    Method name.
	 *    @return string         Method signature up to last
	 *                           bracket.
	 *    @access public
	 */
	function getSignature($name) {
		if ($name == '__set') {
			return 'function __set($key, $value)';
		}
		if ($name == '__call') {
			return 'function __call($method, $arguments)';
		}
		if (version_compare(phpversion(), '5.1.0', '>=')) {
			if (in_array($name, array('__get', '__isset', $name == '__unset'))) {
				return "function {$name}(\$key)";
			}
		}
		if (! is_callable(array($this->_interface, $name)) && ! $this->_isAbstractMethod($name)) {
			return "function $name()";
		}
		if ($this->_isInterfaceMethod($name) ||
				$this->_isAbstractMethod($name) ||
				$this->_isAbstractMethodInParent($name) ||
				$this->_isStaticMethod($name)) {
			return $this->_getFullSignature($name);
		}
		return "function $name()";
	}

	/**
	 *    For a signature specified in an interface, full
	 *    details must be replicated to be a valid implementation.
	 *    @param string $name    Method name.
	 *    @return string         Method signature up to last
	 *                           bracket.
	 *    @access private
	 */
	function _getFullSignature($name) {
		$interface = new ReflectionClass($this->_interface);
		$method = $interface->getMethod($name);
		$reference = $method->returnsReference() ? '&' : '';
		$static = $method->isStatic() ? 'static ' : '';
		return "{$static}function $reference$name(" .
				implode(', ', $this->_getParameterSignatures($method)) .
				")";
	}

	/**
	 *    Gets the source code for each parameter.
	 *    @param ReflectionMethod $method   Method object from
	 *					                    reflection API
	 *    @return array                     List of strings, each
	 *                                      a snippet of code.
	 *    @access private
	 */
	function _getParameterSignatures($method) {
		$signatures = array();
		foreach ($method->getParameters() as $parameter) {
			$signature = '';
			$type = $parameter->getClass();
			if (is_null($type) && version_compare(phpversion(), '5.1.0', '>=') && $parameter->isArray()) {
				$signature .= 'array ';
			} elseif (!is_null($type)) {
				$signature .= $type->getName() . ' ';
			}
			if ($parameter->isPassedByReference()) {
				$signature .= '&';
			}
			$signature .= '$' . $this->_suppressSpurious($parameter->getName());
			if ($this->_isOptional($parameter)) {
				$signature .= ' = null';
			}
			$signatures[] = $signature;
		}
		return $signatures;
	}

	/**
	 *    The SPL library has problems with the
	 *    Reflection library. In particular, you can
	 *    get extra characters in parameter names :(.
	 *    @param string $name    Parameter name.
	 *    @return string         Cleaner name.
	 *    @access private
	 */
	function _suppressSpurious($name) {
		return str_replace(array('[', ']', ' '), '', $name);
	}

	/**
	 *    Test of a reflection parameter being optional
	 *    that works with early versions of PHP5.
	 *    @param reflectionParameter $parameter    Is this optional.
	 *    @return boolean                          True if optional.
	 *    @access private
	 */
	function _isOptional($parameter) {
		if (method_exists($parameter, 'isOptional')) {
			return $parameter->isOptional();
		}
		return false;
	}
}
?>