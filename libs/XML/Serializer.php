<?PHP
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * XML_Serializer
 *
 * Creates XML documents from PHP data structures like arrays, objects or scalars.
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
 * @version    CVS: $Id: Serializer.php,v 1.47 2005/09/30 13:40:30 schst Exp $
 * @link       http://pear.php.net/package/XML_Serializer
 * @see        XML_Unserializer
 */

/**
 * uses PEAR error management
 */
require_once 'PEAR.php';

/**
 * uses XML_Util to create XML tags
 */
require_once 'XML/Util.php';

/**
 * option: string used for indentation
 *
 * Possible values:
 * - any string (default is any string)
 */
define('XML_SERIALIZER_OPTION_INDENT', 'indent');

/**
 * option: string used for linebreaks
 *
 * Possible values:
 * - any string (default is \n)
 */
define('XML_SERIALIZER_OPTION_LINEBREAKS', 'linebreak');

/**
 * option: enable type hints
 *
 * Possible values:
 * - true
 * - false
 */
define('XML_SERIALIZER_OPTION_TYPEHINTS', 'typeHints');

/**
 * option: add an XML declaration
 *
 * Possible values:
 * - true
 * - false
 */
define('XML_SERIALIZER_OPTION_XML_DECL_ENABLED', 'addDecl');

/**
 * option: encoding of the document
 *
 * Possible values:
 * - any valid encoding
 * - null (default)
 */
define('XML_SERIALIZER_OPTION_XML_ENCODING', 'encoding');

/**
 * option: default name for tags
 *
 * Possible values:
 * - any string (XML_Serializer_Tag is default)
 */
define('XML_SERIALIZER_OPTION_DEFAULT_TAG', 'defaultTagName');

/**
 * option: use classname for objects in indexed arrays
 *
 * Possible values:
 * - true (default)
 * - false
 */
define('XML_SERIALIZER_OPTION_CLASSNAME_AS_TAGNAME', 'classAsTagName');

/**
 * option: attribute where original key is stored
 *
 * Possible values:
 * - any string (default is _originalKey)
 */
define('XML_SERIALIZER_OPTION_ATTRIBUTE_KEY', 'keyAttribute');

/**
 * option: attribute for type (only if typeHints => true)
 *
 * Possible values:
 * - any string (default is _type)
 */
define('XML_SERIALIZER_OPTION_ATTRIBUTE_TYPE', 'typeAttribute');

/**
 * option: attribute for class (only if typeHints => true)
 *
 * Possible values:
 * - any string (default is _class)
 */
define('XML_SERIALIZER_OPTION_ATTRIBUTE_CLASS', 'classAttribute');

/**
 * option: scalar values (strings, ints,..) will be serialized as attribute
 *
 * Possible values:
 * - true
 * - false (default)
 * - array which sets this option on a per-tag basis
 */
define('XML_SERIALIZER_OPTION_SCALAR_AS_ATTRIBUTES', 'scalarAsAttributes');

/**
 * option: prepend string for attributes
 *
 * Possible values:
 * - any string (default is any string)
 */
define('XML_SERIALIZER_OPTION_PREPEND_ATTRIBUTES', 'prependAttributes');

/**
 * option: indent the attributes, if set to '_auto', it will indent attributes so they all start at the same column
 *
 * Possible values:
 * - true
 * - false (default)
 */
define('XML_SERIALIZER_OPTION_INDENT_ATTRIBUTES', 'indentAttributes');

/**
 * option: use 'simplexml' to use parent name as tagname if transforming an indexed array
 *
 * Possible values:
 * - XML_SERIALIZER_MODE_DEFAULT (default)
 * - XML_SERIALIZER_MODE_SIMPLEXML
 */
define('XML_SERIALIZER_OPTION_MODE', 'mode');

/**
 * option: add a doctype declaration
 *
 * Possible values:
 * - true
 * - false (default)
 */
define('XML_SERIALIZER_OPTION_DOCTYPE_ENABLED', 'addDoctype');

/**
 * option: supply a string or an array with id and uri ({@see XML_Util::getDoctypeDeclaration()}
 *
 * Possible values:
 * - string
 * - array
 */
define('XML_SERIALIZER_OPTION_DOCTYPE', 'doctype');

/**
 * option: name of the root tag
 *
 * Possible values:
 * - string
 * - null (default)
 */
define('XML_SERIALIZER_OPTION_ROOT_NAME', 'rootName');

/**
 * option: attributes of the root tag
 *
 * Possible values:
 * - array
 */
define('XML_SERIALIZER_OPTION_ROOT_ATTRIBS', 'rootAttributes');

/**
 * option: all values in this key will be treated as attributes
 *
 * Possible values:
 * - array
 */
define('XML_SERIALIZER_OPTION_ATTRIBUTES_KEY', 'attributesArray');

/**
 * option: this value will be used directly as content, instead of creating a new tag, may only be used in conjuction with attributesArray
 *
 * Possible values:
 * - string
 * - null (default)
 */
define('XML_SERIALIZER_OPTION_CONTENT_KEY', 'contentName');

/**
 * option: this value will be used in a comment, instead of creating a new tag
 *
 * Possible values:
 * - string
 * - null (default)
 */
define('XML_SERIALIZER_OPTION_COMMENT_KEY', 'commentName');

/**
 * option: tag names that will be changed
 *
 * Possible values:
 * - array
 */
define('XML_SERIALIZER_OPTION_TAGMAP', 'tagMap');

/**
 * option: function that will be applied before serializing
 *
 * Possible values:
 * - any valid PHP callback
 */
define('XML_SERIALIZER_OPTION_ENCODE_FUNC', 'encodeFunction');

/**
 * option: function that will be applied before serializing
 *
 * Possible values:
 * - string
 * - null (default)
 */
define('XML_SERIALIZER_OPTION_NAMESPACE', 'namespace');

/**
 * option: type of entities to replace
 *
 * Possible values:
 * - XML_SERIALIZER_ENTITIES_NONE
 * - XML_SERIALIZER_ENTITIES_XML (default)
 * - XML_SERIALIZER_ENTITIES_XML_REQUIRED
 * - XML_SERIALIZER_ENTITIES_HTML
 */
define('XML_SERIALIZER_OPTION_ENTITIES', 'replaceEntities');

/**
 * option: whether to return the result of the serialization from serialize()
 *
 * Possible values:
 * - true
 * - false (default)
 */
define('XML_SERIALIZER_OPTION_RETURN_RESULT', 'returnResult');

/**
 * option: whether to ignore properties that are set to null
 *
 * Possible values:
 * - true
 * - false (default)
 */
define('XML_SERIALIZER_OPTION_IGNORE_NULL', 'ignoreNull');

/**
 * option: whether to use cdata sections for character data
 *
 * Possible values:
 * - true
 * - false (default)
 */
define('XML_SERIALIZER_OPTION_CDATA_SECTIONS', 'cdata');


/**
 * default mode
 */
define('XML_SERIALIZER_MODE_DEFAULT', 'default');

/**
 * SimpleXML mode
 *
 * When serializing indexed arrays, the key of the parent value is used as a tagname.
 */
define('XML_SERIALIZER_MODE_SIMPLEXML', 'simplexml');
                         
/**
 * error code for no serialization done
 */
define('XML_SERIALIZER_ERROR_NO_SERIALIZATION', 51);

/**
 * do not replace entitites
 */
define('XML_SERIALIZER_ENTITIES_NONE', XML_UTIL_ENTITIES_NONE);

/**
 * replace all XML entitites
 * This setting will replace <, >, ", ' and &
 */
define('XML_SERIALIZER_ENTITIES_XML', XML_UTIL_ENTITIES_XML);

/**
 * replace only required XML entitites
 * This setting will replace <, " and &
 */
define('XML_SERIALIZER_ENTITIES_XML_REQUIRED', XML_UTIL_ENTITIES_XML_REQUIRED);

/**
 * replace HTML entitites
 * @link    http://www.php.net/htmlentities
 */
define('XML_SERIALIZER_ENTITIES_HTML', XML_UTIL_ENTITIES_HTML);

/**
 * Creates XML documents from PHP data structures like arrays, objects or scalars.
 *
 * this class can be used in two modes:
 *
 *  1. create an XML document from an array or object that is processed by other
 *    applications. That means, you can create a RDF document from an array in the
 *    following format:
 *
 *    $data = array(
 *              'channel' => array(
 *                            'title' => 'Example RDF channel',
 *                            'link'  => 'http://www.php-tools.de',
 *                            'image' => array(
 *                                        'title' => 'Example image',
 *                                        'url'   => 'http://www.php-tools.de/image.gif',
 *                                        'link'  => 'http://www.php-tools.de'
 *                                           ),
 *                            array(
 *                                 'title' => 'Example item',
 *                                 'link'  => 'http://example.com'
 *                                 ),
 *                            array(
 *                                 'title' => 'Another Example item',
 *                                 'link'  => 'http://example.org'
 *                                 )
 *                              )
 *             );
 *
 *   to create a RDF document from this array do the following:
 *
 *   require_once 'XML/Serializer.php';
 *
 *   $options = array(
 *                     XML_SERIALIZER_OPTION_INDENT      => "\t",        // indent with tabs
 *                     XML_SERIALIZER_OPTION_LINEBREAKS  => "\n",        // use UNIX line breaks
 *                     XML_SERIALIZER_OPTION_ROOT_NAME   => 'rdf:RDF',   // root tag
 *                     XML_SERIALIZER_OPTION_DEFAULT_TAG => 'item'       // tag for values with numeric keys
 *   );
 *
 *   $serializer = new XML_Serializer($options);
 *   $rdf        = $serializer->serialize($data);
 *
 * You will get a complete XML document that can be processed like any RDF document.
 *
 * 2. this classes can be used to serialize any data structure in a way that it can
 *    later be unserialized again.
 *    XML_Serializer will store the type of the value and additional meta information
 *    in attributes of the surrounding tag. This meat information can later be used
 *    to restore the original data structure in PHP. If you want XML_Serializer
 *    to add meta information to the tags, add
 *
 *      XML_SERIALIZER_OPTION_TYPEHINTS => true
 *
 *    to the options array in the constructor.
 *
 * @category   XML
 * @package    XML_Serializer
 * @author     Stephan Schmidt <schst@php.net>
 * @copyright  1997-2005 The PHP Group
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version    Release: @package_version@
 * @link       http://pear.php.net/package/XML_Serializer
 * @see        XML_Unserializer
 */
class XML_Serializer extends PEAR
{
   /**
    * list of all available options
    *
    * @access private
    * @var    array
    */
    var $_knownOptions = array(
                                 XML_SERIALIZER_OPTION_INDENT,
                                 XML_SERIALIZER_OPTION_LINEBREAKS,
                                 XML_SERIALIZER_OPTION_TYPEHINTS,
                                 XML_SERIALIZER_OPTION_XML_DECL_ENABLED,
                                 XML_SERIALIZER_OPTION_XML_ENCODING,
                                 XML_SERIALIZER_OPTION_DEFAULT_TAG,
                                 XML_SERIALIZER_OPTION_CLASSNAME_AS_TAGNAME,
                                 XML_SERIALIZER_OPTION_ATTRIBUTE_KEY,
                                 XML_SERIALIZER_OPTION_ATTRIBUTE_TYPE,
                                 XML_SERIALIZER_OPTION_ATTRIBUTE_CLASS,
                                 XML_SERIALIZER_OPTION_SCALAR_AS_ATTRIBUTES,
                                 XML_SERIALIZER_OPTION_PREPEND_ATTRIBUTES,
                                 XML_SERIALIZER_OPTION_INDENT_ATTRIBUTES,
                                 XML_SERIALIZER_OPTION_MODE,
                                 XML_SERIALIZER_OPTION_DOCTYPE_ENABLED,
                                 XML_SERIALIZER_OPTION_DOCTYPE,
                                 XML_SERIALIZER_OPTION_ROOT_NAME,
                                 XML_SERIALIZER_OPTION_ROOT_ATTRIBS,
                                 XML_SERIALIZER_OPTION_ATTRIBUTES_KEY,
                                 XML_SERIALIZER_OPTION_CONTENT_KEY,
                                 XML_SERIALIZER_OPTION_COMMENT_KEY,
                                 XML_SERIALIZER_OPTION_TAGMAP,
                                 XML_SERIALIZER_OPTION_ENCODE_FUNC,
                                 XML_SERIALIZER_OPTION_NAMESPACE,
                                 XML_SERIALIZER_OPTION_ENTITIES,
                                 XML_SERIALIZER_OPTION_RETURN_RESULT,
                                 XML_SERIALIZER_OPTION_IGNORE_NULL,
                                 XML_SERIALIZER_OPTION_CDATA_SECTIONS
                                );
    
   /**
    * default options for the serialization
    *
    * @access private
    * @var    array
    */
    var $_defaultOptions = array(
                         XML_SERIALIZER_OPTION_INDENT               => '',                    // string used for indentation
                         XML_SERIALIZER_OPTION_LINEBREAKS           => "\n",                  // string used for newlines
                         XML_SERIALIZER_OPTION_TYPEHINTS            => false,                 // automatically add type hin attributes
                         XML_SERIALIZER_OPTION_XML_DECL_ENABLED     => false,                 // add an XML declaration
                         XML_SERIALIZER_OPTION_XML_ENCODING         => null,                  // encoding specified in the XML declaration
                         XML_SERIALIZER_OPTION_DEFAULT_TAG          => 'XML_Serializer_Tag',  // tag used for indexed arrays or invalid names
                         XML_SERIALIZER_OPTION_CLASSNAME_AS_TAGNAME => false,                 // use classname for objects in indexed arrays
                         XML_SERIALIZER_OPTION_ATTRIBUTE_KEY        => '_originalKey',        // attribute where original key is stored
                         XML_SERIALIZER_OPTION_ATTRIBUTE_TYPE       => '_type',               // attribute for type (only if typeHints => true)
                         XML_SERIALIZER_OPTION_ATTRIBUTE_CLASS      => '_class',              // attribute for class of objects (only if typeHints => true)
                         XML_SERIALIZER_OPTION_SCALAR_AS_ATTRIBUTES => false,                 // scalar values (strings, ints,..) will be serialized as attribute
                         XML_SERIALIZER_OPTION_PREPEND_ATTRIBUTES   => '',                    // prepend string for attributes
                         XML_SERIALIZER_OPTION_INDENT_ATTRIBUTES    => false,                 // indent the attributes, if set to '_auto', it will indent attributes so they all start at the same column
                         XML_SERIALIZER_OPTION_MODE                 => XML_SERIALIZER_MODE_DEFAULT,             // use XML_SERIALIZER_MODE_SIMPLEXML to use parent name as tagname if transforming an indexed array
                         XML_SERIALIZER_OPTION_DOCTYPE_ENABLED      => false,                 // add a doctype declaration
                         XML_SERIALIZER_OPTION_DOCTYPE              => null,                  // supply a string or an array with id and uri ({@see XML_Util::getDoctypeDeclaration()}
                         XML_SERIALIZER_OPTION_ROOT_NAME            => null,                  // name of the root tag
                         XML_SERIALIZER_OPTION_ROOT_ATTRIBS         => array(),               // attributes of the root tag
                         XML_SERIALIZER_OPTION_ATTRIBUTES_KEY       => null,                  // all values in this key will be treated as attributes
                         XML_SERIALIZER_OPTION_CONTENT_KEY          => null,                  // this value will be used directly as content, instead of creating a new tag, may only be used in conjuction with attributesArray
                         XML_SERIALIZER_OPTION_COMMENT_KEY          => null,                  // this value will be used directly as comment, instead of creating a new tag, may only be used in conjuction with attributesArray
                         XML_SERIALIZER_OPTION_TAGMAP               => array(),               // tag names that will be changed
                         XML_SERIALIZER_OPTION_ENCODE_FUNC          => null,                  // function that will be applied before serializing
                         XML_SERIALIZER_OPTION_NAMESPACE            => null,                  // namespace to use
                         XML_SERIALIZER_OPTION_ENTITIES             => XML_SERIALIZER_ENTITIES_XML, // type of entities to replace,
                         XML_SERIALIZER_OPTION_RETURN_RESULT        => false,                 // serialize() returns the result of the serialization instead of true
                         XML_SERIALIZER_OPTION_IGNORE_NULL          => false,                 // ignore properties that are set to null
                         XML_SERIALIZER_OPTION_CDATA_SECTIONS       => false                  // Whether to use cdata sections for plain character data
                        );

   /**
    * options for the serialization
    *
    * @access public
    * @var    array
    */
    var $options = array();

   /**
    * current tag depth
    *
    * @access private
    * @var    integer
    */
    var $_tagDepth = 0;

   /**
    * serilialized representation of the data
    *
    * @access private
    * @var    string
    */
    var $_serializedData = null;
    
   /**
    * constructor
    *
    * @access   public
    * @param    mixed   $options    array containing options for the serialization
    */
    function XML_Serializer( $options = null )
    {
        $this->PEAR();
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
    * @see      setOption(), XML_Serializer()
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
    * @see      resetOption(), XML_Serializer()
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
    * serialize data
    *
    * @access   public
    * @param    mixed    $data data to serialize
    * @return   boolean  true on success, pear error on failure
    */
    function serialize($data, $options = null)
    {
        // if options have been specified, use them instead
        // of the previously defined ones
        if (is_array($options)) {
            $optionsBak = $this->options;
            if (isset($options['overrideOptions']) && $options['overrideOptions'] == true) {
                $this->options = array_merge($this->_defaultOptions, $options);
            } else {
                $this->options = array_merge($this->options, $options);
            }
        } else {
            $optionsBak = null;
        }
        
        //  start depth is zero
        $this->_tagDepth = 0;

        $rootAttributes = $this->options[XML_SERIALIZER_OPTION_ROOT_ATTRIBS];
        if (isset($this->options[XML_SERIALIZER_OPTION_NAMESPACE]) && is_array($this->options[XML_SERIALIZER_OPTION_NAMESPACE])) {
        	$rootAttributes['xmlns:'.$this->options[XML_SERIALIZER_OPTION_NAMESPACE][0]] = $this->options[XML_SERIALIZER_OPTION_NAMESPACE][1];
        }
        
        $this->_serializedData = '';
        // serialize an array
        if (is_array($data)) {
            if (isset($this->options[XML_SERIALIZER_OPTION_ROOT_NAME])) {
                $tagName = $this->options[XML_SERIALIZER_OPTION_ROOT_NAME];
            } else {
                $tagName = 'array';
            }

            $this->_serializedData .= $this->_serializeArray($data, $tagName, $rootAttributes);
        } elseif (is_object($data)) {
            // serialize an object
            if (isset($this->options[XML_SERIALIZER_OPTION_ROOT_NAME])) {
                $tagName = $this->options[XML_SERIALIZER_OPTION_ROOT_NAME];
            } else {
                $tagName = get_class($data);
            }
            $this->_serializedData .= $this->_serializeObject($data, $tagName, $rootAttributes);
        } else {
            $tag = array();
            if (isset($this->options[XML_SERIALIZER_OPTION_ROOT_NAME])) {
                $tag['qname'] = $this->options[XML_SERIALIZER_OPTION_ROOT_NAME];
            } else {
                $tag['qname'] = gettype($data);
            }
            if ($this->options[XML_SERIALIZER_OPTION_TYPEHINTS] === true) {
                $rootAttributes[$this->options[XML_SERIALIZER_OPTION_ATTRIBUTE_TYPE]] = gettype($data);
            }
            @settype($data, 'string');
            $tag['content']    = $data;
            $tag['attributes'] = $rootAttributes;
            $this->_serializedData = $this->_createXMLTag($tag);
        }
        
        // add doctype declaration
        if ($this->options[XML_SERIALIZER_OPTION_DOCTYPE_ENABLED] === true) {
            $this->_serializedData = XML_Util::getDoctypeDeclaration($tagName, $this->options[XML_SERIALIZER_OPTION_DOCTYPE])
                                   . $this->options[XML_SERIALIZER_OPTION_LINEBREAKS]
                                   . $this->_serializedData;
        }

        //  build xml declaration
        if ($this->options[XML_SERIALIZER_OPTION_XML_DECL_ENABLED]) {
            $atts = array();
            $this->_serializedData = XML_Util::getXMLDeclaration('1.0', $this->options[XML_SERIALIZER_OPTION_XML_ENCODING])
                                   . $this->options[XML_SERIALIZER_OPTION_LINEBREAKS]
                                   . $this->_serializedData;
        }

        if ($this->options[XML_SERIALIZER_OPTION_RETURN_RESULT] === true) {
        	$result = $this->_serializedData;
        } else {
            $result = true;
        }
        
        if ($optionsBak !== null) {
            $this->options = $optionsBak;
        }

        return $result;
    }

   /**
    * get the result of the serialization
    *
    * @access public
    * @return string serialized XML
    */
    function getSerializedData()
    {
        if ($this->_serializedData == null) {
            return  $this->raiseError('No serialized data available. Use XML_Serializer::serialize() first.', XML_SERIALIZER_ERROR_NO_SERIALIZATION);
        }
        return $this->_serializedData;
    }
    
   /**
    * serialize any value
    *
    * This method checks for the type of the value and calls the appropriate method
    *
    * @access private
    * @param  mixed     $value
    * @param  string    $tagName
    * @param  array     $attributes
    * @return string
    */
    function _serializeValue($value, $tagName = null, $attributes = array())
    {
        if (is_array($value)) {
            $xml = $this->_serializeArray($value, $tagName, $attributes);
        } elseif (is_object($value)) {
            $xml = $this->_serializeObject($value, $tagName);
        } else {
            $tag = array(
                          'qname'      => $tagName,
                          'attributes' => $attributes,
                          'content'    => $value
                        );
            $xml = $this->_createXMLTag($tag);
        }
        return $xml;
    }
    
   /**
    * serialize an array
    *
    * @access   private
    * @param    array   $array       array to serialize
    * @param    string  $tagName     name of the root tag
    * @param    array   $attributes  attributes for the root tag
    * @return   string  $string      serialized data
    * @uses     XML_Util::isValidName() to check, whether key has to be substituted
    */
    function _serializeArray(&$array, $tagName = null, $attributes = array())
    {
        $_content = null;
        $_comment = null;

        // check for comment
        if ($this->options[XML_SERIALIZER_OPTION_COMMENT_KEY] !== null) {
            if (isset($array[$this->options[XML_SERIALIZER_OPTION_COMMENT_KEY]])) {
                $_comment = $array[$this->options[XML_SERIALIZER_OPTION_COMMENT_KEY]];
                unset($array[$this->options[XML_SERIALIZER_OPTION_COMMENT_KEY]]);
            }
        }
        
        /**
         * check for special attributes
         */
        if ($this->options[XML_SERIALIZER_OPTION_ATTRIBUTES_KEY] !== null) {
            if (isset($array[$this->options[XML_SERIALIZER_OPTION_ATTRIBUTES_KEY]])) {
                $attributes = $array[$this->options[XML_SERIALIZER_OPTION_ATTRIBUTES_KEY]];
                unset($array[$this->options[XML_SERIALIZER_OPTION_ATTRIBUTES_KEY]]);
            }
            /**
             * check for special content
             */
            if ($this->options[XML_SERIALIZER_OPTION_CONTENT_KEY] !== null) {
                if (isset($array[$this->options[XML_SERIALIZER_OPTION_CONTENT_KEY]])) {
                    $_content = $array[$this->options[XML_SERIALIZER_OPTION_CONTENT_KEY]];
                    unset($array[$this->options[XML_SERIALIZER_OPTION_CONTENT_KEY]]);
                }
            }
        }

        if ($this->options[XML_SERIALIZER_OPTION_IGNORE_NULL] === true) {
        	foreach (array_keys($array) as $key) {
        		if (is_null($array[$key])) {
        			unset($array[$key]);
        		}
        	}
        }

        /*
        * if mode is set to simpleXML, check whether
        * the array is associative or indexed
        */
        if (is_array($array) && !empty($array) && $this->options[XML_SERIALIZER_OPTION_MODE] == XML_SERIALIZER_MODE_SIMPLEXML) {
            $indexed = true;
            foreach ($array as $key => $val) {
                if (!is_int($key)) {
                    $indexed = false;
                    break;
                }
            }

            if ($indexed && $this->options[XML_SERIALIZER_OPTION_MODE] == XML_SERIALIZER_MODE_SIMPLEXML) {
                $string = '';
                foreach ($array as $key => $val) {
                    $string .= $this->_serializeValue( $val, $tagName, $attributes);
                    
                    $string .= $this->options[XML_SERIALIZER_OPTION_LINEBREAKS];
                    // do indentation
                    if ($this->options[XML_SERIALIZER_OPTION_INDENT]!==null && $this->_tagDepth>0) {
                        $string .= str_repeat($this->options[XML_SERIALIZER_OPTION_INDENT], $this->_tagDepth);
                    }
                }
                return rtrim($string);
            }
        }
        
        $scalarAsAttributes = false;
        if (is_array($this->options[XML_SERIALIZER_OPTION_SCALAR_AS_ATTRIBUTES]) && isset($this->options[XML_SERIALIZER_OPTION_SCALAR_AS_ATTRIBUTES][$tagName])) {
            $scalarAsAttributes = $this->options[XML_SERIALIZER_OPTION_SCALAR_AS_ATTRIBUTES][$tagName];
        } elseif ($this->options[XML_SERIALIZER_OPTION_SCALAR_AS_ATTRIBUTES] === true) {
            $scalarAsAttributes = true;
        }
        
        if ($scalarAsAttributes === true) {
            $this->expectError('*');
            foreach ($array as $key => $value) {
                if (is_scalar($value) && (XML_Util::isValidName($key) === true)) {
                    unset($array[$key]);
                    $attributes[$this->options[XML_SERIALIZER_OPTION_PREPEND_ATTRIBUTES].$key] = $value;
                }
            }
            $this->popExpect();
        } elseif (is_array($scalarAsAttributes)) {
            $this->expectError('*');
            foreach ($scalarAsAttributes as $key) {
                if (!isset($array[$key])) {
                    continue;
                }
                $value = $array[$key];
                if (is_scalar($value) && (XML_Util::isValidName($key) === true)) {
                    unset($array[$key]);
                    $attributes[$this->options[XML_SERIALIZER_OPTION_PREPEND_ATTRIBUTES].$key] = $value;
                }
            }
            $this->popExpect();
        }

        // check for empty array => create empty tag
        if (empty($array)) {
            $tag = array(
                            'qname'      => $tagName,
                            'content'    => $_content,
                            'attributes' => $attributes
                        );
        } else {
            $this->_tagDepth++;
            $tmp = $this->options[XML_SERIALIZER_OPTION_LINEBREAKS];
            foreach ($array as $key => $value) {
                // do indentation
                if ($this->options[XML_SERIALIZER_OPTION_INDENT]!==null && $this->_tagDepth>0) {
                    $tmp .= str_repeat($this->options[XML_SERIALIZER_OPTION_INDENT], $this->_tagDepth);
                }

                if (isset($this->options[XML_SERIALIZER_OPTION_TAGMAP][$key])) {
                    $key = $this->options[XML_SERIALIZER_OPTION_TAGMAP][$key];
                }

                // copy key
                $origKey = $key;
                $this->expectError('*');
                // key cannot be used as tagname => use default tag
                $valid = XML_Util::isValidName($key);
                $this->popExpect();
                if (PEAR::isError($valid)) {
                    if ($this->options[XML_SERIALIZER_OPTION_CLASSNAME_AS_TAGNAME] && is_object($value)) {
                        $key = get_class($value);
                    } else {
                        $key = $this->_getDefaultTagname($tagName);
                    }
                }
                $atts = array();
                if ($this->options[XML_SERIALIZER_OPTION_TYPEHINTS] === true) {
                    $atts[$this->options[XML_SERIALIZER_OPTION_ATTRIBUTE_TYPE]] = gettype($value);
                    if ($key !== $origKey) {
                        $atts[$this->options[XML_SERIALIZER_OPTION_ATTRIBUTE_KEY]] = (string)$origKey;
                    }
                }

                $tmp .= $this->_createXMLTag(array(
                                                    'qname'      => $key,
                                                    'attributes' => $atts,
                                                    'content'    => $value )
                                            );
                $tmp .= $this->options[XML_SERIALIZER_OPTION_LINEBREAKS];
            }
            
            $this->_tagDepth--;
            if ($this->options[XML_SERIALIZER_OPTION_INDENT]!==null && $this->_tagDepth>0) {
                $tmp .= str_repeat($this->options[XML_SERIALIZER_OPTION_INDENT], $this->_tagDepth);
            }
    
            if (trim($tmp) === '') {
                $tmp = null;
            }
            
            $tag = array(
                          'qname'      => $tagName,
                          'content'    => $tmp,
                          'attributes' => $attributes
                        );
        }
        if ($this->options[XML_SERIALIZER_OPTION_TYPEHINTS] === true) {
            if (!isset($tag['attributes'][$this->options[XML_SERIALIZER_OPTION_ATTRIBUTE_TYPE]])) {
                $tag['attributes'][$this->options[XML_SERIALIZER_OPTION_ATTRIBUTE_TYPE]] = 'array';
            }
        }

        $string = '';
        if (!is_null($_comment)) {
        	$string .= XML_Util::createComment($_comment);
        	$string .= $this->options[XML_SERIALIZER_OPTION_LINEBREAKS];
            if ($this->options[XML_SERIALIZER_OPTION_INDENT]!==null && $this->_tagDepth>0) {
                $string .= str_repeat($this->options[XML_SERIALIZER_OPTION_INDENT], $this->_tagDepth);
            }
        }
        $string .= $this->_createXMLTag($tag, false);
        return $string;
    }

   /**
    * get the name of the default tag.
    *
    * The name of the parent tag needs to be passed as the
    * default name can depend on the context.
    *
    * @param  string     name of the parent tag
    * @return string     default tag name
    */
    function _getDefaultTagname($parent)
    {
        if (is_string($this->options[XML_SERIALIZER_OPTION_DEFAULT_TAG])) {
        	return $this->options[XML_SERIALIZER_OPTION_DEFAULT_TAG];
        }
        if (isset($this->options[XML_SERIALIZER_OPTION_DEFAULT_TAG][$parent])) {
            return $this->options[XML_SERIALIZER_OPTION_DEFAULT_TAG][$parent];
        } elseif (isset($this->options[XML_SERIALIZER_OPTION_DEFAULT_TAG]['#default'])) {
            return $this->options[XML_SERIALIZER_OPTION_DEFAULT_TAG]['#default'];
        } elseif (isset($this->options[XML_SERIALIZER_OPTION_DEFAULT_TAG]['__default'])) {
            // keep this for BC
            return $this->options[XML_SERIALIZER_OPTION_DEFAULT_TAG]['__default'];
        }
        return 'XML_Serializer_Tag';
    }
    
   /**
    * serialize an object
    *
    * @access   private
    * @param    object  $object object to serialize
    * @return   string  $string serialized data
    */
    function _serializeObject(&$object, $tagName = null, $attributes = array())
    {
        // check for magic function
        if (method_exists($object, '__sleep')) {
            $propNames = $object->__sleep();
            if (is_array($propNames)) {
            	$properties = array();
            	foreach ($propNames as $propName) {
            		$properties[$propName] = $object->$propName;
            	}
            } else {
                $properties = get_object_vars($object);
            }
        } else {
            $properties = get_object_vars($object);
        }

        if (empty($tagName)) {
            $tagName = get_class($object);
        }

        // typehints activated?
        if ($this->options[XML_SERIALIZER_OPTION_TYPEHINTS] === true) {
            $attributes[$this->options[XML_SERIALIZER_OPTION_ATTRIBUTE_TYPE]]  = 'object';
            $attributes[$this->options[XML_SERIALIZER_OPTION_ATTRIBUTE_CLASS]] =  get_class($object);
        }
        $string = $this->_serializeArray($properties, $tagName, $attributes);
        return $string;
    }
  
   /**
    * create a tag from an array
    * this method awaits an array in the following format
    * array(
    *       'qname'        => $tagName,
    *       'attributes'   => array(),
    *       'content'      => $content,      // optional
    *       'namespace'    => $namespace     // optional
    *       'namespaceUri' => $namespaceUri  // optional
    *   )
    *
    * @access   private
    * @param    array   $tag tag definition
    * @param    boolean $replaceEntities whether to replace XML entities in content or not
    * @return   string  $string XML tag
    */
    function _createXMLTag($tag, $firstCall = true)
    {
        // build fully qualified tag name
        if ($this->options[XML_SERIALIZER_OPTION_NAMESPACE] !== null) {
        	if (is_array($this->options[XML_SERIALIZER_OPTION_NAMESPACE])) {
        		$tag['qname'] = $this->options[XML_SERIALIZER_OPTION_NAMESPACE][0] . ':' . $tag['qname'];
        	} else {
        		$tag['qname'] = $this->options[XML_SERIALIZER_OPTION_NAMESPACE] . ':' . $tag['qname'];
        	}
        }

        // attribute indentation
        if ($this->options[XML_SERIALIZER_OPTION_INDENT_ATTRIBUTES] !== false) {
            $multiline = true;
            $indent    = str_repeat($this->options[XML_SERIALIZER_OPTION_INDENT], $this->_tagDepth);

            if ($this->options[XML_SERIALIZER_OPTION_INDENT_ATTRIBUTES] == '_auto') {
                $indent .= str_repeat(' ', (strlen($tag['qname'])+2));

            } else {
                $indent .= $this->options[XML_SERIALIZER_OPTION_INDENT_ATTRIBUTES];
            }
        } else {
            $multiline = false;
            $indent    = false;
        }

        if (is_array($tag['content'])) {
            if (empty($tag['content'])) {
                $tag['content'] =   '';
            }
        } elseif(is_scalar($tag['content']) && (string)$tag['content'] == '') {
            $tag['content'] =   '';
        }

        // replace XML entities (only needed, if this is not a nested call)
        if ($firstCall === true) {
            if ($this->options[XML_SERIALIZER_OPTION_CDATA_SECTIONS] === true) {
                $replaceEntities = XML_UTIL_CDATA_SECTION;
            } else {
           	    $replaceEntities = $this->options[XML_SERIALIZER_OPTION_ENTITIES];
            }
        } else {
            $replaceEntities = XML_SERIALIZER_ENTITIES_NONE;
        }
        if (is_scalar($tag['content']) || is_null($tag['content'])) {
            if ($this->options[XML_SERIALIZER_OPTION_ENCODE_FUNC]) {
                if ($firstCall === true) {
                	$tag['content'] = call_user_func($this->options[XML_SERIALIZER_OPTION_ENCODE_FUNC], $tag['content']);
                }
            	$tag['attributes'] = array_map($this->options[XML_SERIALIZER_OPTION_ENCODE_FUNC], $tag['attributes']);
            }
            $tag = XML_Util::createTagFromArray($tag, $replaceEntities, $multiline, $indent, $this->options[XML_SERIALIZER_OPTION_LINEBREAKS]);
        } elseif (is_array($tag['content'])) {
            $tag    =   $this->_serializeArray($tag['content'], $tag['qname'], $tag['attributes']);
        } elseif (is_object($tag['content'])) {
            $tag    =   $this->_serializeObject($tag['content'], $tag['qname'], $tag['attributes']);
        } elseif (is_resource($tag['content'])) {
            settype($tag['content'], 'string');
            if ($this->options[XML_SERIALIZER_OPTION_ENCODE_FUNC]) {
            	if ($replaceEntities === true) {
                    $tag['content'] = call_user_func($this->options[XML_SERIALIZER_OPTION_ENCODE_FUNC], $tag['content']);
            	}
            	$tag['attributes'] = array_map($this->options[XML_SERIALIZER_OPTION_ENCODE_FUNC], $tag['attributes']);
            }
            $tag = XML_Util::createTagFromArray($tag, $replaceEntities);
        }
        return  $tag;
    }
}
?>