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
 * @category  Zend
 * @package   Zend_Config
 * @copyright Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd     New BSD License
 * @version   $Id: Yaml.php 24092 2011-05-31 02:43:28Z adamlundrigan $
 */

/**
 * @see Zend_Config
 */
// require_once 'Zend/Config.php';

/**
 * YAML Adapter for Zend_Config
 *
 * @category  Zend
 * @package   Zend_Config
 * @copyright Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Config_Yaml extends Zend_Config
{
    /**
     * Attribute name that indicates what section a config extends from
     */
    const EXTENDS_NAME = "_extends";

    /**
     * Whether to skip extends or not
     *
     * @var boolean
     */
    protected $_skipExtends = false;

    /**
     * What to call when we need to decode some YAML?
     *
     * @var callable
     */
    protected $_yamlDecoder = array(__CLASS__, 'decode');

    /**
     * Whether or not to ignore constants in parsed YAML
     * @var bool
     */
    protected static $_ignoreConstants = false;

    /**
     * Indicate whether parser should ignore constants or not
     *
     * @param  bool $flag
     * @return void
     */
    public static function setIgnoreConstants($flag)
    {
        self::$_ignoreConstants = (bool) $flag;
    }

    /**
     * Whether parser should ignore constants or not
     *
     * @return bool
     */
    public static function ignoreConstants()
    {
        return self::$_ignoreConstants;
    }

    /**
     * Get callback for decoding YAML
     *
     * @return callable
     */
    public function getYamlDecoder()
    {
        return $this->_yamlDecoder;
    }

    /**
     * Set callback for decoding YAML
     *
     * @param  callable $yamlDecoder the decoder to set
     * @return Zend_Config_Yaml
     */
    public function setYamlDecoder($yamlDecoder)
    {
        if (!is_callable($yamlDecoder)) {
            // require_once 'Zend/Config/Exception.php';
            throw new Zend_Config_Exception('Invalid parameter to setYamlDecoder() - must be callable');
        }

        $this->_yamlDecoder = $yamlDecoder;
        return $this;
    }

    /**
     * Loads the section $section from the config file encoded as YAML
     *
     * Sections are defined as properties of the main object
     *
     * In order to extend another section, a section defines the "_extends"
     * property having a value of the section name from which the extending
     * section inherits values.
     *
     * Note that the keys in $section will override any keys of the same
     * name in the sections that have been included via "_extends".
     *
     * Options may include:
     * - allow_modifications: whether or not the config object is mutable
     * - skip_extends: whether or not to skip processing of parent configuration
     * - yaml_decoder: a callback to use to decode the Yaml source
     *
     * @param  string        $yaml     YAML file to process
     * @param  mixed         $section  Section to process
     * @param  array|boolean $options 
     */
    public function __construct($yaml, $section = null, $options = false)
    {
        if (empty($yaml)) {
            // require_once 'Zend/Config/Exception.php';
            throw new Zend_Config_Exception('Filename is not set');
        }

        $ignoreConstants    = $staticIgnoreConstants = self::ignoreConstants();
        $allowModifications = false;
        if (is_bool($options)) {
            $allowModifications = $options;
        } elseif (is_array($options)) {
            foreach ($options as $key => $value) {
                switch (strtolower($key)) {
                    case 'allow_modifications':
                    case 'allowmodifications':
                        $allowModifications = (bool) $value;
                        break;
                    case 'skip_extends':
                    case 'skipextends':
                        $this->_skipExtends = (bool) $value;
                        break;
                    case 'ignore_constants':
                    case 'ignoreconstants':
                        $ignoreConstants = (bool) $value;
                        break;
                    case 'yaml_decoder':
                    case 'yamldecoder':
                        $this->setYamlDecoder($value);
                        break;
                    default:
                        break;
                }
            }
        }

        // Suppress warnings and errors while loading file
        set_error_handler(array($this, '_loadFileErrorHandler'));
        $yaml = file_get_contents($yaml);
        restore_error_handler();

        // Check if there was a error while loading file
        if ($this->_loadFileErrorStr !== null) {
            // require_once 'Zend/Config/Exception.php';
            throw new Zend_Config_Exception($this->_loadFileErrorStr);
        }

        // Override static value for ignore_constants if provided in $options
        self::setIgnoreConstants($ignoreConstants);

        // Parse YAML
        $config = call_user_func($this->getYamlDecoder(), $yaml);

        // Reset original static state of ignore_constants
        self::setIgnoreConstants($staticIgnoreConstants);

        if (null === $config) {
            // decode failed
            // require_once 'Zend/Config/Exception.php';
            throw new Zend_Config_Exception("Error parsing YAML data");
        }

        if (null === $section) {
            $dataArray = array();
            foreach ($config as $sectionName => $sectionData) {
                $dataArray[$sectionName] = $this->_processExtends($config, $sectionName);
            }
            parent::__construct($dataArray, $allowModifications);
        } elseif (is_array($section)) {
            $dataArray = array();
            foreach ($section as $sectionName) {
                if (!isset($config[$sectionName])) {
                    // require_once 'Zend/Config/Exception.php';
                    throw new Zend_Config_Exception(sprintf('Section "%s" cannot be found', $section));
                }

                $dataArray = array_merge($this->_processExtends($config, $sectionName), $dataArray);
            }
            parent::__construct($dataArray, $allowModifications);
        } else {
            if (!isset($config[$section])) {
                // require_once 'Zend/Config/Exception.php';
                throw new Zend_Config_Exception(sprintf('Section "%s" cannot be found', $section));
            }

            $dataArray = $this->_processExtends($config, $section);
            if (!is_array($dataArray)) {
                // Section in the yaml data contains just one top level string
                $dataArray = array($section => $dataArray);
            }
            parent::__construct($dataArray, $allowModifications);
        }

        $this->_loadedSection = $section;
    }

    /**
     * Helper function to process each element in the section and handle
     * the "_extends" inheritance attribute.
     *
     * @param  array            $data Data array to process
     * @param  string           $section Section to process
     * @param  array            $config  Configuration which was parsed yet
     * @return array
     * @throws Zend_Config_Exception When $section cannot be found
     */
    protected function _processExtends(array $data, $section, array $config = array())
    {
        if (!isset($data[$section])) {
            // require_once 'Zend/Config/Exception.php';
            throw new Zend_Config_Exception(sprintf('Section "%s" cannot be found', $section));
        }

        $thisSection  = $data[$section];

        if (is_array($thisSection) && isset($thisSection[self::EXTENDS_NAME])) {
            $this->_assertValidExtend($section, $thisSection[self::EXTENDS_NAME]);

            if (!$this->_skipExtends) {
                $config = $this->_processExtends($data, $thisSection[self::EXTENDS_NAME], $config);
            }
            unset($thisSection[self::EXTENDS_NAME]);
        }

        $config = $this->_arrayMergeRecursive($config, $thisSection);

        return $config;
    }

    /**
     * Very dumb YAML parser
     *
     * Until we have Zend_Yaml...
     *
     * @param  string $yaml YAML source
     * @return array Decoded data
     */
    public static function decode($yaml)
    {
        $lines = explode("\n", $yaml);
        reset($lines);
        return self::_decodeYaml(0, $lines);
    }

    /**
     * Service function to decode YAML
     *
     * @param  int $currentIndent Current indent level
     * @param  array $lines  YAML lines
     * @return array|string
     */
    protected static function _decodeYaml($currentIndent, &$lines)
    {
        $config   = array();
        $inIndent = false;
        while (list($n, $line) = each($lines)) {
            $lineno = $n + 1;
            
            $line = rtrim(preg_replace("/#.*$/", "", $line));
            if (strlen($line) == 0) {
                continue;
            }

            $indent = strspn($line, " ");

            // line without the spaces
            $line = trim($line);
            if (strlen($line) == 0) {
                continue;
            }

            if ($indent < $currentIndent) {
                // this level is done
                prev($lines);
                return $config;
            }

            if (!$inIndent) {
                $currentIndent = $indent;
                $inIndent      = true;
            }

            if (preg_match("/(\w+):\s*(.*)/", $line, $m)) {
                // key: value
                if (strlen($m[2])) {
                    // simple key: value
                    $value = rtrim(preg_replace("/#.*$/", "", $m[2]));
                    // Check for booleans and constants
                    if (preg_match('/^(t(rue)?|on|y(es)?)$/i', $value)) {
                        $value = true;
                    } elseif (preg_match('/^(f(alse)?|off|n(o)?)$/i', $value)) {
                        $value = false;
                    } elseif (!self::$_ignoreConstants) {
                        // test for constants
                        $value = self::_replaceConstants($value);
                    }
                } else {
                    // key: and then values on new lines
                    $value = self::_decodeYaml($currentIndent + 1, $lines);
                    if (is_array($value) && !count($value)) {
                        $value = "";
                    }
                }
                $config[$m[1]] = $value;
            } elseif ($line[0] == "-") {
                // item in the list:
                // - FOO
                if (strlen($line) > 2) {
                    $config[] = substr($line, 2);
                } else {
                    $config[] = self::_decodeYaml($currentIndent + 1, $lines);
                }
            } else {
                // require_once 'Zend/Config/Exception.php';
                throw new Zend_Config_Exception(sprintf(
                    'Error parsing YAML at line %d - unsupported syntax: "%s"',
                    $lineno, $line
                ));
            }
        }
        return $config;
    }

    /**
     * Replace any constants referenced in a string with their values
     *
     * @param  string $value
     * @return string
     */
    protected static function _replaceConstants($value)
    {
        foreach (self::_getConstants() as $constant) {
            if (strstr($value, $constant)) {
                $value = str_replace($constant, constant($constant), $value);
            }
        }
        return $value;
    }

    /**
     * Get (reverse) sorted list of defined constant names
     *
     * @return array
     */
    protected static function _getConstants()
    {
        $constants = array_keys(get_defined_constants());
        rsort($constants, SORT_STRING);
        return $constants;
    }
}
