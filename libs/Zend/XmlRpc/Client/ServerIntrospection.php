<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_XmlRpc
 * @subpackage Client
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */


/** Zend_XmlRpc_Client_IntrospectException */
require_once 'Zend/XmlRpc/Client/IntrospectException.php';


/**
 * Wraps the XML-RPC system.* introspection methods
 *
 * @category   Zend
 * @package    Zend_XmlRpc
 * @subpackage Client
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_XmlRpc_Client_ServerIntrospection
{
    /**
     * @var Zend_XmlRpc_Client_ServerProxy
     */
    private $_system = null;

    
    /**
     * @param Zend_XmlRpc_Client $client 
     */
    public function __construct(Zend_XmlRpc_Client $client)
    {
        $this->_system = $client->getProxy('system');
    }

    /**
     * Returns the signature for each method on the server,
     * autodetecting whether system.multicall() is supported and
     * using it if so.
     *
     * @return array
     */
    public function getSignatureForEachMethod()
    {
        $methods = $this->listMethods();

        try {
            $signatures = $this->getSignatureForEachMethodByMulticall($methods);
        } catch (Zend_XmlRpc_Client_FaultException $e) {
            // degrade to looping
        }
        
        if (empty($signatures)) {
            $signatures = $this->getSignatureForEachMethodByLooping($methods);
        }
        
        return $signatures;
    }

    /**
     * Attempt to get the method signatures in one request via system.multicall().
     * This is a boxcar feature of XML-RPC and is found on fewer servers.  However,
     * can significantly improve performance if present.
     *
     * @param  array $methods 
     * @return array array(array(return, param, param, param...))
     */
    public function getSignatureForEachMethodByMulticall($methods = null)
    {
        if ($methods === null) {
            $methods = $this->listMethods();
        }
        
        $multicallParams = array();
        foreach ($methods as $method) {
            $multicallParams[] = array('methodName' => 'system.methodSignature',
                                       'params'     => array($method));
        }

        $serverSignatures = $this->_system->multicall($multicallParams);

        if (! is_array($serverSignatures)) {
            $type = gettype($serverSignatures);
            $error = "Multicall return is malformed.  Expected array, got $type";
            throw new Zend_XmlRpc_Client_IntrospectException($error);
        } 
        
        if (count($serverSignatures) != count($methods)) {
            $error = 'Bad number of signatures received from multicall';
            throw new Zend_XmlRpc_Client_IntrospectException($error);
        }

        // Create a new signatures array with the methods name as keys and the signature as value
        $signatures = array();
        foreach ($serverSignatures as $i => $signature) {
            $signatures[$methods[$i]] = $signature;
        }

        return $signatures;
    }
    
    /**
     * Get the method signatures for every method by
     * successively calling system.methodSignature
     *
     * @param array $methods
     * @return array
     */
    public function getSignatureForEachMethodByLooping($methods = null)
    {
        if ($methods === null) {
            $methods = $this->listMethods();
        }

        $signatures = array();
        foreach ($methods as $method) {
            $signatures[$method] = $this->getMethodSignature($method);
        }

        return $signatures;
    }

    /**
     * Call system.methodSignature() for the given method
     *
     * @param  array  $method 
     * @return array  array(array(return, param, param, param...))
     */
    public function getMethodSignature($method)
    {
        $signature = $this->_system->methodSignature($method);
        return $signature;
    }

    /**
     * Call system.listMethods()
     *
     * @param  array  $method 
     * @return array  array(method, method, method...)
     */
    public function listMethods()
    {
        return $this->_system->listMethods();
    }
    
}
