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
 * @package    Zend_Http
 * @subpackage UserAgent
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * Interface defining a browser device type.
 *
 * @category   Zend
 * @package    Zend_Http
 * @subpackage UserAgent
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
interface Zend_Http_UserAgent_Device extends Serializable
{
    /**
     * Constructor
     *
     * Allows injecting user agent, server array, and/or config array. If an
     * array is provided for the first argument, the assumption should be that
     * the device object is being seeded with cached values from serialization.
     *
     * @param  null|string|array $userAgent
     * @param  array $server
     * @param  array $config
     * @return void
     */
    public function __construct($userAgent = null, array $server = array(), array $config = array());

    /**
     * Attempt to match the user agent
     *
     * Return either an array of browser signature strings, or a boolean.
     *
     * @param  string $userAgent
     * @param  array $server
     * @return bool|array
     */
    public static function match($userAgent, $server);

    /**
     * Get all browser/device features
     *
     * @return array
     */
    public function getAllFeatures();

    /**
     * Get all of the browser/device's features' groups
     *
     * @return void
     */
    public function getAllGroups();

    /**
     * Whether or not the device has a given feature
     *
     * @param  string $feature
     * @return bool
     */
    public function hasFeature($feature);

    /**
     * Get the value of a specific device feature
     *
     * @param  string $feature
     * @return mixed
     */
    public function getFeature($feature);

    /**
     * Get the browser type
     *
     * @return string
     */
    public function getBrowser();

    /**
     * Retrurn the browser version
     *
     * @return string
     */
    public function getBrowserVersion();

    /**
     * Get an array of features associated with a group
     *
     * @param  string $group
     * @return array
     */
    public function getGroup($group);

    /**
     * Retrieve image format support
     *
     * @return array
     */
    public function getImageFormatSupport();

    /**
     * Get image types
     *
     * @return array
     */
    public function getImages();

    /**
     * Get the maximum image height supported by this device
     *
     * @return int
     */
    public function getMaxImageHeight();

    /**
     * Get the maximum image width supported by this device
     *
     * @return int
     */
    public function getMaxImageWidth();

    /**
     * Get the physical screen height of this device
     *
     * @return int
     */
    public function getPhysicalScreenHeight();

    /**
     * Get the physical screen width of this device
     *
     * @return int
     */
    public function getPhysicalScreenWidth();

    /**
     * Get the preferred markup type
     *
     * @return string
     */
    public function getPreferredMarkup();

    /**
     * Get the user agent string
     *
     * @return string
     */
    public function getUserAgent();

    /**
     * Get supported X/HTML version
     *
     * @return int
     */
    public function getXhtmlSupportLevel();

    /**
     * Does the device support Flash?
     *
     * @return bool
     */
    public function hasFlashSupport();

    /**
     * Does the device support PDF?
     *
     * @return bool
     */
    public function hasPdfSupport();

    /**
     * Does the device have a phone number associated with it?
     *
     * @return bool
     */
    public function hasPhoneNumber();

    /**
     * Does the device support HTTPS?
     *
     * @return bool
     */
    public function httpsSupport();
}
