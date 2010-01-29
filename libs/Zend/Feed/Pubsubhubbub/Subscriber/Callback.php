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
 * @see Zend_Feed_Pubsubhubbub
 */
require_once 'Zend/Feed/Pubsubhubbub/CallbackAbstract.php';

/**
 * @see Zend_Feed_Reader
 */
require_once 'Zend/Feed/Reader.php';

/**
 * @category   Zend
 * @package    Zend_Feed_Pubsubhubbub
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Feed_Pubsubhubbub_Subscriber_Callback
    extends Zend_Feed_Pubsubhubbub_CallbackAbstract
{
    /**
     * Contains the content of any feeds sent as updates to the Callback URL
     *
     * @var string
     */
    protected $_feedUpdate = null;
    
    /**
     * Holds a manually set subscription key (i.e. identifies a unique
     * subscription) which is typical when it is not passed in the query string
     * but is part of the Callback URL path, requiring manual retrieval e.g.
     * using a route and the Zend_Controller_Action::_getParam() method.
     *
     * @var string
     */
    protected $_subscriptionKey = null;
    
    /**
     * After verification, this is set to the verified subscription's data.
     *
     * @var array
     */
    protected $_currentSubscriptionData = null;
    
    /**
     * Set a subscription key to use for the current callback request manually.
     * Required if usePathParameter is enabled for the Subscriber.
     *
     * @param  string $key
     * @return Zend_Feed_Pubsubhubbub_Subscriber_Callback
     */
    public function setSubscriptionKey($key)
    {
        $this->_subscriptionKey = $key;
        return $this;
    }

    /**
     * Handle any callback from a Hub Server responding to a subscription or
     * unsubscription request. This should be the Hub Server confirming the
     * the request prior to taking action on it.
     *
     * @param  array $httpGetData GET data if available and not in $_GET
     * @param  bool $sendResponseNow Whether to send response now or when asked
     * @return void
     */
    public function handle(array $httpGetData = null, $sendResponseNow = false)
    {
        if ($httpGetData === null) {
            $httpGetData = $_GET;
        }

        /**
         * Handle any feed updates (sorry for the mess :P)
         *
         * This DOES NOT attempt to process a feed update. Feed updates
         * SHOULD be validated/processed by an asynchronous process so as
         * to avoid holding up responses to the Hub.
         */
        if (strtolower($_SERVER['REQUEST_METHOD']) == 'post'
            && $this->_hasValidVerifyToken(null, false)
            && ($this->_getHeader('Content-Type') == 'application/atom+xml'
                || $this->_getHeader('Content-Type') == 'application/rss+xml'
                || $this->_getHeader('Content-Type') == 'application/rdf+xml')
        ) {
            $this->setFeedUpdate($this->_getRawBody());
            $this->getHttpResponse()
                 ->setHeader('X-Hub-On-Behalf-Of', $this->getSubscriberCount());
        /**
         * Handle any (un)subscribe confirmation requests
         */
        } elseif ($this->isValidHubVerification($httpGetData)) {
            $data = $this->_currentSubscriptionData;
            $this->getHttpResponse()->setBody($httpGetData['hub_challenge']);
            $data['subscription_state'] = Zend_Feed_Pubsubhubbub::SUBSCRIPTION_VERIFIED;
            if (isset($httpGetData['hub_lease_seconds'])) {
                $data['lease_seconds'] = $httpGetData['hub_lease_seconds'];
            }
            $this->getStorage()->setSubscription($data);
        /**
         * Hey, C'mon! We tried everything else!
         */
        } else {
            $this->getHttpResponse()->setHttpResponseCode(404);
        }
        if ($sendResponseNow) {
            $this->sendResponse();
        }
    }

    /**
     * Checks validity of the request simply by making a quick pass and
     * confirming the presence of all REQUIRED parameters.
     *
     * @param  array $httpGetData
     * @return bool
     */
    public function isValidHubVerification(array $httpGetData)
    {
        /**
         * As per the specification, the hub.verify_token is OPTIONAL. This
         * implementation of Pubsubhubbub considers it REQUIRED and will
         * always send a hub.verify_token parameter to be echoed back
         * by the Hub Server. Therefore, its absence is considered invalid.
         */
        if (strtolower($_SERVER['REQUEST_METHOD']) !== 'get') {
            return false;
        }
        $required = array(
            'hub_mode', 
            'hub_topic',
            'hub_challenge', 
            'hub_verify_token',
        );
        foreach ($required as $key) {
            if (!array_key_exists($key, $httpGetData)) {
                return false;
            }
        }
        if ($httpGetData['hub_mode'] !== 'subscribe'
            && $httpGetData['hub_mode'] !== 'unsubscribe'
        ) {
            return false;
        }
        if ($httpGetData['hub_mode'] == 'subscribe'
            && !array_key_exists('hub_lease_seconds', $httpGetData)
        ) {
            return false;
        }
        if (!Zend_Uri::check($httpGetData['hub_topic'])) {
            return false;
        }

        /**
         * Attempt to retrieve any Verification Token Key attached to Callback
         * URL's path by our Subscriber implementation
         */
        if (!$this->_hasValidVerifyToken($httpGetData)) {
            return false;
        }
        return true;
    }

    /**
     * Sets a newly received feed (Atom/RSS) sent by a Hub as an update to a
     * Topic we've subscribed to.
     *
     * @param  string $feed
     * @return Zend_Feed_Pubsubhubbub_Subscriber_Callback
     */
    public function setFeedUpdate($feed)
    {
        $this->_feedUpdate = $feed;
        return $this;
    }

    /**
     * Check if any newly received feed (Atom/RSS) update was received
     *
     * @return bool
     */
    public function hasFeedUpdate()
    {
        if (is_null($this->_feedUpdate)) {
            return false;
        }
        return true;
    }

    /**
     * Gets a newly received feed (Atom/RSS) sent by a Hub as an update to a
     * Topic we've subscribed to.
     *
     * @return string
     */
    public function getFeedUpdate()
    {
        return $this->_feedUpdate;
    }

    /**
     * Check for a valid verify_token. By default attempts to compare values
     * with that sent from Hub, otherwise merely ascertains its existence.
     *
     * @param  array $httpGetData
     * @param  bool $checkValue
     * @return bool
     */
    protected function _hasValidVerifyToken(array $httpGetData = null, $checkValue = true)
    {
        $verifyTokenKey = $this->_detectVerifyTokenKey($httpGetData);
        if (empty($verifyTokenKey)) {
            return false;
        }
        $verifyTokenExists = $this->getStorage()->hasSubscription($verifyTokenKey);
        if (!$verifyTokenExists) {
            return false;
        }
        if ($checkValue) {
            $data = $this->getStorage()->getSubscription($verifyTokenKey);
            $verifyToken = $data['verify_token'];
            if ($verifyToken !== hash('sha256', $httpGetData['hub_verify_token'])) {
                return false;
            }
            $this->_currentSubscriptionData = $data;
            return true;
        }
        return true;
    }

    /**
     * Attempt to detect the verification token key. This would be passed in
     * the Callback URL (which we are handling with this class!) as a URI
     * path part (the last part by convention).
     *
     * @param  null|array $httpGetData
     * @return false|string
     */
    protected function _detectVerifyTokenKey(array $httpGetData = null)
    {
        /**
         * Available when sub keys encoding in Callback URL path
         */
        if (isset($this->_subscriptionKey)) {
            return $this->_subscriptionKey;
        }

        /**
         * Available only if allowed by PuSH 0.2 Hubs
         */
        if (is_array($httpGetData)
            && isset($httpGetData['xhub_subscription'])
        ) {
            return $httpGetData['xhub_subscription'];
        }

        /**
         * Available (possibly) if corrupted in transit and not part of $_GET
         */
        $params = $this->_parseQueryString();
        if (isset($params['xhub.subscription'])) {
            return rawurldecode($params['xhub.subscription']);
        }

        return false;
    }

    /**
     * Build an array of Query String parameters.
     * This bypasses $_GET which munges parameter names and cannot accept
     * multiple parameters with the same key.
     *
     * @return array|void
     */
    protected function _parseQueryString()
    {
        $params      = array();
        $queryString = '';
        if (isset($_SERVER['QUERY_STRING'])) {
            $queryString = $_SERVER['QUERY_STRING'];
        }
        if (empty($queryString)) {
            return array();
        }
        $parts = explode('&', $queryString);
        foreach ($parts as $kvpair) {
            $pair  = explode('=', $kvpair);
            $key   = rawurldecode($pair[0]);
            $value = rawurldecode($pair[1]);
            if (isset($params[$key])) {
                if (is_array($params[$key])) {
                    $params[$key][] = $value;
                } else {
                    $params[$key] = array($params[$key], $value);
                }
            } else {
                $params[$key] = $value;
            }
        }
        return $params;
    }
}
