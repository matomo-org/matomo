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
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * Zend_Http_UserAgent_Features_Adapter_Interface
 */
// require_once 'Zend/Http/UserAgent/Features/Adapter.php';

/**
 * Features adapter build with the Tera Wurfl Api
 * See installation instruction here : http://www.tera-wurfl.com/wiki/index.php/Installation
 * Download : http://www.tera-wurfl.com/wiki/index.php/Downloads
 *
 * @package    Zend_Http
 * @subpackage UserAgent
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Http_UserAgent_Features_Adapter_TeraWurfl implements Zend_Http_UserAgent_Features_Adapter
{
    /**
     * Get features from request
     *
     * @param  array $request $_SERVER variable
     * @return array
     */
    public static function getFromRequest($request, array $config)
    {
        if (!class_exists('TeraWurfl')) {
            // If TeraWurfl class not found, see if we can load it from
            // configuration
            //
            if (!isset($config['terawurfl'])) {
                // No configuration
                // require_once 'Zend/Http/UserAgent/Features/Exception.php';
                throw new Zend_Http_UserAgent_Features_Exception('"TeraWurfl" configuration is not defined');
            }

            $config = $config['terawurfl'];

             if (empty($config['terawurfl_lib_dir'])) {
                // No lib_dir given
                // require_once 'Zend/Http/UserAgent/Features/Exception.php';
                throw new Zend_Http_UserAgent_Features_Exception('The "terawurfl_lib_dir" parameter is not defined');
            }

            // Include the Tera-WURFL file
            // require_once ($config['terawurfl_lib_dir'] . '/TeraWurfl.php');
        }


        // instantiate the Tera-WURFL object
        $wurflObj = new TeraWurfl();

        // Get the capabilities of the current client.
        $matched = $wurflObj->getDeviceCapabilitiesFromRequest(array_change_key_case($request, CASE_UPPER));

        return self::getAllCapabilities($wurflObj);
    }

    /***
     * Builds an array with all capabilities
     *
     * @param TeraWurfl $wurflObj TeraWurfl object
     */
    public static function getAllCapabilities(TeraWurfl $wurflObj)
    {

        foreach ($wurflObj->capabilities as $group) {
            if (!is_array($group)) {
                continue;
            }
            while (list ($key, $value) = each($group)) {
                if (is_bool($value)) {
                    // to have the same type than the official WURFL API
                    $features[$key] = ($value ? 'true' : 'false');
                } else {
                    $features[$key] = $value;
                }
            }
        }
        return $features;
    }
}
