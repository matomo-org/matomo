<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to version 1.0 of the Zend Framework
 * license, that is bundled with this package in the file LICENSE, and
 * is available through the world-wide-web at the following URL:
 * http://www.zend.com/license/framework/1_0.txt. If you did not receive
 * a copy of the Zend Framework license and are unable to obtain it
 * through the world-wide-web, please send a note to license@zend.com
 * so we can mail you a copy immediately.
 *
 * @package    Zend_XmlRpc
 * @subpackage Server
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://www.zend.com/license/framework/1_0.txt Zend Framework License version 1.0
 */

/**
 * Implement Zend_Server_Interface
 */
require_once 'Zend/Server/Interface.php';

/**
 * Exception this class throws
 */
require_once 'Zend/XmlRpc/Server/Exception.php';

/**
 * XMLRPC Request
 */
require_once 'Zend/XmlRpc/Request.php';

/**
 * XMLRPC Response
 */
require_once 'Zend/XmlRpc/Response.php';

/**
 * XMLRPC HTTP Response
 */
require_once 'Zend/XmlRpc/Response/Http.php';

/**
 * XMLRPC server fault class
 */
require_once 'Zend/XmlRpc/Server/Fault.php';

/**
 * Convert PHP to and from xmlrpc native types
 */
require_once 'Zend/XmlRpc/Value.php';

/**
 * Reflection API for function/method introspection
 */
require_once 'Zend/Server/Reflection.php';

/**
 * Zend_Server_Reflection_Function_Abstract
 */
require_once 'Zend/Server/Reflection/Function/Abstract.php';

/**
 * Specifically grab the Zend_Server_Reflection_Method for manually setting up 
 * system.* methods and handling callbacks in {@link loadFunctions()}.
 */
require_once 'Zend/Server/Reflection/Method.php';

/**
 * An XML-RPC server implementation
 *
 * Example:
 * <code>
 * require_once 'Zend/XmlRpc/Server.php';
 * require_once 'Zend/XmlRpc/Server/Cache.php';
 * require_once 'Zend/XmlRpc/Server/Fault.php';
 * require_once 'My/Exception.php';
 * require_once 'My/Fault/Observer.php';
 *
 * // Instantiate server
 * $server = new Zend_XmlRpc_Server();
 *
 * // Allow some exceptions to report as fault responses:
 * Zend_XmlRpc_Server_Fault::attachFaultException('My_Exception');
 * Zend_XmlRpc_Server_Fault::attachObserver('My_Fault_Observer');
 *
 * // Get or build dispatch table:
 * if (!Zend_XmlRpc_Server_Cache::get($filename, $server)) {
 *     require_once 'Some/Service/Class.php';
 *     require_once 'Another/Service/Class.php';
 *
 *     // Attach Some_Service_Class in 'some' namespace
 *     $server->setClass('Some_Service_Class', 'some');
 *
 *     // Attach Another_Service_Class in 'another' namespace
 *     $server->setClass('Another_Service_Class', 'another');
 *
 *     // Create dispatch table cache file
 *     Zend_XmlRpc_Server_Cache::save($filename, $server);
 * }
 *
 * $response = $server->handle();
 * echo $response;
 * </code>
 *
 * @category   Zend
 * @package    Zend_XmlRpc
 * @subpackage Server
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://www.zend.com/license/framework/1_0.txt Zend Framework License version 1.0
 */
class Zend_XmlRpc_Server
{
    /**
     * Character encoding
     * @var string 
     */
    protected $_encoding = 'UTF-8';

    /**
     * Array of dispatchables
     * @var array
     */
    protected $_methods = array();

    /**
     * Request processed
     * @var null|Zend_XmlRpc_Request 
     */
    protected $_request = null;

    /**
     * Class to use for responses; defaults to {@link Zend_XmlRpc_Response_Http}
     * @var string 
     */
    protected $_responseClass = 'Zend_XmlRpc_Response_Http';

    /**
     * Dispatch table of name => method pairs
     * @var array 
     */
    protected $_table = array();

    /**
     * PHP types => XML-RPC types
     * @var array
     */
    protected $_typeMap = array(
        'i4'               => 'i4',
        'int'              => 'int',
        'integer'          => 'int',
        'double'           => 'double',
        'float'            => 'double',
        'real'             => 'double',
        'boolean'          => 'boolean',
        'bool'             => 'boolean',
        'true'             => 'boolean',
        'false'            => 'boolean',
        'string'           => 'string',
        'str'              => 'string',
        'base64'           => 'base64',
        'dateTime.iso8601' => 'dateTime.iso8601',
        'date'             => 'dateTime.iso8601',
        'time'             => 'dateTime.iso8601',
        'time'             => 'dateTime.iso8601',
        'array'            => 'array',
        'struct'           => 'struct',
        'null'             => 'void',
        'void'             => 'void',
        'mixed'            => 'struct'
    );

    /**
     * Constructor
     *
     * Creates system.* methods.
     *
     * @return void
     */
    public function __construct()
    {
        // Setup system.* methods
        $system = array(
            'listMethods',
            'methodHelp',
            'methodSignature',
            'multicall'
        );

        $class = Zend_Server_Reflection::reflectClass($this);
        foreach ($system as $method) {
            $reflection = new Zend_Server_Reflection_Method($class, new ReflectionMethod($this, $method), 'system');
            $reflection->system = true;
            $this->_methods[] = $reflection;
        }

        $this->_buildDispatchTable();
    }

    /**
     * Map PHP parameter types to XML-RPC types
     * 
     * @param Zend_Server_Reflection_Function_Abstract $method 
     * @return void
     */
    protected function _fixTypes(Zend_Server_Reflection_Function_Abstract $method)
    {
        foreach ($method->getPrototypes() as $prototype) {
            foreach ($prototype->getParameters() as $param) {
                $pType = $param->getType();
                if (isset($this->_typeMap[$pType])) {
                    $param->setType($this->_typeMap[$pType]);
                } else {
                    $param->setType('void');
                }
            }
        }
    }

    /**
     * Re/Build the dispatch table
     *
     * The dispatch table consists of a an array of method name => 
     * Zend_Server_Reflection_Function_Abstract pairs
     * 
     * @return void
     */
    protected function _buildDispatchTable()
    {
        $table      = array();
        foreach ($this->_methods as $dispatchable) {
            if ($dispatchable instanceof Zend_Server_Reflection_Function_Abstract) {
                // function/method call
                $ns   = $dispatchable->getNamespace();
                $name = $dispatchable->getName();
                $name = empty($ns) ? $name : $ns . '.' . $name;

                if (isset($table[$name])) {
                    throw new Zend_XmlRpc_Server_Exception('Duplicate method registered: ' . $name);
                }
                $table[$name] = $dispatchable;
                $this->_fixTypes($dispatchable);

                continue;
            }

            if ($dispatchable instanceof Zend_Server_Reflection_Class) {
                foreach ($dispatchable->getMethods() as $method) {
                    $ns   = $method->getNamespace();
                    $name = $method->getName();
                    $name = empty($ns) ? $name : $ns . '.' . $name;

                    if (isset($table[$name])) {
                        throw new Zend_XmlRpc_Server_Exception('Duplicate method registered: ' . $name);
                    }
                    $table[$name] = $method;
                    $this->_fixTypes($method);
                    continue;
                }
            }
        }

        $this->_table = $table;
    }

    /**
     * Set encoding
     * 
     * @param string $encoding 
     * @return Zend_XmlRpc_Server
     */
    public function setEncoding($encoding)
    {
        $this->_encoding = $encoding;
        return $this;
    }

    /**
     * Retrieve current encoding
     * 
     * @return string
     */
    public function getEncoding()
    {
        return $this->_encoding;
    }

    /**
     * Attach a callback as an XMLRPC method
     *
     * Attaches a callback as an XMLRPC method, prefixing the XMLRPC method name 
     * with $namespace, if provided. Reflection is done on the callback's 
     * docblock to create the methodHelp for the XMLRPC method.
     *
     * Additional arguments to pass to the function at dispatch may be passed; 
     * any arguments following the namespace will be aggregated and passed at 
     * dispatch time.
     *
     * @param string|array $function Valid callback
     * @param string $namespace Optional namespace prefix
     * @return void
     * @throws Zend_XmlRpc_Server_Exception
     */
    public function addFunction($function, $namespace = '') 
    {
        if (!is_string($function) && !is_array($function)) {
            throw new Zend_XmlRpc_Server_Exception('Unable to attach function; invalid', 611);
        }

        $argv = null;
        if (2 < func_num_args()) {
            $argv = func_get_args();
            $argv = array_slice($argv, 2);
        }

        $function = (array) $function;
        foreach ($function as $func) {
            if (!is_string($func) || !function_exists($func)) {
                throw new Zend_XmlRpc_Server_Exception('Unable to attach function; invalid', 611);
            }
            $this->_methods[] = Zend_Server_Reflection::reflectFunction($func, $argv, $namespace);
        }

        $this->_buildDispatchTable();
    }

    /**
     * Load methods as returned from {@link getFunctions}
     *
     * Typically, you will not use this method; it will be called using the 
     * results pulled from {@link Zend_XmlRpc_Server_Cache::get()}.
     * 
     * @param array $array 
     * @return void
     * @throws Zend_XmlRpc_Server_Exception on invalid input
     */
    public function loadFunctions($array)
    {
        if (!is_array($array)) {
            throw new Zend_XmlRpc_Server_Exception('Unable to load array; not an array', 612);
        }

        foreach ($array as $key => $value) {
            if (!$value instanceof Zend_Server_Reflection_Function_Abstract
                && !$value instanceof Zend_Server_Reflection_Class) 
            {
                throw new Zend_XmlRpc_Server_Exception('One or more method records are corrupt or otherwise unusable', 613);
            }

            if ($value->system) {
                unset($array[$key]);
            }
        }

        foreach ($array as $dispatchable) {
            $this->_methods[] = $dispatchable;
        }

        $this->_buildDispatchTable();
    }

    /**
     * Do nothing; persistence is handled via {@link Zend_XmlRpc_Server_Cache}
     * 
     * @param mixed $class 
     * @return void
     */
    public function setPersistence($class = null)
    {
    }

    /**
     * Attach class methods as XMLRPC method handlers
     *
     * $class may be either a class name or an object. Reflection is done on the 
     * class or object to determine the available public methods, and each is 
     * attached to the server as an available method; if a $namespace has been 
     * provided, that namespace is used to prefix the XMLRPC method names.
     *
     * Any additional arguments beyond $namespace will be passed to a method at 
     * invocation.
     *
     * @param string|object $class 
     * @param string $namespace Optional
     * @param mixed $argv Optional arguments to pass to methods
     * @return void
     * @throws Zend_XmlRpc_Server_Exception on invalid input
     */
    public function setClass($class, $namespace = '', $argv = null)
    {
        if (is_string($class) && !class_exists($class)) {
            if (!class_exists($class)) {
                throw new Zend_XmlRpc_Server_Exception('Invalid method class', 610);
            }
        }

        $argv = null;
        if (3 < func_num_args()) {
            $argv = func_get_args();
            $argv = array_slice($argv, 3);
        }

        $this->_methods[] = Zend_Server_Reflection::reflectClass($class, $argv, $namespace);
        $this->_buildDispatchTable();
    }

    /**
     * Set the request object
     * 
     * @param string|Zend_XmlRpc_Request $request 
     * @return Zend_XmlRpc_Server
     * @throws Zend_XmlRpc_Server_Exception on invalid request class or object
     */
    public function setRequest($request)
    {
        if (is_string($request) && class_exists($request)) {
            $request = new $request();
            if (!$request instanceof Zend_XmlRpc_Request) {
                throw new Zend_XmlRpc_Server_Exception('Invalid request class');
            }
            $request->setEncoding($this->getEncoding());
        } elseif (!$request instanceof Zend_XmlRpc_Request) {
            throw new Zend_XmlRpc_Server_Exception('Invalid request object');
        }

        $this->_request = $request;
        return $this;
    }

    /**
     * Return currently registered request object
     * 
     * @return null|Zend_XmlRpc_Request
     */
    public function getRequest()
    {
        return $this->_request;
    }

    /**
     * Raise an xmlrpc server fault
     * 
     * @param string|Exception $fault 
     * @param int $code 
     * @return Zend_XmlRpc_Server_Fault
     */
    public function fault($fault, $code = 404)
    {
        if (!$fault instanceof Exception) {
            $fault = (string) $fault;
            $fault = new Zend_XmlRpc_Server_Exception($fault, $code);
        }

        return Zend_XmlRpc_Server_Fault::getInstance($fault);
    }

    /**
     * Handle an xmlrpc call (actual work)
     *
     * @param Zend_XmlRpc_Request $request
     * @return Zend_XmlRpc_Response
     * @throws Zend_XmlRpcServer_Exception|Exception 
     * Zend_XmlRpcServer_Exceptions are thrown for internal errors; otherwise, 
     * any other exception may be thrown by the callback
     */
    protected function _handle(Zend_XmlRpc_Request $request) 
    {
        $method = $request->getMethod();

        // Check for valid method
        if (!isset($this->_table[$method])) {
            throw new Zend_XmlRpc_Server_Exception('Method "' . $method . '" does not exist', 620);
        }

        $info     = $this->_table[$method];
        $params   = $request->getParams();
        $argv     = $info->getInvokeArguments();
        if (0 < count($argv)) {
            $params = array_merge($params, $argv);
        }

        // Check calling parameters against signatures
        $matched    = false;
        $sigCalled  = array();
        foreach ($params as $param) {
            $value = Zend_XmlRpc_Value::getXmlRpcValue($param);
            $sigCalled[] = $value->getType();
        }
        $signatures = $info->getPrototypes();
        foreach ($signatures as $signature) {
            $sigParams = $signature->getParameters();
            $tmpParams = array();
            foreach ($sigParams as $param) {
                $tmpParams[] = $param->getType();
            }
            if ($sigCalled === $tmpParams) {
                $matched = true;
                break;
            }
        }
        if (!$matched) {
            throw new Zend_XmlRpc_Server_Exception('Calling parameters do not match signature', 623);
        }

        if ($info instanceof Zend_Server_Reflection_Function) {
            $func = $info->getName();
            $return = call_user_func_array($func, $params);
        } elseif (($info instanceof Zend_Server_Reflection_Method) && $info->system) {
            // System methods
            $return = $info->invokeArgs($this, $params);
        } elseif ($info instanceof Zend_Server_Reflection_Method) {
            // Get class
            $class = $info->getDeclaringClass()->getName();

            if ('static' == $info->isStatic()) {
                // for some reason, invokeArgs() does not work the same as 
                // invoke(), and expects the first argument to be an object. 
                // So, using a callback if the method is static.
                $return = call_user_func_array(array($class, $info->getName()), $params);
            } else {
                // Object methods
                try {
                    $object = $info->getDeclaringClass()->newInstance();
                } catch (Exception $e) {
                    throw new Zend_XmlRpc_Server_Exception('Error instantiating class ' . $class . ' to invoke method ' . $info->getName(), 621);
                }

                $return = $info->invokeArgs($object, $params);
            }
        } else {
            throw new Zend_XmlRpc_Server_Exception('Method missing implementation ' . get_class($info), 622);
        }

        $response = new ReflectionClass($this->_responseClass);
        return $response->newInstance($return);
    }

    /**
     * Handle an xmlrpc call
     *
     * @param Zend_XmlRpc_Request $request Optional
     * @return Zend_XmlRpc_Response|Zend_XmlRpc_Fault
     */
    public function handle(Zend_XmlRpc_Request $request = null) 
    {
        // Get request
        if ((null === $request) && (null === ($request = $this->getRequest()))) {
            require_once 'Zend/XmlRpc/Request/Http.php';
            $request = new Zend_XmlRpc_Request_Http();
            $request->setEncoding($this->getEncoding());
        }

        $this->setRequest($request);

        if ($request->isFault()) {
            $response = $request->getFault();
        } else {
            try {
                $response = $this->_handle($request);
            } catch (Exception $e) {
                $response = $this->fault($e);
            }
        }

        // Set output encoding
        $response->setEncoding($this->getEncoding());

        return $response;
    }

    /**
     * Set the class to use for the response
     * 
     * @param string $class 
     * @return boolean True if class was set, false if not
     */
    public function setResponseClass($class)
    {
        if (class_exists($class)) {
            $reflection = new ReflectionClass($class);
            if ($reflection->isSubclassOf(new ReflectionClass('Zend_XmlRpc_Response'))) {
                $this->_responseClass = $class;
                return true;
            }
        }

        return false;
    }

    /**
     * Returns a list of registered methods
     *
     * Returns an array of dispatchables (Zend_Server_Reflection_Function, 
     * _Method, and _Class items).
     * 
     * @return array
     */
    public function getFunctions()
    {
        $return = array();
        foreach ($this->_methods as $method) {
            if ($method instanceof Zend_Server_Reflection_Class
                && ($method->system)) 
            {
                continue;
            }

            $return[] = $method;
        }

        return $return;
    }

    /**
     * List all available XMLRPC methods
     *
     * Returns an array of methods.
     * 
     * @return array
     */
    public function listMethods()
    {
        return array_keys($this->_table);
    }

    /**
     * Display help message for an XMLRPC method
     * 
     * @param string $method
     * @return string
     */
    public function methodHelp($method)
    {
        if (!isset($this->_table[$method])) {
            throw new Zend_Server_Exception('Method "' . $method . '"does not exist', 640);
        }

        return $this->_table[$method]->getDescription();
    }

    /**
     * Return a method signature
     * 
     * @param string $method
     * @return array
     */
    public function methodSignature($method)
    {
        if (!isset($this->_table[$method])) {
            throw new Zend_Server_Exception('Method "' . $method . '"does not exist', 640);
        }
        $prototypes = $this->_table[$method]->getPrototypes();

        $signatures = array();
        foreach ($prototypes as $prototype) {
            $signature = array($prototype->getReturnType());
            foreach ($prototype->getParameters() as $parameter) {
                $signature[] = $parameter->getType();
            }
            $signatures[] = $signature;
        }

        return $signatures;
    }

    /**
     * Multicall - boxcar feature of XML-RPC for calling multiple methods
     * in a single request.
     *
     * Expects a an array of structs representing method calls, each element
     * having the keys:
     * - methodName
     * - params
     *
     * Returns an array of responses, one for each method called, with the value
     * returned by the method. If an error occurs for a given method, returns a
     * struct with a fault response.
     *
     * @see http://www.xmlrpc.com/discuss/msgReader$1208
     * @param array $methods
     * @return array
     */
    public function multicall($methods) 
    {
        $responses = array();
        foreach ($methods as $method) {
            $fault = false;
            if (!is_array($method)) {
                $fault = $this->fault('system.multicall expects each method to be a struct', 601);
            } elseif (!isset($method['methodName'])) {
                $fault = $this->fault('Missing methodName', 602);
            } elseif (!isset($method['params'])) {
                $fault = $this->fault('Missing params', 603);
            } elseif (!is_array($method['params'])) {
                $fault = $this->fault('Params must be an array', 604);
            } else {
                if ('system.multicall' == $method['methodName']) {
                    // don't allow recursive calls to multicall
                    $fault = $this->fault('Recursive system.multicall forbidden', 605);
                }
            }

            if (!$fault) {
                try {
                    $request = new Zend_XmlRpc_Request();
                    $request->setMethod($method['methodName']);
                    $request->setParams($method['params']);
                    $response = $this->_handle($request);
                    $responses[] = $response->getReturnValue();
                } catch (Exception $e) {
                    $fault = $this->fault($e);
                }
            }

            if ($fault) {
                $responses[] = array(
                    'faultCode'   => $fault->getCode(),
                    'faultString' => $fault->getMessage()
                );
            }
        }

        return $responses;
    }
}
