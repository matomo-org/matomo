<?PHP
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | PHP Version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2002 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the PHP license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/2_02.txt.                                 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Authors: Stephan Schmidt <schst@php-tools.net>                       |
// +----------------------------------------------------------------------+
//
//    $Id$

/**
 * error code for invalid chars in XML name
 */
define("XML_UTIL_ERROR_INVALID_CHARS", 51);

/**
 * error code for invalid chars in XML name
 */
define("XML_UTIL_ERROR_INVALID_START", 52);

/**
 * error code for non-scalar tag content
 */
define("XML_UTIL_ERROR_NON_SCALAR_CONTENT", 60);

/**
 * error code for missing tag name
 */
define("XML_UTIL_ERROR_NO_TAG_NAME", 61);

/**
 * replace XML entities
 */
define("XML_UTIL_REPLACE_ENTITIES", 1);

/**
 * embedd content in a CData Section
 */
define("XML_UTIL_CDATA_SECTION", 5);

/**
 * do not replace entitites
 */
define("XML_UTIL_ENTITIES_NONE", 0);

/**
 * replace all XML entitites
 * This setting will replace <, >, ", ' and &
 */
define("XML_UTIL_ENTITIES_XML", 1);

/**
 * replace only required XML entitites
 * This setting will replace <, " and &
 */
define("XML_UTIL_ENTITIES_XML_REQUIRED", 2);

/**
 * replace HTML entitites
 * @link    http://www.php.net/htmlentities
 */
define("XML_UTIL_ENTITIES_HTML", 3);

/**
 * Collapse all empty tags.
 */
define("XML_UTIL_COLLAPSE_ALL", 1);

/**
 * Collapse only empty XHTML tags that have no end tag.
 */
define("XML_UTIL_COLLAPSE_XHTML_ONLY", 2);

/**
 * utility class for working with XML documents
 *
 * @category XML
 * @package  XML_Util
 * @version  1.1.0
 * @author   Stephan Schmidt <schst@php.net>
 */
class XML_Util {

   /**
    * return API version
    *
    * @access   public
    * @static
    * @return   string  $version API version
    */
    static function apiVersion()
    {
        return '1.1';
    }

   /**
    * replace XML entities
    *
    * With the optional second parameter, you may select, which
    * entities should be replaced.
    *
    * <code>
    * require_once 'XML/Util.php';
    *
    * // replace XML entites:
    * $string = XML_Util::replaceEntities("This string contains < & >.");
    * </code>
    *
    * @access   public
    * @static
    * @param    string  string where XML special chars should be replaced
    * @param    integer setting for entities in attribute values (one of XML_UTIL_ENTITIES_XML, XML_UTIL_ENTITIES_XML_REQUIRED, XML_UTIL_ENTITIES_HTML)
    * @return   string  string with replaced chars
    * @see      reverseEntities()
    */
    static function replaceEntities($string, $replaceEntities = XML_UTIL_ENTITIES_XML)
    {
        switch ($replaceEntities) {
            case XML_UTIL_ENTITIES_XML:
                return strtr($string,array(
                                          '&'  => '&amp;',
                                          '>'  => '&gt;',
                                          '<'  => '&lt;',
                                          '"'  => '&quot;',
                                          '\'' => '&apos;' ));
                break;
            case XML_UTIL_ENTITIES_XML_REQUIRED:
                return strtr($string,array(
                                          '&'  => '&amp;',
                                          '<'  => '&lt;',
                                          '"'  => '&quot;' ));
                break;
            case XML_UTIL_ENTITIES_HTML:
                return htmlentities($string);
                break;
        }
        return $string;
    }

   /**
    * reverse XML entities
    *
    * With the optional second parameter, you may select, which
    * entities should be reversed.
    *
    * <code>
    * require_once 'XML/Util.php';
    *
    * // reverse XML entites:
    * $string = XML_Util::reverseEntities("This string contains &lt; &amp; &gt;.");
    * </code>
    *
    * @access   public
    * @static
    * @param    string  string where XML special chars should be replaced
    * @param    integer setting for entities in attribute values (one of XML_UTIL_ENTITIES_XML, XML_UTIL_ENTITIES_XML_REQUIRED, XML_UTIL_ENTITIES_HTML)
    * @return   string  string with replaced chars
    * @see      replaceEntities()
    */
    static function reverseEntities($string, $replaceEntities = XML_UTIL_ENTITIES_XML)
    {
        switch ($replaceEntities) {
            case XML_UTIL_ENTITIES_XML:
                return strtr($string,array(
                                          '&amp;'  => '&',
                                          '&gt;'   => '>',
                                          '&lt;'   => '<',
                                          '&quot;' => '"',
                                          '&apos;' => '\'' ));
                break;
            case XML_UTIL_ENTITIES_XML_REQUIRED:
                return strtr($string,array(
                                          '&amp;'  => '&',
                                          '&lt;'   => '<',
                                          '&quot;' => '"' ));
                break;
            case XML_UTIL_ENTITIES_HTML:
                $arr = array_flip(get_html_translation_table(HTML_ENTITIES));
                return strtr($string, $arr);
                break;
        }
        return $string;
    }

   /**
    * build an xml declaration
    *
    * <code>
    * require_once 'XML/Util.php';
    *
    * // get an XML declaration:
    * $xmlDecl = XML_Util::getXMLDeclaration("1.0", "UTF-8", true);
    * </code>
    *
    * @access   public
    * @static
    * @param    string  $version     xml version
    * @param    string  $encoding    character encoding
    * @param    boolean $standAlone  document is standalone (or not)
    * @return   string  $decl xml declaration
    * @uses     XML_Util::attributesToString() to serialize the attributes of the XML declaration
    */
    static function getXMLDeclaration($version = "1.0", $encoding = null, $standalone = null)
    {
        $attributes = array(
                            "version" => $version,
                           );
        // add encoding
        if ($encoding !== null) {
            $attributes["encoding"] = $encoding;
        }
        // add standalone, if specified
        if ($standalone !== null) {
            $attributes["standalone"] = $standalone ? "yes" : "no";
        }

        return sprintf("<?xml%s?>", XML_Util::attributesToString($attributes, false));
    }

   /**
    * build a document type declaration
    *
    * <code>
    * require_once 'XML/Util.php';
    *
    * // get a doctype declaration:
    * $xmlDecl = XML_Util::getDocTypeDeclaration("rootTag","myDocType.dtd");
    * </code>
    *
    * @access   public
    * @static
    * @param    string  $root         name of the root tag
    * @param    string  $uri          uri of the doctype definition (or array with uri and public id)
    * @param    string  $internalDtd  internal dtd entries
    * @return   string  $decl         doctype declaration
    * @since    0.2
    */
    static function getDocTypeDeclaration($root, $uri = null, $internalDtd = null)
    {
        if (is_array($uri)) {
            $ref = sprintf( ' PUBLIC "%s" "%s"', $uri["id"], $uri["uri"] );
        } elseif (!empty($uri)) {
            $ref = sprintf( ' SYSTEM "%s"', $uri );
        } else {
            $ref = "";
        }

        if (empty($internalDtd)) {
            return sprintf("<!DOCTYPE %s%s>", $root, $ref);
        } else {
            return sprintf("<!DOCTYPE %s%s [\n%s\n]>", $root, $ref, $internalDtd);
        }
    }

   /**
    * create string representation of an attribute list
    *
    * <code>
    * require_once 'XML/Util.php';
    *
    * // build an attribute string
    * $att = array(
    *              "foo"   =>  "bar",
    *              "argh"  =>  "tomato"
    *            );
    *
    * $attList = XML_Util::attributesToString($att);
    * </code>
    *
    * @access   public
    * @static
    * @param    array         $attributes        attribute array
    * @param    boolean|array $sort              sort attribute list alphabetically, may also be an assoc array containing the keys 'sort', 'multiline', 'indent', 'linebreak' and 'entities'
    * @param    boolean       $multiline         use linebreaks, if more than one attribute is given
    * @param    string        $indent            string used for indentation of multiline attributes
    * @param    string        $linebreak         string used for linebreaks of multiline attributes
    * @param    integer       $entities          setting for entities in attribute values (one of XML_UTIL_ENTITIES_NONE, XML_UTIL_ENTITIES_XML, XML_UTIL_ENTITIES_XML_REQUIRED, XML_UTIL_ENTITIES_HTML)
    * @return   string                           string representation of the attributes
    * @uses     XML_Util::replaceEntities() to replace XML entities in attribute values
    * @todo     allow sort also to be an options array
    */
    static function attributesToString($attributes, $sort = true, $multiline = false, $indent = '    ', $linebreak = "\n", $entities = XML_UTIL_ENTITIES_XML)
    {
        /**
         * second parameter may be an array
         */
        if (is_array($sort)) {
            if (isset($sort['multiline'])) {
                $multiline = $sort['multiline'];
            }
            if (isset($sort['indent'])) {
                $indent = $sort['indent'];
            }
            if (isset($sort['linebreak'])) {
                $multiline = $sort['linebreak'];
            }
            if (isset($sort['entities'])) {
                $entities = $sort['entities'];
            }
            if (isset($sort['sort'])) {
                $sort = $sort['sort'];
            } else {
                $sort = true;
            }
        }
        $string = '';
        if (is_array($attributes) && !empty($attributes)) {
            if ($sort) {
                ksort($attributes);
            }
            if( !$multiline || count($attributes) == 1) {
                foreach ($attributes as $key => $value) {
                    if ($entities != XML_UTIL_ENTITIES_NONE) {
                        if ($entities === XML_UTIL_CDATA_SECTION) {
                        	$entities = XML_UTIL_ENTITIES_XML;
                        }
                        $value = XML_Util::replaceEntities($value, $entities);
                    }
                    $string .= ' '.$key.'="'.$value.'"';
                }
            } else {
                $first = true;
                foreach ($attributes as $key => $value) {
                    if ($entities != XML_UTIL_ENTITIES_NONE) {
                        $value = XML_Util::replaceEntities($value, $entities);
                    }
                    if ($first) {
                        $string .= " ".$key.'="'.$value.'"';
                        $first = false;
                    } else {
                        $string .= $linebreak.$indent.$key.'="'.$value.'"';
                    }
                }
            }
        }
        return $string;
    }

   /**
    * Collapses empty tags.
    *
    * @access   public
    * @static
    * @param    string  $xml  XML
    * @param    integer $mode Whether to collapse all empty tags (XML_UTIL_COLLAPSE_ALL) or only XHTML (XML_UTIL_COLLAPSE_XHTML_ONLY) ones.
    * @return   string  $xml  XML
    */
    static function collapseEmptyTags($xml, $mode = XML_UTIL_COLLAPSE_ALL) {
        if ($mode == XML_UTIL_COLLAPSE_XHTML_ONLY) {
            return preg_replace(
              '/<(area|base|br|col|hr|img|input|link|meta|param)([^>]*)><\/\\1>/s',
              '<\\1\\2 />',
              $xml
            );
        } else {
            return preg_replace(
              '/<(\w+)([^>]*)><\/\\1>/s',
              '<\\1\\2 />',
              $xml
            );
        }
    }

   /**
    * create a tag
    *
    * This method will call XML_Util::createTagFromArray(), which
    * is more flexible.
    *
    * <code>
    * require_once 'XML/Util.php';
    *
    * // create an XML tag:
    * $tag = XML_Util::createTag("myNs:myTag", array("foo" => "bar"), "This is inside the tag", "http://www.w3c.org/myNs#");
    * </code>
    *
    * @access   public
    * @static
    * @param    string  $qname             qualified tagname (including namespace)
    * @param    array   $attributes        array containg attributes
    * @param    mixed   $content
    * @param    string  $namespaceUri      URI of the namespace
    * @param    integer $replaceEntities   whether to replace XML special chars in content, embedd it in a CData section or none of both
    * @param    boolean $multiline         whether to create a multiline tag where each attribute gets written to a single line
    * @param    string  $indent            string used to indent attributes (_auto indents attributes so they start at the same column)
    * @param    string  $linebreak         string used for linebreaks
    * @param    boolean $sortAttributes    Whether to sort the attributes or not
    * @return   string  $string            XML tag
    * @see      XML_Util::createTagFromArray()
    * @uses     XML_Util::createTagFromArray() to create the tag
    */
    static function createTag($qname, $attributes = array(), $content = null, $namespaceUri = null, $replaceEntities = XML_UTIL_REPLACE_ENTITIES, $multiline = false, $indent = "_auto", $linebreak = "\n", $sortAttributes = true)
    {
        $tag = array(
                     "qname"      => $qname,
                     "attributes" => $attributes
                    );

        // add tag content
        if ($content !== null) {
            $tag["content"] = $content;
        }

        // add namespace Uri
        if ($namespaceUri !== null) {
            $tag["namespaceUri"] = $namespaceUri;
        }

        return XML_Util::createTagFromArray($tag, $replaceEntities, $multiline, $indent, $linebreak, $sortAttributes);
    }

   /**
    * create a tag from an array
    * this method awaits an array in the following format
    * <pre>
    * array(
    *  "qname"        => $qname         // qualified name of the tag
    *  "namespace"    => $namespace     // namespace prefix (optional, if qname is specified or no namespace)
    *  "localpart"    => $localpart,    // local part of the tagname (optional, if qname is specified)
    *  "attributes"   => array(),       // array containing all attributes (optional)
    *  "content"      => $content,      // tag content (optional)
    *  "namespaceUri" => $namespaceUri  // namespaceUri for the given namespace (optional)
    *   )
    * </pre>
    *
    * <code>
    * require_once 'XML/Util.php';
    *
    * $tag = array(
    *           "qname"        => "foo:bar",
    *           "namespaceUri" => "http://foo.com",
    *           "attributes"   => array( "key" => "value", "argh" => "fruit&vegetable" ),
    *           "content"      => "I'm inside the tag",
    *            );
    * // creating a tag with qualified name and namespaceUri
    * $string = XML_Util::createTagFromArray($tag);
    * </code>
    *
    * @access   public
    * @static
    * @param    array   $tag               tag definition
    * @param    integer $replaceEntities   whether to replace XML special chars in content, embedd it in a CData section or none of both
    * @param    boolean $multiline         whether to create a multiline tag where each attribute gets written to a single line
    * @param    string  $indent            string used to indent attributes (_auto indents attributes so they start at the same column)
    * @param    string  $linebreak         string used for linebreaks
    * @param    boolean $sortAttributes    Whether to sort the attributes or not
    * @return   string  $string            XML tag
    * @see      XML_Util::createTag()
    * @uses     XML_Util::attributesToString() to serialize the attributes of the tag
    * @uses     XML_Util::splitQualifiedName() to get local part and namespace of a qualified name
    */
    static function createTagFromArray($tag, $replaceEntities = XML_UTIL_REPLACE_ENTITIES, $multiline = false, $indent = "_auto", $linebreak = "\n", $sortAttributes = true)
    {
        if (isset($tag['content']) && !is_scalar($tag['content'])) {
            return XML_Util::raiseError( 'Supplied non-scalar value as tag content', XML_UTIL_ERROR_NON_SCALAR_CONTENT );
        }

        if (!isset($tag['qname']) && !isset($tag['localPart'])) {
            return XML_Util::raiseError( 'You must either supply a qualified name (qname) or local tag name (localPart).', XML_UTIL_ERROR_NO_TAG_NAME );
        }

        // if no attributes hav been set, use empty attributes
        if (!isset($tag["attributes"]) || !is_array($tag["attributes"])) {
            $tag["attributes"] = array();
        }

        if (isset($tag['namespaces'])) {
        	foreach ($tag['namespaces'] as $ns => $uri) {
                $tag['attributes']['xmlns:'.$ns] = $uri;
        	}
        }

        // qualified name is not given
        if (!isset($tag["qname"])) {
            // check for namespace
            if (isset($tag["namespace"]) && !empty($tag["namespace"])) {
                $tag["qname"] = $tag["namespace"].":".$tag["localPart"];
            } else {
                $tag["qname"] = $tag["localPart"];
            }
        // namespace URI is set, but no namespace
        } elseif (isset($tag["namespaceUri"]) && !isset($tag["namespace"])) {
            $parts = XML_Util::splitQualifiedName($tag["qname"]);
            $tag["localPart"] = $parts["localPart"];
            if (isset($parts["namespace"])) {
                $tag["namespace"] = $parts["namespace"];
            }
        }

        if (isset($tag["namespaceUri"]) && !empty($tag["namespaceUri"])) {
            // is a namespace given
            if (isset($tag["namespace"]) && !empty($tag["namespace"])) {
                $tag["attributes"]["xmlns:".$tag["namespace"]] = $tag["namespaceUri"];
            } else {
                // define this Uri as the default namespace
                $tag["attributes"]["xmlns"] = $tag["namespaceUri"];
            }
        }

        // check for multiline attributes
        if ($multiline === true) {
            if ($indent === "_auto") {
                $indent = str_repeat(" ", (strlen($tag["qname"])+2));
            }
        }

        // create attribute list
        $attList    =   XML_Util::attributesToString($tag['attributes'], $sortAttributes, $multiline, $indent, $linebreak, $replaceEntities );
        if (!isset($tag['content']) || (string)$tag['content'] == '') {
            $tag    =   sprintf('<%s%s />', $tag['qname'], $attList);
        } else {
            switch ($replaceEntities) {
                case XML_UTIL_ENTITIES_NONE:
                    break;
                case XML_UTIL_CDATA_SECTION:
                    $tag['content'] = XML_Util::createCDataSection($tag['content']);
                    break;
                default:
                    $tag['content'] = XML_Util::replaceEntities($tag['content'], $replaceEntities);
                    break;
            }
            $tag    =   sprintf('<%s%s>%s</%s>', $tag['qname'], $attList, $tag['content'], $tag['qname'] );
        }
        return  $tag;
    }

   /**
    * create a start element
    *
    * <code>
    * require_once 'XML/Util.php';
    *
    * // create an XML start element:
    * $tag = XML_Util::createStartElement("myNs:myTag", array("foo" => "bar") ,"http://www.w3c.org/myNs#");
    * </code>
    *
    * @access   public
    * @static
    * @param    string  $qname             qualified tagname (including namespace)
    * @param    array   $attributes        array containg attributes
    * @param    string  $namespaceUri      URI of the namespace
    * @param    boolean $multiline         whether to create a multiline tag where each attribute gets written to a single line
    * @param    string  $indent            string used to indent attributes (_auto indents attributes so they start at the same column)
    * @param    string  $linebreak         string used for linebreaks
    * @param    boolean $sortAttributes    Whether to sort the attributes or not
    * @return   string  $string            XML start element
    * @see      XML_Util::createEndElement(), XML_Util::createTag()
    */
    static function createStartElement($qname, $attributes = array(), $namespaceUri = null, $multiline = false, $indent = '_auto', $linebreak = "\n", $sortAttributes = true)
    {
        // if no attributes hav been set, use empty attributes
        if (!isset($attributes) || !is_array($attributes)) {
            $attributes = array();
        }

        if ($namespaceUri != null) {
            $parts = XML_Util::splitQualifiedName($qname);
        }

        // check for multiline attributes
        if ($multiline === true) {
            if ($indent === "_auto") {
                $indent = str_repeat(" ", (strlen($qname)+2));
            }
        }

        if ($namespaceUri != null) {
            // is a namespace given
            if (isset($parts["namespace"]) && !empty($parts["namespace"])) {
                $attributes["xmlns:".$parts["namespace"]] = $namespaceUri;
            } else {
                // define this Uri as the default namespace
                $attributes["xmlns"] = $namespaceUri;
            }
        }

        // create attribute list
        $attList    =   XML_Util::attributesToString($attributes, $sortAttributes, $multiline, $indent, $linebreak);
        $element    =   sprintf("<%s%s>", $qname, $attList);
        return  $element;
    }

   /**
    * create an end element
    *
    * <code>
    * require_once 'XML/Util.php';
    *
    * // create an XML start element:
    * $tag = XML_Util::createEndElement("myNs:myTag");
    * </code>
    *
    * @access   public
    * @static
    * @param    string  $qname             qualified tagname (including namespace)
    * @return   string  $string            XML end element
    * @see      XML_Util::createStartElement(), XML_Util::createTag()
    */
    static function createEndElement($qname)
    {
        $element    =   sprintf("</%s>", $qname);
        return  $element;
    }

   /**
    * create an XML comment
    *
    * <code>
    * require_once 'XML/Util.php';
    *
    * // create an XML start element:
    * $tag = XML_Util::createComment("I am a comment");
    * </code>
    *
    * @access   public
    * @static
    * @param    string  $content           content of the comment
    * @return   string  $comment           XML comment
    */
    static function createComment($content)
    {
        $comment    =   sprintf("<!-- %s -->", $content);
        return  $comment;
    }

   /**
    * create a CData section
    *
    * <code>
    * require_once 'XML/Util.php';
    *
    * // create a CData section
    * $tag = XML_Util::createCDataSection("I am content.");
    * </code>
    *
    * @access   public
    * @static
    * @param    string  $data              data of the CData section
    * @return   string  $string            CData section with content
    */
    static function createCDataSection($data)
    {
        return  sprintf("<![CDATA[%s]]>", $data);
    }

   /**
    * split qualified name and return namespace and local part
    *
    * <code>
    * require_once 'XML/Util.php';
    *
    * // split qualified tag
    * $parts = XML_Util::splitQualifiedName("xslt:stylesheet");
    * </code>
    * the returned array will contain two elements:
    * <pre>
    * array(
    *       "namespace" => "xslt",
    *       "localPart" => "stylesheet"
    *      );
    * </pre>
    *
    * @access public
    * @static
    * @param  string    $qname      qualified tag name
    * @param  string    $defaultNs  default namespace (optional)
    * @return array     $parts      array containing namespace and local part
    */
    static function splitQualifiedName($qname, $defaultNs = null)
    {
        if (strstr($qname, ':')) {
            $tmp = explode(":", $qname);
            return array(
                          "namespace" => $tmp[0],
                          "localPart" => $tmp[1]
                        );
        }
        return array(
                      "namespace" => $defaultNs,
                      "localPart" => $qname
                    );
    }

   /**
    * check, whether string is valid XML name
    *
    * <p>XML names are used for tagname, attribute names and various
    * other, lesser known entities.</p>
    * <p>An XML name may only consist of alphanumeric characters,
    * dashes, undescores and periods, and has to start with a letter
    * or an underscore.
    * </p>
    *
    * <code>
    * require_once 'XML/Util.php';
    *
    * // verify tag name
    * $result = XML_Util::isValidName("invalidTag?");
    * if (XML_Util::isError($result)) {
    *    print "Invalid XML name: " . $result->getMessage();
    * }
    * </code>
    *
    * @access  public
    * @static
    * @param   string  $string string that should be checked
    * @return  mixed   $valid  true, if string is a valid XML name, PEAR error otherwise
    * @todo    support for other charsets
    */
    static function isValidName($string)
    {
        // check for invalid chars
        if (!preg_match('/^[[:alpha:]_]$/', $string{0})) {
            return XML_Util::raiseError('XML names may only start with letter or underscore', XML_UTIL_ERROR_INVALID_START);
        }

        // check for invalid chars
        if (!preg_match('/^([[:alpha:]_]([[:alnum:]\-\.]*)?:)?[[:alpha:]_]([[:alnum:]\_\-\.]+)?$/', $string)) {
            return XML_Util::raiseError('XML names may only contain alphanumeric chars, period, hyphen, colon and underscores', XML_UTIL_ERROR_INVALID_CHARS);
         }
        // XML name is valid
        return true;
    }

   /**
    * replacement for XML_Util::raiseError
    *
    * Avoids the necessity to always require
    * PEAR.php
    *
    * @access   public
    * @param    string      error message
    * @param    integer     error code
    * @return   object PEAR_Error
    */
    static function raiseError($msg, $code)
    {
        require_once 'PEAR.php';
        return PEAR::raiseError($msg, $code);
    }
}
?>