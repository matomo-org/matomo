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
 * See installation instruction here : http://deviceatlas.com/licences
 * Download : http://deviceatlas.com/getAPI/php
 *
 * @package    Zend_Http
 * @subpackage UserAgent
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Http_UserAgent_Features_Adapter_DeviceAtlas implements Zend_Http_UserAgent_Features_Adapter
{
    /**
     * Get features from request
     *
     * @param  array $request $_SERVER variable
     * @return array
     */
    public static function getFromRequest($request, array $config)
    {
        if (!class_exists('Mobi_Mtld_DA_Api')) {
            if (!isset($config['deviceatlas'])) {
                // require_once 'Zend/Http/UserAgent/Features/Exception.php';
                throw new Zend_Http_UserAgent_Features_Exception('"DeviceAtlas" configuration is not defined');
            }
        }

        $config = $config['deviceatlas'];

        if (!class_exists('Mobi_Mtld_DA_Api')) {
            if (empty($config['deviceatlas_lib_dir'])) {
                // require_once 'Zend/Http/UserAgent/Features/Exception.php';
                throw new Zend_Http_UserAgent_Features_Exception('The "deviceatlas_lib_dir" parameter is not defined');
            }

            // Include the Device Atlas file from the specified lib_dir
            // require_once ($config['deviceatlas_lib_dir'] . '/Mobi/Mtld/DA/Api.php');
        }

        if (empty($config['deviceatlas_data'])) {
            // require_once 'Zend/Http/UserAgent/Features/Exception.php';
            throw new Zend_Http_UserAgent_Features_Exception('The "deviceatlas_data" parameter is not defined');
        }

        //load the device data-tree : e.g. 'json/DeviceAtlas.json
        $tree = Mobi_Mtld_DA_Api::getTreeFromFile($config['deviceatlas_data']);

        $properties = Mobi_Mtld_DA_Api::getProperties($tree, $request['http_user_agent']);

        return $properties;
    }
}
