<?php
/**
 *  base include file for SimpleTest
 *  @package    SimpleTest
 *  @subpackage UnitTester
 *  @version    $Id: xml.php 1723 2008-04-08 00:34:10Z lastcraft $
 */

/**#@+
 *  include other SimpleTest class files
 */
require_once(dirname(__FILE__) . '/scorer.php');
/**#@-*/

/**
 *    Creates the XML needed for remote communication
 *    by SimpleTest.
 *    @package SimpleTest
 *    @subpackage UnitTester
 */
class XmlReporter extends SimpleReporter {
    var $_indent;
    var $_namespace;

    /**
     *    Sets up indentation and namespace.
     *    @param string $namespace        Namespace to add to each tag.
     *    @param string $indent           Indenting to add on each nesting.
     *    @access public
     */
    function XmlReporter($namespace = false, $indent = '  ') {
        $this->SimpleReporter();
        $this->_namespace = ($namespace ? $namespace . ':' : '');
        $this->_indent = $indent;
    }

    /**
     *    Calculates the pretty printing indent level
     *    from the current level of nesting.
     *    @param integer $offset  Extra indenting level.
     *    @return string          Leading space.
     *    @access protected
     */
    function _getIndent($offset = 0) {
        return str_repeat(
                $this->_indent,
                count($this->getTestList()) + $offset);
    }

    /**
     *    Converts character string to parsed XML
     *    entities string.
     *    @param string text        Unparsed character data.
     *    @return string            Parsed character data.
     *    @access public
     */
    function toParsedXml($text) {
        return str_replace(
                array('&', '<', '>', '"', '\''),
                array('&amp;', '&lt;', '&gt;', '&quot;', '&apos;'),
                $text);
    }

    /**
     *    Paints the start of a group test.
     *    @param string $test_name   Name of test that is starting.
     *    @param integer $size       Number of test cases starting.
     *    @access public
     */
    function paintGroupStart($test_name, $size) {
        parent::paintGroupStart($test_name, $size);
        print $this->_getIndent();
        print "<" . $this->_namespace . "group size=\"$size\">\n";
        print $this->_getIndent(1);
        print "<" . $this->_namespace . "name>" .
                $this->toParsedXml($test_name) .
                "</" . $this->_namespace . "name>\n";
    }

    /**
     *    Paints the end of a group test.
     *    @param string $test_name   Name of test that is ending.
     *    @access public
     */
    function paintGroupEnd($test_name) {
        print $this->_getIndent();
        print "</" . $this->_namespace . "group>\n";
        parent::paintGroupEnd($test_name);
    }

    /**
     *    Paints the start of a test case.
     *    @param string $test_name   Name of test that is starting.
     *    @access public
     */
    function paintCaseStart($test_name) {
        parent::paintCaseStart($test_name);
        print $this->_getIndent();
        print "<" . $this->_namespace . "case>\n";
        print $this->_getIndent(1);
        print "<" . $this->_namespace . "name>" .
                $this->toParsedXml($test_name) .
                "</" . $this->_namespace . "name>\n";
    }

    /**
     *    Paints the end of a test case.
     *    @param string $test_name   Name of test that is ending.
     *    @access public
     */
    function paintCaseEnd($test_name) {
        print $this->_getIndent();
        print "</" . $this->_namespace . "case>\n";
        parent::paintCaseEnd($test_name);
    }

    /**
     *    Paints the start of a test method.
     *    @param string $test_name   Name of test that is starting.
     *    @access public
     */
    function paintMethodStart($test_name) {
        parent::paintMethodStart($test_name);
        print $this->_getIndent();
        print "<" . $this->_namespace . "test>\n";
        print $this->_getIndent(1);
        print "<" . $this->_namespace . "name>" .
                $this->toParsedXml($test_name) .
                "</" . $this->_namespace . "name>\n";
    }

    /**
     *    Paints the end of a test method.
     *    @param string $test_name   Name of test that is ending.
     *    @param integer $progress   Number of test cases ending.
     *    @access public
     */
    function paintMethodEnd($test_name) {
        print $this->_getIndent();
        print "</" . $this->_namespace . "test>\n";
        parent::paintMethodEnd($test_name);
    }

    /**
     *    Paints pass as XML.
     *    @param string $message        Message to encode.
     *    @access public
     */
    function paintPass($message) {
        parent::paintPass($message);
        print $this->_getIndent(1);
        print "<" . $this->_namespace . "pass>";
        print $this->toParsedXml($message);
        print "</" . $this->_namespace . "pass>\n";
    }

    /**
     *    Paints failure as XML.
     *    @param string $message        Message to encode.
     *    @access public
     */
    function paintFail($message) {
        parent::paintFail($message);
        print $this->_getIndent(1);
        print "<" . $this->_namespace . "fail>";
        print $this->toParsedXml($message);
        print "</" . $this->_namespace . "fail>\n";
    }

    /**
     *    Paints error as XML.
     *    @param string $message        Message to encode.
     *    @access public
     */
    function paintError($message) {
        parent::paintError($message);
        print $this->_getIndent(1);
        print "<" . $this->_namespace . "exception>";
        print $this->toParsedXml($message);
        print "</" . $this->_namespace . "exception>\n";
    }

    /**
     *    Paints exception as XML.
     *    @param Exception $exception    Exception to encode.
     *    @access public
     */
    function paintException($exception) {
        parent::paintException($exception);
        print $this->_getIndent(1);
        print "<" . $this->_namespace . "exception>";
        $message = 'Unexpected exception of type [' . get_class($exception) .
                '] with message ['. $exception->getMessage() .
                '] in ['. $exception->getFile() .
                ' line ' . $exception->getLine() . ']';
        print $this->toParsedXml($message);
        print "</" . $this->_namespace . "exception>\n";
    }

    /**
     *    Paints the skipping message and tag.
     *    @param string $message        Text to display in skip tag.
     *    @access public
     */
    function paintSkip($message) {
        parent::paintSkip($message);
        print $this->_getIndent(1);
        print "<" . $this->_namespace . "skip>";
        print $this->toParsedXml($message);
        print "</" . $this->_namespace . "skip>\n";
    }

    /**
     *    Paints a simple supplementary message.
     *    @param string $message        Text to display.
     *    @access public
     */
    function paintMessage($message) {
        parent::paintMessage($message);
        print $this->_getIndent(1);
        print "<" . $this->_namespace . "message>";
        print $this->toParsedXml($message);
        print "</" . $this->_namespace . "message>\n";
    }

    /**
     *    Paints a formatted ASCII message such as a
     *    variable dump.
     *    @param string $message        Text to display.
     *    @access public
     */
    function paintFormattedMessage($message) {
        parent::paintFormattedMessage($message);
        print $this->_getIndent(1);
        print "<" . $this->_namespace . "formatted>";
        print "<![CDATA[$message]]>";
        print "</" . $this->_namespace . "formatted>\n";
    }

    /**
     *    Serialises the event object.
     *    @param string $type        Event type as text.
     *    @param mixed $payload      Message or object.
     *    @access public
     */
    function paintSignal($type, $payload) {
        parent::paintSignal($type, $payload);
        print $this->_getIndent(1);
        print "<" . $this->_namespace . "signal type=\"$type\">";
        print "<![CDATA[" . serialize($payload) . "]]>";
        print "</" . $this->_namespace . "signal>\n";
    }

    /**
     *    Paints the test document header.
     *    @param string $test_name     First test top level
     *                                 to start.
     *    @access public
     *    @abstract
     */
    function paintHeader($test_name) {
        if (! SimpleReporter::inCli()) {
            header('Content-type: text/xml');
        }
        print "<?xml version=\"1.0\"";
        if ($this->_namespace) {
            print " xmlns:" . $this->_namespace .
                    "=\"www.lastcraft.com/SimpleTest/Beta3/Report\"";
        }
        print "?>\n";
        print "<" . $this->_namespace . "run>\n";
    }

    /**
     *    Paints the test document footer.
     *    @param string $test_name        The top level test.
     *    @access public
     *    @abstract
     */
    function paintFooter($test_name) {
        print "</" . $this->_namespace . "run>\n";
    }
}

/**
 *    Accumulator for incoming tag. Holds the
 *    incoming test structure information for
 *    later dispatch to the reporter.
 *    @package SimpleTest
 *    @subpackage UnitTester
 */
class NestingXmlTag {
    var $_name;
    var $_attributes;

    /**
     *    Sets the basic test information except
     *    the name.
     *    @param hash $attributes   Name value pairs.
     *    @access public
     */
    function NestingXmlTag($attributes) {
        $this->_name = false;
        $this->_attributes = $attributes;
    }

    /**
     *    Sets the test case/method name.
     *    @param string $name        Name of test.
     *    @access public
     */
    function setName($name) {
        $this->_name = $name;
    }

    /**
     *    Accessor for name.
     *    @return string        Name of test.
     *    @access public
     */
    function getName() {
        return $this->_name;
    }

    /**
     *    Accessor for attributes.
     *    @return hash        All attributes.
     *    @access protected
     */
    function _getAttributes() {
        return $this->_attributes;
    }
}

/**
 *    Accumulator for incoming method tag. Holds the
 *    incoming test structure information for
 *    later dispatch to the reporter.
 *    @package SimpleTest
 *    @subpackage UnitTester
 */
class NestingMethodTag extends NestingXmlTag {

    /**
     *    Sets the basic test information except
     *    the name.
     *    @param hash $attributes   Name value pairs.
     *    @access public
     */
    function NestingMethodTag($attributes) {
        $this->NestingXmlTag($attributes);
    }

    /**
     *    Signals the appropriate start event on the
     *    listener.
     *    @param SimpleReporter $listener    Target for events.
     *    @access public
     */
    function paintStart(&$listener) {
        $listener->paintMethodStart($this->getName());
    }

    /**
     *    Signals the appropriate end event on the
     *    listener.
     *    @param SimpleReporter $listener    Target for events.
     *    @access public
     */
    function paintEnd(&$listener) {
        $listener->paintMethodEnd($this->getName());
    }
}

/**
 *    Accumulator for incoming case tag. Holds the
 *    incoming test structure information for
 *    later dispatch to the reporter.
 *    @package SimpleTest
 *    @subpackage UnitTester
 */
class NestingCaseTag extends NestingXmlTag {

    /**
     *    Sets the basic test information except
     *    the name.
     *    @param hash $attributes   Name value pairs.
     *    @access public
     */
    function NestingCaseTag($attributes) {
        $this->NestingXmlTag($attributes);
    }

    /**
     *    Signals the appropriate start event on the
     *    listener.
     *    @param SimpleReporter $listener    Target for events.
     *    @access public
     */
    function paintStart(&$listener) {
        $listener->paintCaseStart($this->getName());
    }

    /**
     *    Signals the appropriate end event on the
     *    listener.
     *    @param SimpleReporter $listener    Target for events.
     *    @access public
     */
    function paintEnd(&$listener) {
        $listener->paintCaseEnd($this->getName());
    }
}

/**
 *    Accumulator for incoming group tag. Holds the
 *    incoming test structure information for
 *    later dispatch to the reporter.
 *    @package SimpleTest
 *    @subpackage UnitTester
 */
class NestingGroupTag extends NestingXmlTag {

    /**
     *    Sets the basic test information except
     *    the name.
     *    @param hash $attributes   Name value pairs.
     *    @access public
     */
    function NestingGroupTag($attributes) {
        $this->NestingXmlTag($attributes);
    }

    /**
     *    Signals the appropriate start event on the
     *    listener.
     *    @param SimpleReporter $listener    Target for events.
     *    @access public
     */
    function paintStart(&$listener) {
        $listener->paintGroupStart($this->getName(), $this->getSize());
    }

    /**
     *    Signals the appropriate end event on the
     *    listener.
     *    @param SimpleReporter $listener    Target for events.
     *    @access public
     */
    function paintEnd(&$listener) {
        $listener->paintGroupEnd($this->getName());
    }

    /**
     *    The size in the attributes.
     *    @return integer     Value of size attribute or zero.
     *    @access public
     */
    function getSize() {
        $attributes = $this->_getAttributes();
        if (isset($attributes['SIZE'])) {
            return (integer)$attributes['SIZE'];
        }
        return 0;
    }
}

/**
 *    Parser for importing the output of the XmlReporter.
 *    Dispatches that output to another reporter.
 *    @package SimpleTest
 *    @subpackage UnitTester
 */
class SimpleTestXmlParser {
    var $_listener;
    var $_expat;
    var $_tag_stack;
    var $_in_content_tag;
    var $_content;
    var $_attributes;

    /**
     *    Loads a listener with the SimpleReporter
     *    interface.
     *    @param SimpleReporter $listener   Listener of tag events.
     *    @access public
     */
    function SimpleTestXmlParser(&$listener) {
        $this->_listener = &$listener;
        $this->_expat = &$this->_createParser();
        $this->_tag_stack = array();
        $this->_in_content_tag = false;
        $this->_content = '';
        $this->_attributes = array();
    }

    /**
     *    Parses a block of XML sending the results to
     *    the listener.
     *    @param string $chunk        Block of text to read.
     *    @return boolean             True if valid XML.
     *    @access public
     */
    function parse($chunk) {
        if (! xml_parse($this->_expat, $chunk)) {
            trigger_error('XML parse error with ' .
                    xml_error_string(xml_get_error_code($this->_expat)));
            return false;
        }
        return true;
    }

    /**
     *    Sets up expat as the XML parser.
     *    @return resource        Expat handle.
     *    @access protected
     */
    function &_createParser() {
        $expat = xml_parser_create();
        xml_set_object($expat, $this);
        xml_set_element_handler($expat, '_startElement', '_endElement');
        xml_set_character_data_handler($expat, '_addContent');
        xml_set_default_handler($expat, '_default');
        return $expat;
    }

    /**
     *    Opens a new test nesting level.
     *    @return NestedXmlTag     The group, case or method tag
     *                             to start.
     *    @access private
     */
    function _pushNestingTag($nested) {
        array_unshift($this->_tag_stack, $nested);
    }

    /**
     *    Accessor for current test structure tag.
     *    @return NestedXmlTag     The group, case or method tag
     *                             being parsed.
     *    @access private
     */
    function &_getCurrentNestingTag() {
        return $this->_tag_stack[0];
    }

    /**
     *    Ends a nesting tag.
     *    @return NestedXmlTag     The group, case or method tag
     *                             just finished.
     *    @access private
     */
    function _popNestingTag() {
        return array_shift($this->_tag_stack);
    }

    /**
     *    Test if tag is a leaf node with only text content.
     *    @param string $tag        XML tag name.
     *    @return @boolean          True if leaf, false if nesting.
     *    @private
     */
    function _isLeaf($tag) {
        return in_array($tag, array(
                'NAME', 'PASS', 'FAIL', 'EXCEPTION', 'SKIP', 'MESSAGE', 'FORMATTED', 'SIGNAL'));
    }

    /**
     *    Handler for start of event element.
     *    @param resource $expat     Parser handle.
     *    @param string $tag         Element name.
     *    @param hash $attributes    Name value pairs.
     *                               Attributes without content
     *                               are marked as true.
     *    @access protected
     */
    function _startElement($expat, $tag, $attributes) {
        $this->_attributes = $attributes;
        if ($tag == 'GROUP') {
            $this->_pushNestingTag(new NestingGroupTag($attributes));
        } elseif ($tag == 'CASE') {
            $this->_pushNestingTag(new NestingCaseTag($attributes));
        } elseif ($tag == 'TEST') {
            $this->_pushNestingTag(new NestingMethodTag($attributes));
        } elseif ($this->_isLeaf($tag)) {
            $this->_in_content_tag = true;
            $this->_content = '';
        }
    }

    /**
     *    End of element event.
     *    @param resource $expat     Parser handle.
     *    @param string $tag         Element name.
     *    @access protected
     */
    function _endElement($expat, $tag) {
        $this->_in_content_tag = false;
        if (in_array($tag, array('GROUP', 'CASE', 'TEST'))) {
            $nesting_tag = $this->_popNestingTag();
            $nesting_tag->paintEnd($this->_listener);
        } elseif ($tag == 'NAME') {
            $nesting_tag = &$this->_getCurrentNestingTag();
            $nesting_tag->setName($this->_content);
            $nesting_tag->paintStart($this->_listener);
        } elseif ($tag == 'PASS') {
            $this->_listener->paintPass($this->_content);
        } elseif ($tag == 'FAIL') {
            $this->_listener->paintFail($this->_content);
        } elseif ($tag == 'EXCEPTION') {
            $this->_listener->paintError($this->_content);
        } elseif ($tag == 'SKIP') {
            $this->_listener->paintSkip($this->_content);
        } elseif ($tag == 'SIGNAL') {
            $this->_listener->paintSignal(
                    $this->_attributes['TYPE'],
                    unserialize($this->_content));
        } elseif ($tag == 'MESSAGE') {
            $this->_listener->paintMessage($this->_content);
        } elseif ($tag == 'FORMATTED') {
            $this->_listener->paintFormattedMessage($this->_content);
        }
    }

    /**
     *    Content between start and end elements.
     *    @param resource $expat     Parser handle.
     *    @param string $text        Usually output messages.
     *    @access protected
     */
    function _addContent($expat, $text) {
        if ($this->_in_content_tag) {
            $this->_content .= $text;
        }
        return true;
    }

    /**
     *    XML and Doctype handler. Discards all such content.
     *    @param resource $expat     Parser handle.
     *    @param string $default     Text of default content.
     *    @access protected
     */
    function _default($expat, $default) {
    }
}
?>
