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
 * @package    Zend_Config
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */


/**
 * @see Zend_Config
 */
require_once 'Zend/Config.php';


/**
 * @category   Zend
 * @package    Zend_Config
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Config_Ini extends Zend_Config
{
    /**
     * String that separates nesting levels of configuration data identifiers
     *
     * @var string
     */
    protected $_nestSeparator = '.';

    /**
     * Loads the section $section from the config file $filename for
     * access facilitated by nested object properties.
     *
     * If any keys with $section are called "extends", then the section
     * pointed to by the "extends" is then included into the properties.
     * Note that the keys in $section will override any keys of the same
     * name in the sections that have been included via "extends".
     *
     * If any key includes a ".", then this will act as a separator to
     * create a sub-property.
     *
     * example ini file:
     *      [all]
     *      db.connection = database
     *      hostname = live
     *
     *      [staging]
     *      extends = all
     *      hostname = staging
     *
     * after calling $data = new Zend_Config_Ini($file, 'staging'); then
     *      $data->hostname === "staging"
     *      $data->db->connection === "database"
     *
     * The $config parameter may be provided as either a boolean or an array. If provided as a boolean, this sets the
     * $allowModifications option of Zend_Config. If provided as an array, there are two configuration directives that
     * may be set. For example:
     *
     * $config = array(
     *     'allowModifications' => false,
     *     'nestSeparator'      => '->'
     *      );
     *
     * @param  string        $filename
     * @param  mixed         $section
     * @param  boolean|array $config
     * @throws Zend_Config_Exception
     */
    public function __construct($filename, $section, $config = false)
    {
        if (empty($filename)) {
            throw new Zend_Config_Exception('Filename is not set');
        }

        $allowModifications = false;
        if (is_bool($config)) {
            $allowModifications = $config;
        } elseif (is_array($config)) {
            if (isset($config['allowModifications'])) {
                $allowModifications = (bool) $config['allowModifications'];
            }
            if (isset($config['nestSeparator'])) {
                $this->_nestSeparator = (string) $config['nestSeparator'];
            }
        }

        $iniArray = parse_ini_file($filename, true);
        $preProcessedArray = array();
        foreach ($iniArray as $key => $data)
        {
            $bits = explode(':', $key);
            $numberOfBits = count($bits);
            $thisSection = trim($bits[0]);
            switch (count($bits)) {
                case 1:
                    $preProcessedArray[$thisSection] = $data;
                    break;

                case 2:
                    $extendedSection = trim($bits[1]);
                    $preProcessedArray[$thisSection] = array_merge(array(';extends'=>$extendedSection), $data);
                    break;

                default:
                    throw new Zend_Config_Exception("Section '$thisSection' may not extend multiple sections in $filename");
            }
        }

        if (null === $section) {
            $dataArray = array();
            foreach ($preProcessedArray as $sectionName => $sectionData) {
                $dataArray[$sectionName] = $this->_processExtends($preProcessedArray, $sectionName);
            }
            parent::__construct($dataArray, $allowModifications);
        } elseif (is_array($section)) {
            $dataArray = array();
            foreach ($section as $sectionName) {
                if (!isset($preProcessedArray[$sectionName])) {
                    throw new Zend_Config_Exception("Section '$sectionName' cannot be found in $filename");
                }
                $dataArray = array_merge($this->_processExtends($preProcessedArray, $sectionName), $dataArray);

            }
            parent::__construct($dataArray, $allowModifications);
        } else {
            if (!isset($preProcessedArray[$section])) {
                throw new Zend_Config_Exception("Section '$section' cannot be found in $filename");
            }
            parent::__construct($this->_processExtends($preProcessedArray, $section), $allowModifications);
        }

        $this->_loadedSection = $section;
    }

    /**
     * Helper function to process each element in the section and handle
     * the "extends" inheritance keyword. Passes control to _processKey()
     * to handle the "dot" sub-property syntax in each key.
     *
     * @param array $iniArray
     * @param string $section
     * @param array $config
     * @throws Zend_Config_Exception
     * @return array
     */
    protected function _processExtends($iniArray, $section, $config = array())
    {
        $thisSection = $iniArray[$section];

        foreach ($thisSection as $key => $value) {
            if (strtolower($key) == ';extends') {
                if (isset($iniArray[$value])) {
                    $this->_assertValidExtend($section, $value);
                    $config = $this->_processExtends($iniArray, $value, $config);
                } else {
                    throw new Zend_Config_Exception("Section '$section' cannot be found");
                }
            } else {
                $config = $this->_processKey($config, $key, $value);
            }
        }
        return $config;
    }

    /**
     * Assign the key's value to the property list. Handle the "dot"
     * notation for sub-properties by passing control to
     * processLevelsInKey().
     *
     * @param array $config
     * @param string $key
     * @param string $value
     * @throws Zend_Config_Exception
     * @return array
     */
    protected function _processKey($config, $key, $value)
    {
        if (strpos($key, $this->_nestSeparator) !== false) {
            $pieces = explode($this->_nestSeparator, $key, 2);
            if (strlen($pieces[0]) && strlen($pieces[1])) {
                if (!isset($config[$pieces[0]])) {
                    $config[$pieces[0]] = array();
                } elseif (!is_array($config[$pieces[0]])) {
                    throw new Zend_Config_Exception("Cannot create sub-key for '{$pieces[0]}' as key already exists");
                }
                $config[$pieces[0]] = $this->_processKey($config[$pieces[0]], $pieces[1], $value);
            } else {
                throw new Zend_Config_Exception("Invalid key '$key'");
            }
        } else {
            $config[$key] = $value;
        }
        return $config;
    }

}
