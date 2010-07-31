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
 * @package    Zend_Db
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Db.php 20096 2010-01-06 02:05:09Z bkarwin $
 */


/**
 * Class for connecting to SQL databases and performing common operations.
 *
 * @category   Zend
 * @package    Zend_Db
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Db
{

    /**
     * Use the PROFILER constant in the config of a Zend_Db_Adapter.
     */
    const PROFILER = 'profiler';

    /**
     * Use the CASE_FOLDING constant in the config of a Zend_Db_Adapter.
     */
    const CASE_FOLDING = 'caseFolding';

    /**
     * Use the AUTO_QUOTE_IDENTIFIERS constant in the config of a Zend_Db_Adapter.
     */
    const AUTO_QUOTE_IDENTIFIERS = 'autoQuoteIdentifiers';

    /**
     * Use the ALLOW_SERIALIZATION constant in the config of a Zend_Db_Adapter.
     */
    const ALLOW_SERIALIZATION = 'allowSerialization';

    /**
     * Use the AUTO_RECONNECT_ON_UNSERIALIZE constant in the config of a Zend_Db_Adapter.
     */
    const AUTO_RECONNECT_ON_UNSERIALIZE = 'autoReconnectOnUnserialize';

    /**
     * Use the INT_TYPE, BIGINT_TYPE, and FLOAT_TYPE with the quote() method.
     */
    const INT_TYPE    = 0;
    const BIGINT_TYPE = 1;
    const FLOAT_TYPE  = 2;

    /**
     * PDO constant values discovered by this script result:
     *
     * $list = array(
     *    'PARAM_BOOL', 'PARAM_NULL', 'PARAM_INT', 'PARAM_STR', 'PARAM_LOB',
     *    'PARAM_STMT', 'PARAM_INPUT_OUTPUT', 'FETCH_LAZY', 'FETCH_ASSOC',
     *    'FETCH_NUM', 'FETCH_BOTH', 'FETCH_OBJ', 'FETCH_BOUND',
     *    'FETCH_COLUMN', 'FETCH_CLASS', 'FETCH_INTO', 'FETCH_FUNC',
     *    'FETCH_GROUP', 'FETCH_UNIQUE', 'FETCH_CLASSTYPE', 'FETCH_SERIALIZE',
     *    'FETCH_NAMED', 'ATTR_AUTOCOMMIT', 'ATTR_PREFETCH', 'ATTR_TIMEOUT',
     *    'ATTR_ERRMODE', 'ATTR_SERVER_VERSION', 'ATTR_CLIENT_VERSION',
     *    'ATTR_SERVER_INFO', 'ATTR_CONNECTION_STATUS', 'ATTR_CASE',
     *    'ATTR_CURSOR_NAME', 'ATTR_CURSOR', 'ATTR_ORACLE_NULLS',
     *    'ATTR_PERSISTENT', 'ATTR_STATEMENT_CLASS', 'ATTR_FETCH_TABLE_NAMES',
     *    'ATTR_FETCH_CATALOG_NAMES', 'ATTR_DRIVER_NAME',
     *    'ATTR_STRINGIFY_FETCHES', 'ATTR_MAX_COLUMN_LEN', 'ERRMODE_SILENT',
     *    'ERRMODE_WARNING', 'ERRMODE_EXCEPTION', 'CASE_NATURAL',
     *    'CASE_LOWER', 'CASE_UPPER', 'NULL_NATURAL', 'NULL_EMPTY_STRING',
     *    'NULL_TO_STRING', 'ERR_NONE', 'FETCH_ORI_NEXT',
     *    'FETCH_ORI_PRIOR', 'FETCH_ORI_FIRST', 'FETCH_ORI_LAST',
     *    'FETCH_ORI_ABS', 'FETCH_ORI_REL', 'CURSOR_FWDONLY', 'CURSOR_SCROLL',
     *    'ERR_CANT_MAP', 'ERR_SYNTAX', 'ERR_CONSTRAINT', 'ERR_NOT_FOUND',
     *    'ERR_ALREADY_EXISTS', 'ERR_NOT_IMPLEMENTED', 'ERR_MISMATCH',
     *    'ERR_TRUNCATED', 'ERR_DISCONNECTED', 'ERR_NO_PERM',
     * );
     *
     * $const = array();
     * foreach ($list as $name) {
     *    $const[$name] = constant("PDO::$name");
     * }
     * var_export($const);
     */
    const ATTR_AUTOCOMMIT = 0;
    const ATTR_CASE = 8;
    const ATTR_CLIENT_VERSION = 5;
    const ATTR_CONNECTION_STATUS = 7;
    const ATTR_CURSOR = 10;
    const ATTR_CURSOR_NAME = 9;
    const ATTR_DRIVER_NAME = 16;
    const ATTR_ERRMODE = 3;
    const ATTR_FETCH_CATALOG_NAMES = 15;
    const ATTR_FETCH_TABLE_NAMES = 14;
    const ATTR_MAX_COLUMN_LEN = 18;
    const ATTR_ORACLE_NULLS = 11;
    const ATTR_PERSISTENT = 12;
    const ATTR_PREFETCH = 1;
    const ATTR_SERVER_INFO = 6;
    const ATTR_SERVER_VERSION = 4;
    const ATTR_STATEMENT_CLASS = 13;
    const ATTR_STRINGIFY_FETCHES = 17;
    const ATTR_TIMEOUT = 2;
    const CASE_LOWER = 2;
    const CASE_NATURAL = 0;
    const CASE_UPPER = 1;
    const CURSOR_FWDONLY = 0;
    const CURSOR_SCROLL = 1;
    const ERR_ALREADY_EXISTS = NULL;
    const ERR_CANT_MAP = NULL;
    const ERR_CONSTRAINT = NULL;
    const ERR_DISCONNECTED = NULL;
    const ERR_MISMATCH = NULL;
    const ERR_NO_PERM = NULL;
    const ERR_NONE = '00000';
    const ERR_NOT_FOUND = NULL;
    const ERR_NOT_IMPLEMENTED = NULL;
    const ERR_SYNTAX = NULL;
    const ERR_TRUNCATED = NULL;
    const ERRMODE_EXCEPTION = 2;
    const ERRMODE_SILENT = 0;
    const ERRMODE_WARNING = 1;
    const FETCH_ASSOC = 2;
    const FETCH_BOTH = 4;
    const FETCH_BOUND = 6;
    const FETCH_CLASS = 8;
    const FETCH_CLASSTYPE = 262144;
    const FETCH_COLUMN = 7;
    const FETCH_FUNC = 10;
    const FETCH_GROUP = 65536;
    const FETCH_INTO = 9;
    const FETCH_LAZY = 1;
    const FETCH_NAMED = 11;
    const FETCH_NUM = 3;
    const FETCH_OBJ = 5;
    const FETCH_ORI_ABS = 4;
    const FETCH_ORI_FIRST = 2;
    const FETCH_ORI_LAST = 3;
    const FETCH_ORI_NEXT = 0;
    const FETCH_ORI_PRIOR = 1;
    const FETCH_ORI_REL = 5;
    const FETCH_SERIALIZE = 524288;
    const FETCH_UNIQUE = 196608;
    const NULL_EMPTY_STRING = 1;
    const NULL_NATURAL = 0;
    const NULL_TO_STRING = NULL;
    const PARAM_BOOL = 5;
    const PARAM_INPUT_OUTPUT = -2147483648;
    const PARAM_INT = 1;
    const PARAM_LOB = 3;
    const PARAM_NULL = 0;
    const PARAM_STMT = 4;
    const PARAM_STR = 2;

    /**
     * Factory for Zend_Db_Adapter_Abstract classes.
     *
     * First argument may be a string containing the base of the adapter class
     * name, e.g. 'Mysqli' corresponds to class Zend_Db_Adapter_Mysqli.  This
     * name is currently case-insensitive, but is not ideal to rely on this behavior.
     * If your class is named 'My_Company_Pdo_Mysql', where 'My_Company' is the namespace
     * and 'Pdo_Mysql' is the adapter name, it is best to use the name exactly as it
     * is defined in the class.  This will ensure proper use of the factory API.
     *
     * First argument may alternatively be an object of type Zend_Config.
     * The adapter class base name is read from the 'adapter' property.
     * The adapter config parameters are read from the 'params' property.
     *
     * Second argument is optional and may be an associative array of key-value
     * pairs.  This is used as the argument to the adapter constructor.
     *
     * If the first argument is of type Zend_Config, it is assumed to contain
     * all parameters, and the second argument is ignored.
     *
     * @param  mixed $adapter String name of base adapter class, or Zend_Config object.
     * @param  mixed $config  OPTIONAL; an array or Zend_Config object with adapter parameters.
     * @return Zend_Db_Adapter_Abstract
     * @throws Zend_Db_Exception
     */
    public static function factory($adapter, $config = array())
    {
        if ($config instanceof Zend_Config) {
            $config = $config->toArray();
        }

        /*
         * Convert Zend_Config argument to plain string
         * adapter name and separate config object.
         */
        if ($adapter instanceof Zend_Config) {
            if (isset($adapter->params)) {
                $config = $adapter->params->toArray();
            }
            if (isset($adapter->adapter)) {
                $adapter = (string) $adapter->adapter;
            } else {
                $adapter = null;
            }
        }

        /*
         * Verify that adapter parameters are in an array.
         */
        if (!is_array($config)) {
            /**
             * @see Zend_Db_Exception
             */
            // require_once 'Zend/Db/Exception.php';
            throw new Zend_Db_Exception('Adapter parameters must be in an array or a Zend_Config object');
        }

        /*
         * Verify that an adapter name has been specified.
         */
        if (!is_string($adapter) || empty($adapter)) {
            /**
             * @see Zend_Db_Exception
             */
            // require_once 'Zend/Db/Exception.php';
            throw new Zend_Db_Exception('Adapter name must be specified in a string');
        }

        /*
         * Form full adapter class name
         */
        $adapterNamespace = 'Zend_Db_Adapter';
        if (isset($config['adapterNamespace'])) {
            if ($config['adapterNamespace'] != '') {
                $adapterNamespace = $config['adapterNamespace'];
            }
            unset($config['adapterNamespace']);
        }

        // Adapter no longer normalized- see http://framework.zend.com/issues/browse/ZF-5606
        $adapterName = $adapterNamespace . '_';
        $adapterName .= str_replace(' ', '_', ucwords(str_replace('_', ' ', strtolower($adapter))));

        /*
         * Load the adapter class.  This throws an exception
         * if the specified class cannot be loaded.
         */
        if (!class_exists($adapterName)) {
            // require_once 'Zend/Loader.php';
            Zend_Loader::loadClass($adapterName);
        }

        /*
         * Create an instance of the adapter class.
         * Pass the config to the adapter class constructor.
         */
        $dbAdapter = new $adapterName($config);

        /*
         * Verify that the object created is a descendent of the abstract adapter type.
         */
        if (! $dbAdapter instanceof Zend_Db_Adapter_Abstract) {
            /**
             * @see Zend_Db_Exception
             */
            // require_once 'Zend/Db/Exception.php';
            throw new Zend_Db_Exception("Adapter class '$adapterName' does not extend Zend_Db_Adapter_Abstract");
        }

        return $dbAdapter;
    }

}
