<?PHP
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * XML_Unserializer
 *
 * Parses any XML document into PHP data structures.
 *
 * PHP versions 4 and 5
 *
 * LICENSE: This source file is subject to version 3.0 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_0.txt.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @category   XML
 * @package    XML_Serializer
 * @author     Stephan Schmidt <schst@php.net>
 * @copyright  1997-2005 The PHP Group
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version    CVS: $Id: Unserializer.php,v 1.39 2005/09/28 11:19:56 schst Exp $
 * @link       http://pear.php.net/package/XML_Serializer
 * @see        XML_Unserializer
 */

/**
 * uses PEAR error managemt
 */
require_once 'PEAR.php';

/**
 * uses XML_Parser to unserialize document
 */
require_once 'XML/Parser.php';

/**
 * option: Convert nested tags to array or object
 *
 * Possible values:
 * - array
 * - object
 * - associative array to define this option per tag name
 */
define('XML_UNSERIALIZER_OPTION_COMPLEXTYPE', 'complexType');

/**
 * option: Name of the attribute that stores the original key
 *
 * Possible values:
 * - any string
 */
define('XML_UNSERIALIZER_OPTION_ATTRIBUTE_KEY', 'keyAttribute');

/**
 * option: Name of the attribute that stores the type
 *
 * Possible values:
 * - any string
 */
define('XML_UNSERIALIZER_OPTION_ATTRIBUTE_TYPE', 'typeAttribute');

/**
 * option: Name of the attribute that stores the class name
 *
 * Possible values:
 * - any string
 */
define('XML_UNSERIALIZER_OPTION_ATTRIBUTE_CLASS', 'classAttribute');

/**
 * option: Whether to use the tag name as a class name
 *
 * Possible values:
 * - true or false
 */
define('XML_UNSERIALIZER_OPTION_TAG_AS_CLASSNAME', 'tagAsClass');

/**
 * option: Name of the default class
 *
 * Possible values:
 * - any string
 */
define('XML_UNSERIALIZER_OPTION_DEFAULT_CLASS', 'defaultClass');

/**
 * option: Whether to parse attributes
 *
 * Possible values:
 * - true or false
 */
define('XML_UNSERIALIZER_OPTION_ATTRIBUTES_PARSE', 'parseAttributes');

/**
 * option: Key of the array to store attributes (if any)
 *
 * Possible values:
 * - any string
 * - false (disabled)
 */
define('XML_UNSERIALIZER_OPTION_ATTRIBUTES_ARRAYKEY', 'attributesArray');

/**
 * option: string to prepend attribute name (if any)
 *
 * Possible values:
 * - any string
 * - false (disabled)
 */
define('XML_UNSERIALIZER_OPTION_ATTRIBUTES_PREPEND', 'prependAttributes');

/**
 * option: key to store the content, if XML_UNSERIALIZER_OPTION_ATTRIBUTES_PARSE is used
 *
 * Possible values:
 * - any string
 */
define('XML_UNSERIALIZER_OPTION_CONTENT_KEY', 'contentName');

/**
 * option: map tag names
 *
 * Possible values:
 * - associative array
 */
define('XML_UNSERIALIZER_OPTION_TAG_MAP', 'tagMap');

/**
 * option: list of tags that will always be enumerated
 *
 * Possible values:
 * - indexed array
 */
define('XML_UNSERIALIZER_OPTION_FORCE_ENUM', 'forceEnum');

/**
 * option: Encoding of the XML document
 *
 * Possible values:
 * - UTF-8
 * - ISO-8859-1
 */
define('XML_UNSERIALIZER_OPTION_ENCODING_SOURCE', 'encoding');

/**
 * option: Desired target encoding of the data
 *
 * Possible values:
 * - UTF-8
 * - ISO-8859-1
 */
define('XML_UNSERIALIZER_OPTION_ENCODING_TARGET', 'targetEncoding');

/**
 * option: Callback that will be applied to textual data
 *
 * Possible values:
 * - any valid PHP callback
 */
define('XML_UNSERIALIZER_OPTION_DECODE_FUNC', 'decodeFunction');

/**
 * option: whether to return the result of the unserialization from unserialize()
 *
 * Possible values:
 * - true
 * - false (default)
 */
define('XML_UNSERIALIZER_OPTION_RETURN_RESULT', 'returnResult');

/**
 * option: set the whitespace behaviour
 *
 * Possible values:
 * - XML_UNSERIALIZER_WHITESPACE_KEEP
 * - XML_UNSERIALIZER_WHITESPACE_TRIM
 * - XML_UNSERIALIZER_WHITESPACE_NORMALIZE
 */
define('XML_UNSERIALIZER_OPTION_WHITESPACE', 'whitespace');

/**
 * Keep all whitespace
 */
define('XML_UNSERIALIZER_WHITESPACE_KEEP', 'keep');

/**
 * remove whitespace from start and end of the data
 */
define('XML_UNSERIALIZER_WHITESPACE_TRIM', 'trim');

/**
 * normalize whitespace
 */
define('XML_UNSERIALIZER_WHITESPACE_NORMALIZE', 'normalize');

/**
 * option: whether to ovverride all options that have been set before
 *
 * Possible values:
 * - true
 * - false (default)
 */
define('XML_UNSERIALIZER_OPTION_OVERRIDE_OPTIONS', 'overrideOptions');

/**
 * option: list of tags, that will not be used as keys
 */
define('XML_UNSERIALIZER_OPTION_IGNORE_KEYS', 'ignoreKeys');

/**
 * option: whether to use type guessing for scalar values
 */
define('XML_UNSERIALIZER_OPTION_GUESS_TYPES', 'guessTypes');

/**
 * error code for no serialization done
 */
define('XML_UNSERIALIZER_ERROR_NO_UNSERIALIZATION', 151);

/**
 * XML_Unserializer
 *
 * class to unserialize XML documents that have been created with
 * XML_Serializer. To unserialize an XML document you have to add
 * type hints to the XML_Serializer options.
 *
 * If no type hints are available, XML_Unserializer will guess how
 * the tags should be treated, that means complex structures will be
 * arrays and tags with only CData in them will be strings.
 *
 * <code>
 * require_once 'XML/Unserializer.php';
 *
 * //  be careful to always use the ampersand in front of the new operator
 * $unserializer = &new XML_Unserializer();
 *
 * $unserializer->unserialize($xml);
 *
 * $data = $unserializer->getUnserializedData();
 * <code>
 *
 *
 * @category   XML
 * @package    XML_Serializer
 * @author     Stephan Schmidt <schst@php.net>
 * @copyright  1997-2005 The PHP Group
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version    Release: @package_version@
 * @link       http://pear.php.net/package/XML_Serializer
 * @see        XML_Serializer
 */
class XML_Unserializer extends PEAR
{
   /**
    * list of all available options
    *
    * @access private
    * @var    array
    */
    var $_knownOptions = array(
                                XML_UNSERIALIZER_OPTION_COMPLEXTYPE,
                                XML_UNSERIALIZER_OPTION_ATTRIBUTE_KEY,
                                XML_UNSERIALIZER_OPTION_ATTRIBUTE_TYPE,
                                XML_UNSERIALIZER_OPTION_ATTRIBUTE_CLASS,
                                XML_UNSERIALIZER_OPTION_TAG_AS_CLASSNAME,
                                XML_UNSERIALIZER_OPTION_DEFAULT_CLASS,
                                XML_UNSERIALIZER_OPTION_ATTRIBUTES_PARSE,
                                XML_UNSERIALIZER_OPTION_ATTRIBUTES_ARRAYKEY,
                                XML_UNSERIALIZER_OPTION_ATTRIBUTES_PREPEND,
                                XML_UNSERIALIZER_OPTION_CONTENT_KEY,
                                XML_UNSERIALIZER_OPTION_TAG_MAP,
                                XML_UNSERIALIZER_OPTION_FORCE_ENUM,
                                XML_UNSERIALIZER_OPTION_ENCODING_SOURCE,
                                XML_UNSERIALIZER_OPTION_ENCODING_TARGET,
                                XML_UNSERIALIZER_OPTION_DECODE_FUNC,
                                XML_UNSERIALIZER_OPTION_RETURN_RESULT,
                                XML_UNSERIALIZER_OPTION_WHITESPACE,
                                XML_UNSERIALIZER_OPTION_IGNORE_KEYS,
                                XML_UNSERIALIZER_OPTION_GUESS_TYPES
                              );
   /**
    * default options for the serialization
    *
    * @access private
    * @var    array
    */
    var $_defaultOptions = array(
                         XML_UNSERIALIZER_OPTION_COMPLEXTYPE         => 'array',                // complex types will be converted to arrays, if no type hint is given
                         XML_UNSERIALIZER_OPTION_ATTRIBUTE_KEY       => '_originalKey',         // get array key/property name from this attribute
                         XML_UNSERIALIZER_OPTION_ATTRIBUTE_TYPE      => '_type',                // get type from this attribute
                         XML_UNSERIALIZER_OPTION_ATTRIBUTE_CLASS     => '_class',               // get class from this attribute (if not given, use tag name)
                         XML_UNSERIALIZER_OPTION_TAG_AS_CLASSNAME    => true,                   // use the tagname as the classname
                         XML_UNSERIALIZER_OPTION_DEFAULT_CLASS       => 'stdClass',             // name of the class that is used to create objects
                         XML_UNSERIALIZER_OPTION_ATTRIBUTES_PARSE    => false,                  // parse the attributes of the tag into an array
                         XML_UNSERIALIZER_OPTION_ATTRIBUTES_ARRAYKEY => false,                  // parse them into sperate array (specify name of array here)
                         XML_UNSERIALIZER_OPTION_ATTRIBUTES_PREPEND  => '',                     // prepend attribute names with this string
                         XML_UNSERIALIZER_OPTION_CONTENT_KEY         => '_content',             // put cdata found in a tag that has been converted to a complex type in this key
                         XML_UNSERIALIZER_OPTION_TAG_MAP             => array(),                // use this to map tagnames
                         XML_UNSERIALIZER_OPTION_FORCE_ENUM          => array(),                // these tags will always be an indexed array
                         XML_UNSERIALIZER_OPTION_ENCODING_SOURCE     => null,                   // specify the encoding character of the document to parse
                         XML_UNSERIALIZER_OPTION_ENCODING_TARGET     => null,                   // specify the target encoding
                         XML_UNSERIALIZER_OPTION_DECODE_FUNC         => null,                   // function used to decode data
                         XML_UNSERIALIZER_OPTION_RETURN_RESULT       => false,                  // unserialize() returns the result of the unserialization instead of true
                         XML_UNSERIALIZER_OPTION_WHITESPACE          => XML_UNSERIALIZER_WHITESPACE_TRIM, // remove whitespace around data
                         XML_UNSERIALIZER_OPTION_IGNORE_KEYS         => array(),                // List of tags that will automatically be added to the parent, instead of adding a new key
                         XML_UNSERIALIZER_OPTION_GUESS_TYPES         => false                   // Whether to use type guessing
                        );

   /**
    * current options for the serialization
    *
    * @access public
    * @var    array
    */
    var $options = array();

   /**
    * unserialized data
    *
    * @access private
    * @var    string
    */
    var $_unserializedData = null;

   /**
    * name of the root tag
    *
    * @access private
    * @var    string
    */
    var $_root = null;

   /**
    * stack for all data that is found
    *
    * @access private
    * @var    array
    */
    var $_dataStack  =   array();

   /**
    * stack for all values that are generated
    *
    * @access private
    * @var    array
    */
    var $_valStack  =   array();

   /**
    * current tag depth
    *
    * @access private
    * @var    int
    */
    var $_depth = 0;

   /**
    * XML_Parser instance
    *
    * @access   private
    * @var      object XML_Parser
    */
    var $_parser = null;
    
   /**
    * constructor
    *
    * @access   public
    * @param    mixed   $options    array containing options for the unserialization
    */
    function XML_Unserializer($options = null)
    {
        if (is_array($options)) {
            $this->options = array_merge($this->_defaultOptions, $options);
        } else {
            $this->options = $this->_defaultOptions;
        }
    }

   /**
    * return API version
    *
    * @access   public
    * @static
    * @return   string  $version API version
    */
    function apiVersion()
    {
        return '@package_version@';
    }

   /**
    * reset all options to default options
    *
    * @access   public
    * @see      setOption(), XML_Unserializer(), setOptions()
    */
    function resetOptions()
    {
        $this->options = $this->_defaultOptions;
    }

   /**
    * set an option
    *
    * You can use this method if you do not want to set all options in the constructor
    *
    * @access   public
    * @see      resetOption(), XML_Unserializer(), setOptions()
    */
    function setOption($name, $value)
    {
        $this->options[$name] = $value;
    }

   /**
    * sets several options at once
    *
    * You can use this method if you do not want to set all options in the constructor
    *
    * @access   public
    * @see      resetOption(), XML_Unserializer(), setOption()
    */
    function setOptions($options)
    {
        $this->options = array_merge($this->options, $options);
    }

   /**
    * unserialize data
    *
    * @access   public
    * @param    mixed    $data     data to unserialize (string, filename or resource)
    * @param    boolean  $isFile   data should be treated as a file
    * @param    array    $options  options that will override the global options for this call
    * @return   boolean  $success
    */
    function unserialize($data, $isFile = false, $options = null)
    {
        $this->_unserializedData = null;
        $this->_root = null;

        // if options have been specified, use them instead
        // of the previously defined ones
        if (is_array($options)) {
            $optionsBak = $this->options;
            if (isset($options[XML_UNSERIALIZER_OPTION_OVERRIDE_OPTIONS]) && $options[XML_UNSERIALIZER_OPTION_OVERRIDE_OPTIONS] == true) {
                $this->options = array_merge($this->_defaultOptions, $options);
            } else {
                $this->options = array_merge($this->options, $options);
            }
        } else {
            $optionsBak = null;
        }

        $this->_valStack = array();
        $this->_dataStack = array();
        $this->_depth = 0;

        $this->_createParser();
        
        if (is_string($data)) {
            if ($isFile) {
                $result = $this->_parser->setInputFile($data);
                if (PEAR::isError($result)) {
                    return $result;
                }
                $result = $this->_parser->parse();
            } else {
                $result = $this->_parser->parseString($data,true);
            }
        } else {
           $this->_parser->setInput($data);
           $result = $this->_parser->parse();
        }

        if ($this->options[XML_UNSERIALIZER_OPTION_RETURN_RESULT] === true) {
        	$return = $this->_unserializedData;
        } else {
            $return = true;
        }

        if ($optionsBak !== null) {
            $this->options = $optionsBak;
        }

        if (PEAR::isError($result)) {
            return $result;
        }

        return $return;
    }

   /**
    * get the result of the serialization
    *
    * @access public
    * @return string  $serializedData
    */
    function getUnserializedData()
    {
        if ($this->_root === null) {
            return $this->raiseError('No unserialized data available. Use XML_Unserializer::unserialize() first.', XML_UNSERIALIZER_ERROR_NO_UNSERIALIZATION);
        }
        return $this->_unserializedData;
    }

   /**
    * get the name of the root tag
    *
    * @access public
    * @return string  $rootName
    */
    function getRootName()
    {
        if ($this->_root === null) {
            return $this->raiseError('No unserialized data available. Use XML_Unserializer::unserialize() first.', XML_UNSERIALIZER_ERROR_NO_UNSERIALIZATION);
        }
        return $this->_root;
    }

   /**
    * Start element handler for XML parser
    *
    * @access private
    * @param  object $parser  XML parser object
    * @param  string $element XML element
    * @param  array  $attribs attributes of XML tag
    * @return void
    */
    function startHandler($parser, $element, $attribs)
    {
        if (isset($attribs[$this->options[XML_UNSERIALIZER_OPTION_ATTRIBUTE_TYPE]])) {
            $type = $attribs[$this->options[XML_UNSERIALIZER_OPTION_ATTRIBUTE_TYPE]];
            $guessType = false;
        } else {
            $type = 'string';
            if ($this->options[XML_UNSERIALIZER_OPTION_GUESS_TYPES] === true) {
                $guessType = true;
            } else {
                $guessType = false;
            }
        }

        if ($this->options[XML_UNSERIALIZER_OPTION_DECODE_FUNC] !== null) {
            $attribs = array_map($this->options[XML_UNSERIALIZER_OPTION_DECODE_FUNC], $attribs);
        }
        
        $this->_depth++;
        $this->_dataStack[$this->_depth] = null;

        if (is_array($this->options[XML_UNSERIALIZER_OPTION_TAG_MAP]) && isset($this->options[XML_UNSERIALIZER_OPTION_TAG_MAP][$element])) {
            $element = $this->options[XML_UNSERIALIZER_OPTION_TAG_MAP][$element];
        }

        $val = array(
                     'name'         => $element,
                     'value'        => null,
                     'type'         => $type,
                     'guessType'    => $guessType,
                     'childrenKeys' => array(),
                     'aggregKeys'   => array()
                    );

        if ($this->options[XML_UNSERIALIZER_OPTION_ATTRIBUTES_PARSE] == true && (count($attribs) > 0)) {
            $val['children'] = array();
            $val['type']  = $this->_getComplexType($element);
            $val['class'] = $element;

            if ($this->options[XML_UNSERIALIZER_OPTION_GUESS_TYPES] === true) {
            	$attribs = $this->_guessAndSetTypes($attribs);
            }            
            if ($this->options[XML_UNSERIALIZER_OPTION_ATTRIBUTES_ARRAYKEY] != false) {
                $val['children'][$this->options[XML_UNSERIALIZER_OPTION_ATTRIBUTES_ARRAYKEY]] = $attribs;
            } else {
                foreach ($attribs as $attrib => $value) {
                    $val['children'][$this->options[XML_UNSERIALIZER_OPTION_ATTRIBUTES_PREPEND].$attrib] = $value;
                }
            }
        }

        $keyAttr = false;
        
        if (is_string($this->options[XML_UNSERIALIZER_OPTION_ATTRIBUTE_KEY])) {
            $keyAttr = $this->options[XML_UNSERIALIZER_OPTION_ATTRIBUTE_KEY];
        } elseif (is_array($this->options[XML_UNSERIALIZER_OPTION_ATTRIBUTE_KEY])) {
            if (isset($this->options[XML_UNSERIALIZER_OPTION_ATTRIBUTE_KEY][$element])) {
                $keyAttr = $this->options[XML_UNSERIALIZER_OPTION_ATTRIBUTE_KEY][$element];
            } elseif (isset($this->options[XML_UNSERIALIZER_OPTION_ATTRIBUTE_KEY]['#default'])) {
                $keyAttr = $this->options[XML_UNSERIALIZER_OPTION_ATTRIBUTE_KEY]['#default'];
            } elseif (isset($this->options[XML_UNSERIALIZER_OPTION_ATTRIBUTE_KEY]['__default'])) {
                // keep this for BC
                $keyAttr = $this->options[XML_UNSERIALIZER_OPTION_ATTRIBUTE_KEY]['__default'];
            }
        }
        
        if ($keyAttr !== false && isset($attribs[$keyAttr])) {
            $val['name'] = $attribs[$keyAttr];
        }

        if (isset($attribs[$this->options[XML_UNSERIALIZER_OPTION_ATTRIBUTE_CLASS]])) {
            $val['class'] = $attribs[$this->options[XML_UNSERIALIZER_OPTION_ATTRIBUTE_CLASS]];
        }

        array_push($this->_valStack, $val);
    }

   /**
    * Try to guess the type of several values and
    * set them accordingly
    *
    * @access   private
    * @param    array      array, containing the values
    * @return   array      array, containing the values with their correct types 
    */
    function _guessAndSetTypes($array)
    {
        foreach ($array as $key => $value) {
        	$array[$key] = $this->_guessAndSetType($value);
        }
        return $array;
    }
    
   /**
    * Try to guess the type of a value and
    * set it accordingly
    *
    * @access   private
    * @param    string      character data
    * @return   mixed       value with the best matching type
    */
    function _guessAndSetType($value)
    {
        if ($value === 'true') {
            return true;
        }
        if ($value === 'false') {
            return false;
        }
        if ($value === 'NULL') {
            return null;
        }
        if (preg_match('/^[-+]?[0-9]{1,}$/', $value)) {
        	return intval($value);
        }
        if (preg_match('/^[-+]?[0-9]{1,}\.[0-9]{1,}$/', $value)) {
        	return doubleval($value);
        }
        return (string)$value;
    }
    
   /**
    * End element handler for XML parser
    *
    * @access private
    * @param  object XML parser object
    * @param  string
    * @return void
    */
    function endHandler($parser, $element)
    {
        $value = array_pop($this->_valStack);
        switch ($this->options[XML_UNSERIALIZER_OPTION_WHITESPACE]) {
            case XML_UNSERIALIZER_WHITESPACE_KEEP:
                $data = $this->_dataStack[$this->_depth];
                break;
            case XML_UNSERIALIZER_WHITESPACE_NORMALIZE:
                $data = trim(preg_replace('/\s\s+/m', ' ', $this->_dataStack[$this->_depth]));
                break;
            case XML_UNSERIALIZER_WHITESPACE_TRIM:
            default:
                $data  = trim($this->_dataStack[$this->_depth]);
                break;
        }

        // adjust type of the value
        switch(strtolower($value['type'])) {

            // unserialize an object
            case 'object':
                if (isset($value['class'])) {
                    $classname  = $value['class'];
                } else {
                    $classname = '';
                }
                // instantiate the class
                if ($this->options[XML_UNSERIALIZER_OPTION_TAG_AS_CLASSNAME] === true && class_exists($classname)) {
                    $value['value'] = &new $classname;
                } else {
                    $value['value'] = &new $this->options[XML_UNSERIALIZER_OPTION_DEFAULT_CLASS];
                }
                if (trim($data) !== '') {
                    if ($value['guessType'] === true) {
                    	$data = $this->_guessAndSetType($data);
                    }
                    $value['children'][$this->options[XML_UNSERIALIZER_OPTION_CONTENT_KEY]] = $data;
                }

                // set properties
                foreach ($value['children'] as $prop => $propVal) {
                    // check whether there is a special method to set this property
                    $setMethod = 'set'.$prop;
                    if (method_exists($value['value'], $setMethod)) {
                        call_user_func(array(&$value['value'], $setMethod), $propVal);
                    } else {
                        $value['value']->$prop = $propVal;
                    }
                }
                //  check for magic function
                if (method_exists($value['value'], '__wakeup')) {
                    $value['value']->__wakeup();
                }
                break;

            // unserialize an array
            case 'array':
                if (trim($data) !== '') {
                    if ($value['guessType'] === true) {
                    	$data = $this->_guessAndSetType($data);
                    }
                    $value['children'][$this->options[XML_UNSERIALIZER_OPTION_CONTENT_KEY]] = $data;
                }
                if (isset($value['children'])) {
                    $value['value'] = $value['children'];
                } else {
                    $value['value'] = array();
                }
                break;

            // unserialize a null value
            case 'null':
                $data = null;
                break;

            // unserialize a resource => this is not possible :-(
            case 'resource':
                $value['value'] = $data;
                break;

            // unserialize any scalar value
            default:
                if ($value['guessType'] === true) {
                    $data = $this->_guessAndSetType($data);
                } else {
                    settype($data, $value['type']);
                }
            
                $value['value'] = $data;
                break;
        }
        $parent = array_pop($this->_valStack);
        if ($parent === null) {
            $this->_unserializedData = &$value['value'];
            $this->_root = &$value['name'];
            return true;
        } else {
            // parent has to be an array
            if (!isset($parent['children']) || !is_array($parent['children'])) {
                $parent['children'] = array();
                if (!in_array($parent['type'], array('array', 'object'))) {
                    $parent['type'] = $this->_getComplexType($parent['name']);
                    if ($parent['type'] == 'object') {
                        $parent['class'] = $parent['name'];
                    }
                }
            }

            if (in_array($element, $this->options[XML_UNSERIALIZER_OPTION_IGNORE_KEYS])) {
                $ignoreKey = true;
            } else {
            	$ignoreKey = false;
            }
            
            if (!empty($value['name']) && $ignoreKey === false) {
                // there already has been a tag with this name
                if (in_array($value['name'], $parent['childrenKeys']) || in_array($value['name'], $this->options[XML_UNSERIALIZER_OPTION_FORCE_ENUM])) {
                    // no aggregate has been created for this tag
                    if (!in_array($value['name'], $parent['aggregKeys'])) {
                        if (isset($parent['children'][$value['name']])) {
                            $parent['children'][$value['name']] = array($parent['children'][$value['name']]);
                        } else {
                            $parent['children'][$value['name']] = array();
                        }
                        array_push($parent['aggregKeys'], $value['name']);
                    }
                    array_push($parent['children'][$value['name']], $value['value']);
                } else {
                    $parent['children'][$value['name']] = &$value['value'];
                    array_push($parent['childrenKeys'], $value['name']);
                }
            } else {
                array_push($parent['children'], $value['value']);
            }
            array_push($this->_valStack, $parent);
        }

        $this->_depth--;
    }

   /**
    * Handler for character data
    *
    * @access private
    * @param  object XML parser object
    * @param  string CDATA
    * @return void
    */
    function cdataHandler($parser, $cdata)
    {
        if ($this->options[XML_UNSERIALIZER_OPTION_DECODE_FUNC] !== null) {
            $cdata = call_user_func($this->options[XML_UNSERIALIZER_OPTION_DECODE_FUNC], $cdata);
        }
        $this->_dataStack[$this->_depth] .= $cdata;
    }

   /**
    * get the complex type, that should be used for a specified tag
    *
    * @access   private
    * @param    string      name of the tag
    * @return   string      complex type ('array' or 'object')
    */
    function _getComplexType($tagname)
    {
        if (is_string($this->options[XML_UNSERIALIZER_OPTION_COMPLEXTYPE])) {
        	return $this->options[XML_UNSERIALIZER_OPTION_COMPLEXTYPE];
        }
        if (isset($this->options[XML_UNSERIALIZER_OPTION_COMPLEXTYPE][$tagname])) {
        	return $this->options[XML_UNSERIALIZER_OPTION_COMPLEXTYPE][$tagname];
        }
        if (isset($this->options[XML_UNSERIALIZER_OPTION_COMPLEXTYPE]['#default'])) {
        	return $this->options[XML_UNSERIALIZER_OPTION_COMPLEXTYPE]['#default'];
        }
        return 'array';
    }
    
   /**
    * create the XML_Parser instance
    *
    * @access    private
    * @return    boolean
    */
    function _createParser()
    {
        if (is_object($this->_parser)) {
            $this->_parser->free();
            unset($this->_parser);
        }
        $this->_parser = &new XML_Parser($this->options[XML_UNSERIALIZER_OPTION_ENCODING_SOURCE], 'event', $this->options[XML_UNSERIALIZER_OPTION_ENCODING_TARGET]);
        $this->_parser->folding = false;
        $this->_parser->setHandlerObj($this);
        return true;
    }
}
?>