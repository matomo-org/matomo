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


/**
 * For handling the HTTP connection to the XML-RPC service
 */
require_once 'Zend/Http/Client.php';

/**
 * Exception thrown when an HTTP error occurs
 */
require_once 'Zend/XmlRpc/Client/HttpException.php';

/**
 * Exception thrown when an XML-RPC fault is returned
 */
require_once 'Zend/XmlRpc/Client/FaultException.php';

/**
 * Enables object chaining for calling namespaced XML-RPC methods.
 */
require_once 'Zend/XmlRpc/Client/ServerProxy.php';

/**
 * Introspects remote servers using the XML-RPC de facto system.* methods
 */
require_once 'Zend/XmlRpc/Client/ServerIntrospection.php';

/**
 * Represent a native XML-RPC value, used both in sending parameters
 * to methods and as the parameters retrieve from method calls
 */
require_once 'Zend/XmlRpc/Value.php';

/**
 * XML-RPC Request
 */
require_once 'Zend/XmlRpc/Request.php';

/**
 * XML-RPC Response
 */
require_once 'Zend/XmlRpc/Response.php';

/**
 * XML-RPC Fault
 */
require_once 'Zend/XmlRpc/Fault.php';


/**
 * An XML-RPC client implementation
 *
 * @category   Zend
 * @package    Zend_XmlRpc
 * @subpackage Client
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_XmlRpc_Client
{
    /** @var string */
    private $_serverAddress;

    /** @var Zend_Http_Client */
    private $_httpClient = null;

    /** @var Zend_Http_Client_Introspector */
    private $_introspector = null;

    /** @var Zend_XmlRpc_Request */
    private $_lastRequest = null;

    /** @var Zend_XmlRpc_Response */
    private $_lastResponse = null;

    /** @var array of Zend_XmlRpc_Client_ServerProxy */
    private $_proxyCache = array();


    /**
     * Create a new XML-RPC client to a remote server
     *
     * @param  string $server      Full address of the XML-RPC service
     *                             (e.g. http://time.xmlrpc.com/RPC2)
     * @param  Zend_Http_Client $httpClient HTTP Client to use for requests
     * @return void
     */
    public function __construct($server, Zend_Http_Client $httpClient = null)
    {
        if ($httpClient === null) {
			$this->_httpClient = new Zend_Http_Client();
        } else {
            $this->_httpClient = $httpClient;
        }

        $this->_introspector  = new Zend_XmlRpc_Client_ServerIntrospection($this);
        $this->_serverAddress = $server;
    }


    /**
     * Sets the HTTP client object to use for connecting the XML-RPC server.
     *
     * @param  Zend_Http_Client $httpClient
     * @return Zend_Http_Client
     */
    public function setHttpClient(Zend_Http_Client $httpClient)
    {
        return $this->_httpClient = $httpClient;
    }


    /**
	 * Gets the HTTP client object.
	 *
	 * @return Zend_Http_Client
	 */
	public function getHttpClient()
	{
		return $this->_httpClient;
	}
	
	
	/**
	 * Sets the object used to introspect remote servers
	 *
	 * @param  Zend_XmlRpc_Client_ServerIntrospection
	 * @return Zend_XmlRpc_Client_ServerIntrospection
	 */
	public function setIntrospector(Zend_XmlRpc_Client_ServerIntrospection $introspector)
	{
	    return $this->_introspector = $introspector;
	}


    /**
     * Gets the introspection object.
     *
     * @return Zend_XmlRpc_Client_ServerIntrospection
     */
	public function getIntrospector()
	{
	    return $this->_introspector;
	}
	

   /**
     * The request of the last method call
     *
     * @return Zend_XmlRpc_Request
     */
    public function getLastRequest()
    {
        return $this->_lastRequest;
    }


    /**
     * The response received from the last method call
     *
     * @return Zend_XmlRpc_Response
     */
    public function getLastResponse()
    {
        return $this->_lastResponse;
    }
    
    
    /**
     * Returns a proxy object for more convenient method calls
     *
     * @param $namespace  Namespace to proxy or empty string for none
     * @return Zend_XmlRpc_Client_ServerProxy
     */
    public function getProxy($namespace = '')
    {
        if (empty($this->_proxyCache[$namespace])) {
            $proxy = new Zend_XmlRpc_Client_ServerProxy($this, $namespace);
            $this->_proxyCache[$namespace] = $proxy;
        }
        return $this->_proxyCache[$namespace];
    }
    
    
    /**
     * Perform an XML-RPC request and return a response.
     *
     * @param Zend_XmlRpc_Request $request
     * @param null|Zend_XmlRpc_Response $response
     * @return void
     */
    public function doRequest($request, $response = null)
    {
        $this->_lastRequest = $request;

        iconv_set_encoding('input_encoding', 'UTF-8');
        iconv_set_encoding('output_encoding', 'UTF-8');
        iconv_set_encoding('internal_encoding', 'UTF-8');

        $http = $this->getHttpClient();
        $http->setUri($this->_serverAddress);

        $http->setHeaders(array(
            'Content-Type: text/xml; charset=utf-8',
            'User-Agent: Zend_XmlRpc_Client'
        ));

        $xml = $this->_lastRequest->__toString();
        $http->setRawData($xml);
        $httpResponse = $http->request(Zend_Http_Client::POST);

        if (! $httpResponse->isSuccessful()) {
            throw new Zend_XmlRpc_Client_HttpException(
                                    $httpResponse->getMessage(),
                                    $httpResponse->getStatus());
        }

        if ($response === null) {
            $response = new Zend_XmlRpc_Response();
        }
        $this->_lastResponse = $response;        
        $this->_lastResponse->loadXml($httpResponse->getBody());
    }
    

    /**
     * Send an XML-RPC request to the service (for a specific method)
     * 
     * @param string $method Name of the method we want to call
     * @param array $params Array of parameters for the method
     * @throws Zend_Http_Client_FaultException
     */
    public function call($method, $params=array())
    {
        $request = new Zend_XmlRpc_Request($method, $params);
        
        $this->doRequest($request);

        if ($this->_lastResponse->isFault()) {
            $fault = $this->_lastResponse->getFault();
            throw new Zend_XmlRpc_Client_FaultException($fault->getMessage(),
                                                        $fault->getCode());
        }
        
        return $this->_lastResponse->getReturnValue();
    }    
}
