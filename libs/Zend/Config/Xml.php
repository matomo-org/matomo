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
 */


/**
 * Zend_Config
 */
require_once 'Zend/Config.php';


/**
 * @category   Zend
 * @package    Zend_Config
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Config_Xml extends Zend_Config
{
    /**
     * Loads the section $section from the config file $filename for
     * access facilitated by nested object properties.
     *
     * Sections are defined in the XML as children of the root element.
     *
     * In order to extend another section, a section defines the "extends"
     * attribute having a value of the section name from which the extending
     * section inherits values.
     *
     * Note that the keys in $section will override any keys of the same
     * name in the sections that have been included via "extends".
     *
     * @param string $filename
     * @param mixed $section
     * @param boolean $allowModifications
     * @throws Zend_Config_Exception
     */
    public function __construct($filename, $section, $allowModifications = false)
    {
        if (empty($filename)) {
            throw new Zend_Config_Exception('Filename is not set');
        }

        $config = simplexml_load_file($filename);

        if (null === $section) {
            $dataArray = array();
            foreach ($config as $sectionName => $sectionData) {
                $dataArray[$sectionName] = $this->_processExtends($config, $sectionName);
            }
            parent::__construct($dataArray, $allowModifications);
        } elseif (is_array($section)) {
            $dataArray = array();
            foreach ($section as $sectionName) {
                if (!isset($config->$sectionName)) {
                    throw new Zend_Config_Exception("Section '$sectionName' cannot be found in $filename");
                }
                $dataArray = array_merge($this->_processExtends($config, $sectionName), $dataArray);
            }
            parent::__construct($dataArray, $allowModifications);
        } else {
            if (!isset($config->$section)) {
                throw new Zend_Config_Exception("Section '$section' cannot be found in $filename");
            }
            parent::__construct($this->_processExtends($config, $section), $allowModifications);
        }

        $this->_loadedSection = $section;
    }


    /**
     * Helper function to process each element in the section and handle
     * the "extends" inheritance attribute.
     *
     * @param SimpleXMLElement $element
     * @param string $section
     * @param array $config
     * @throws Zend_Config_Exception
     * @return array
     */
    protected function _processExtends($element, $section, $config = array())
    {
        if (!$element->$section) {
            throw new Zend_Config_Exception("Section '$section' cannot be found");
        }

        $thisSection = $element->$section;

        if (isset($thisSection['extends'])) {
            $extendedSection = (string) $thisSection['extends'];
            $this->_assertValidExtend($section, $extendedSection);
            $config = $this->_processExtends($element, $extendedSection, $config);
        }

        $config = $this->_arrayMergeRecursive($config, $this->_toArray($thisSection));

        return $config;
    }


    /**
     * Returns an associative and possibly multidimensional array from a SimpleXMLElement.
     *
     * @param SimpleXMLElement $xmlObject
     * @return array
     */
    protected function _toArray($xmlObject)
    {
        $config = array();
        foreach ($xmlObject->children() as $key => $value) {
            if ($value->children()) {
                $config[$key] = $this->_toArray($value);
            } else {
                $config[$key] = (string) $value;
            }
        }
        return $config;
    }

    /**
     * Merge two arrays recursively, overwriting keys of the same name name
     * in $array1 with the value in $array2.
     *
     * @param array $array1
     * @param array $array2
     * @return array
     */
    protected function _arrayMergeRecursive($array1, $array2)
    {
        if (is_array($array1) && is_array($array2)) {
            foreach ($array2 as $key => $value) {
                if (isset($array1[$key])) {
                    $array1[$key] = $this->_arrayMergeRecursive($array1[$key], $value);
                } else {
                    $array1[$key] = $value;
                }
            }
        } else {
            $array1 = $array2;
        }
        return $array1;
    }

}
