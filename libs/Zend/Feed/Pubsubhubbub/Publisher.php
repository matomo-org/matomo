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
 * @package    Zend_Feed_Pubsubhubbub
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * @see Zend_Feed_Pubsubhubbub
 */
require_once 'Zend/Feed/Pubsubhubbub.php';

/**
 * @category   Zend
 * @package    Zend_Feed_Pubsubhubbub
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Feed_Pubsubhubbub_Publisher
{
    /**
     * An array of URLs for all Hub Servers used by the Publisher, and to
     * which all topic update notifications will be sent.
     *
     * @var array
     */
    protected $_hubUrls = array();

    /**
     * An array of topic (Atom or RSS feed) URLs which have been updated and
     * whose updated status will be notified to all Hub Servers.
     *
     * @var array
     */
    protected $_updatedTopicUrls = array();

    /**
     * An array of any errors including keys for 'response', 'hubUrl'.
     * The response is the actual Zend_Http_Response object.
     *
     * @var array
     */
    protected $_errors = array();

    /**
     * An array of topic (Atom or RSS feed) URLs which have been updated and
     * whose updated status will be notified to all Hub Servers.
     *
     * @var array
     */
    protected $_parameters = array();

    /**
     * Constructor; accepts an array or Zend_Config instance to preset
     * options for the Publisher without calling all supported setter
     * methods in turn.
     *
     * @param  array|Zend_Config $options Options array or Zend_Config instance
     * @return void
     */
    public function __construct($config = null)
    {
        if (!is_null($config)) {
            $this->setConfig($config);
        }
    }

    /**
     * Process any injected configuration options
     *
     * @param  array|Zend_Config $options Options array or Zend_Config instance
     * @return Zend_Feed_Pubsubhubbub_Publisher
     */
    public function setConfig($config)
    {
        if ($config instanceof Zend_Config) {
            $config = $config->toArray();
        } elseif (!is_array($config)) {
            require_once 'Zend/Feed/Pubsubhubbub/Exception.php';
            throw new Zend_Feed_Pubsubhubbub_Exception('Array or Zend_Config object'
                . 'expected, got ' . gettype($config));
        }
        if (array_key_exists('hubUrls', $config)) {
            $this->addHubUrls($config['hubUrls']);
        }
        if (array_key_exists('updatedTopicUrls', $config)) {
            $this->addUpdatedTopicUrls($config['updatedTopicUrls']);
        }
        if (array_key_exists('parameters', $config)) {
            $this->setParameters($config['parameters']);
        }
        return $this;
    }

    /**
     * Add a Hub Server URL supported by Publisher
     *
     * @param  string $url
     * @return Zend_Feed_Pubsubhubbub_Publisher
     */
    public function addHubUrl($url)
    {
        if (empty($url) || !is_string($url) || !Zend_Uri::check($url)) {
            require_once 'Zend/Feed/Pubsubhubbub/Exception.php';
            throw new Zend_Feed_Pubsubhubbub_Exception('Invalid parameter "url"'
                .' of "' . $url . '" must be a non-empty string and a valid'
                .'URL');
        }
        $this->_hubUrls[] = $url;
        return $this;
    }

    /**
     * Add an array of Hub Server URLs supported by Publisher
     *
     * @param  array $urls
     * @return Zend_Feed_Pubsubhubbub_Publisher
     */
    public function addHubUrls(array $urls)
    {
        foreach ($urls as $url) {
            $this->addHubUrl($url);
        }
        return $this;
    }

    /**
     * Remove a Hub Server URL
     *
     * @param  string $url
     * @return Zend_Feed_Pubsubhubbub_Publisher
     */
    public function removeHubUrl($url)
    {
        if (!in_array($url, $this->getHubUrls())) {
            return $this;
        }
        $key = array_search($url, $this->_hubUrls);
        unset($this->_hubUrls[$key]);
        return $this;
    }

    /**
     * Return an array of unique Hub Server URLs currently available
     *
     * @return array
     */
    public function getHubUrls()
    {
        $this->_hubUrls = array_unique($this->_hubUrls);
        return $this->_hubUrls;
    }

    /**
     * Add a URL to a topic (Atom or RSS feed) which has been updated
     *
     * @param  string $url
     * @return Zend_Feed_Pubsubhubbub_Publisher
     */
    public function addUpdatedTopicUrl($url)
    {
        if (empty($url) || !is_string($url) || !Zend_Uri::check($url)) {
            require_once 'Zend/Feed/Pubsubhubbub/Exception.php';
            throw new Zend_Feed_Pubsubhubbub_Exception('Invalid parameter "url"'
                .' of "' . $url . '" must be a non-empty string and a valid'
                .'URL');
        }
        $this->_updatedTopicUrls[] = $url;
        return $this;
    }

    /**
     * Add an array of Topic URLs which have been updated
     *
     * @param  array $urls
     * @return Zend_Feed_Pubsubhubbub_Publisher
     */
    public function addUpdatedTopicUrls(array $urls)
    {
        foreach ($urls as $url) {
            $this->addUpdatedTopicUrl($url);
        }
        return $this;
    }

    /**
     * Remove an updated topic URL
     *
     * @param  string $url
     * @return Zend_Feed_Pubsubhubbub_Publisher
     */
    public function removeUpdatedTopicUrl($url)
    {
        if (!in_array($url, $this->getUpdatedTopicUrls())) {
            return $this;
        }
        $key = array_search($url, $this->_updatedTopicUrls);
        unset($this->_updatedTopicUrls[$key]);
        return $this;
    }

    /**
     * Return an array of unique updated topic URLs currently available
     *
     * @return array
     */
    public function getUpdatedTopicUrls()
    {
        $this->_updatedTopicUrls = array_unique($this->_updatedTopicUrls);
        return $this->_updatedTopicUrls;
    }

    /**
     * Notifies a single Hub Server URL of changes
     *
     * @param  string $url The Hub Server's URL
     * @return void
     * @throws Zend_Feed_Pubsubhubbub_Exception Thrown on failure
     */
    public function notifyHub($url)
    {
        if (empty($url) || !is_string($url) || !Zend_Uri::check($url)) {
            require_once 'Zend/Feed/Pubsubhubbub/Exception.php';
            throw new Zend_Feed_Pubsubhubbub_Exception('Invalid parameter "url"'
                .' of "' . $url . '" must be a non-empty string and a valid'
                .'URL');
        }
        $client = $this->_getHttpClient();
        $client->setUri($url);
        $response = $client->request();
        if ($response->getStatus() !== 204) {
            require_once 'Zend/Feed/Pubsubhubbub/Exception.php';
            throw new Zend_Feed_Pubsubhubbub_Exception('Notification to Hub Server '
                . 'at "' . $url . '" appears to have failed with a status code of "'
                . $response->getStatus() . '" and message "'
                . $response->getMessage() . '"');
        }
    }

    /**
     * Notifies all Hub Server URLs of changes
     *
     * If a Hub notification fails, certain data will be retained in an
     * an array retrieved using getErrors(), if a failure occurs for any Hubs
     * the isSuccess() check will return FALSE. This method is designed not
     * to needlessly fail with an Exception/Error unless from Zend_Http_Client.
     *
     * @return void
     * @throws Zend_Feed_Pubsubhubbub_Exception Thrown if no hubs attached
     */
    public function notifyAll()
    {
        $client = $this->_getHttpClient();
        $hubs   = $this->getHubUrls();
        if (empty($hubs)) {
            require_once 'Zend/Feed/Pubsubhubbub/Exception.php';
            throw new Zend_Feed_Pubsubhubbub_Exception('No Hub Server URLs'
                . ' have been set so no notifcations can be sent');
        }
        $this->_errors = array();
        foreach ($hubs as $url) {
            $client->setUri($url);
            $response = $client->request();
            if ($response->getStatus() !== 204) {
                $this->_errors[] = array(
                    'response' => $response,
                    'hubUrl' => $url
                );
            }
        }
    }

    /**
     * Add an optional parameter to the update notification requests
     *
     * @param  string $name
     * @param  string|null $value
     * @return Zend_Feed_Pubsubhubbub_Publisher
     */
    public function setParameter($name, $value = null)
    {
        if (is_array($name)) {
            $this->setParameters($name);
            return $this;
        }
        if (empty($name) || !is_string($name)) {
            require_once 'Zend/Feed/Pubsubhubbub/Exception.php';
            throw new Zend_Feed_Pubsubhubbub_Exception('Invalid parameter "name"'
                .' of "' . $name . '" must be a non-empty string');
        }
        if ($value === null) {
            $this->removeParameter($name);
            return $this;
        }
        if (empty($value) || (!is_string($value) && !is_null($value))) {
            require_once 'Zend/Feed/Pubsubhubbub/Exception.php';
            throw new Zend_Feed_Pubsubhubbub_Exception('Invalid parameter "value"'
                .' of "' . $value . '" must be a non-empty string');
        }
        $this->_parameters[$name] = $value;
        return $this;
    }

    /**
     * Add an optional parameter to the update notification requests
     *
     * @param  array $parameters
     * @return Zend_Feed_Pubsubhubbub_Publisher
     */
    public function setParameters(array $parameters)
    {
        foreach ($parameters as $name => $value) {
            $this->setParameter($name, $value);
        }
        return $this;
    }

    /**
     * Remove an optional parameter for the notification requests
     *
     * @param  string $name
     * @return Zend_Feed_Pubsubhubbub_Publisher
     */
    public function removeParameter($name)
    {
        if (empty($name) || !is_string($name)) {
            require_once 'Zend/Feed/Pubsubhubbub/Exception.php';
            throw new Zend_Feed_Pubsubhubbub_Exception('Invalid parameter "name"'
                .' of "' . $name . '" must be a non-empty string');
        }
        if (array_key_exists($name, $this->_parameters)) {
            unset($this->_parameters[$name]);
        }
        return $this;
    }

    /**
     * Return an array of optional parameters for notification requests
     *
     * @return array
     */
    public function getParameters()
    {
        return $this->_parameters;
    }

    /**
     * Returns a boolean indicator of whether the notifications to Hub
     * Servers were ALL successful. If even one failed, FALSE is returned.
     *
     * @return bool
     */
    public function isSuccess()
    {
        if (count($this->_errors) > 0) {
            return false;
        }
        return true;
    }

    /**
     * Return an array of errors met from any failures, including keys:
     * 'response' => the Zend_Http_Response object from the failure
     * 'hubUrl' => the URL of the Hub Server whose notification failed
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->_errors;
    }

    /**
     * Get a basic prepared HTTP client for use
     *
     * @return Zend_Http_Client
     */
    protected function _getHttpClient()
    {
        $client = Zend_Feed_Pubsubhubbub::getHttpClient();
        $client->setMethod(Zend_Http_Client::POST);
        $client->setConfig(array(
            'useragent' => 'Zend_Feed_Pubsubhubbub_Publisher/' . Zend_Version::VERSION,
        ));
        $params   = array();
        $params[] = 'hub.mode=publish';
        $topics   = $this->getUpdatedTopicUrls();
        if (empty($topics)) {
            require_once 'Zend/Feed/Pubsubhubbub/Exception.php';
            throw new Zend_Feed_Pubsubhubbub_Exception('No updated topic URLs'
                . ' have been set');
        }
        foreach ($topics as $topicUrl) {
            $params[] = 'hub.url=' . urlencode($topicUrl);
        }
        $optParams = $this->getParameters();
        foreach ($optParams as $name => $value) {
            $params[] = urlencode($name) . '=' . urlencode($value);
        }
        $paramString = implode('&', $params);
        $client->setRawData($paramString);
        return $client;
    }
}
