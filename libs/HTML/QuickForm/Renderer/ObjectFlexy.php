<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * QuickForm renderer for Flexy template engine, static version.
 * 
 * PHP versions 4 and 5
 *
 * LICENSE: This source file is subject to version 3.01 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_01.txt If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @category    HTML
 * @package     HTML_QuickForm
 * @author      Ron McClain <ron@humaniq.com>
 * @copyright   2001-2009 The PHP Group
 * @license     http://www.php.net/license/3_01.txt PHP License 3.01
 * @version     CVS: $Id$
 * @link        http://pear.php.net/package/HTML_QuickForm
 */

/**
 * A concrete renderer for HTML_QuickForm, makes an object from form contents
 */ 
require_once 'HTML/QuickForm/Renderer/Object.php';

/**
 * QuickForm renderer for Flexy template engine, static version.
 * 
 * A static renderer for HTML_Quickform.  Makes a QuickFormFlexyObject
 * from the form content suitable for use with a Flexy template
 *
 * Usage:
 * <code>
 * $form =& new HTML_QuickForm('form', 'POST');
 * $template =& new HTML_Template_Flexy();
 * $renderer =& new HTML_QuickForm_Renderer_ObjectFlexy(&$template);
 * $renderer->setHtmlTemplate("html.html");
 * $renderer->setLabelTemplate("label.html");
 * $form->accept($renderer);
 * $view = new StdClass;
 * $view->form = $renderer->toObject();
 * $template->compile("mytemplate.html");
 * </code>
 *
 * Based on the code for HTML_QuickForm_Renderer_ArraySmarty
 *
 * @category    HTML
 * @package     HTML_QuickForm
 * @author      Ron McClain <ron@humaniq.com>
 * @version     Release: 3.2.11
 * @since       3.1.1
 */
class HTML_QuickForm_Renderer_ObjectFlexy extends HTML_QuickForm_Renderer_Object
{
   /**#@+
    * @access private
    */
    /**
     * HTML_Template_Flexy instance
     * @var object $_flexy
     */
    var $_flexy;

    /**
     * Current element index
     * @var integer $_elementIdx
     */
    var $_elementIdx;

    /**
     * The current element index inside a group
     * @var integer $_groupElementIdx
     */
    var $_groupElementIdx = 0;

    /**
     * Name of template file for form html
     * @var string $_html
     * @see     setRequiredTemplate()
     */
    var $_html = '';

    /**
     * Name of template file for form labels
     * @var string $label
     * @see        setErrorTemplate()
     */
    var $label = '';

    /**
     * Class of the element objects, so you can add your own
     * element methods
     * @var string $_elementType
     */
    var $_elementType = 'QuickformFlexyElement';
   /**#@-*/

    /**
     * Constructor
     *
     * @param HTML_Template_Flexy   template object to use
     * @public
     */
    function HTML_QuickForm_Renderer_ObjectFlexy(&$flexy)
    {
        $this->HTML_QuickForm_Renderer_Object(true);
        $this->_obj = new QuickformFlexyForm();
        $this->_flexy =& $flexy;
    } // end constructor

    function renderHeader(&$header)
    {
        if($name = $header->getName()) {
            $this->_obj->header->$name = $header->toHtml();
        } else {
            $this->_obj->header[$this->_sectionCount] = $header->toHtml();
        }
        $this->_currentSection = $this->_sectionCount++;
    } // end func renderHeader

    function startGroup(&$group, $required, $error)
    {
        parent::startGroup($group, $required, $error);
        $this->_groupElementIdx = 1;
    } //end func startGroup

    /**
     * Creates an object representing an element containing
     * the key for storing this
     *
     * @access private
     * @param HTML_QuickForm_element    form element being rendered
     * @param bool        Whether an element is required
     * @param string    Error associated with the element
     * @return object
     */
    function _elementToObject(&$element, $required, $error)
    {
        $ret = parent::_elementToObject($element, $required, $error);
        if($ret->type == 'group') {
            $ret->html = $element->toHtml();
            unset($ret->elements);
        }
        if(!empty($this->_label)) {
            $this->_renderLabel($ret);
        }

        if(!empty($this->_html)) {
            $this->_renderHtml($ret);
            $ret->error = $error;
        }

        // Create an element key from the name
        if (false !== ($pos = strpos($ret->name, '[')) || is_object($this->_currentGroup)) {
            if (!$pos) {
                $keys = '->{\'' . str_replace(array('\\', '\''), array('\\\\', '\\\''), $ret->name) . '\'}';
            } else {
                $keys = '->{\'' . str_replace(
                            array('\\', '\'', '[', ']'), array('\\\\', '\\\'', '\'}->{\'', ''), 
                            $ret->name
                        ) . '\'}';
            }
            // special handling for elements in native groups
            if (is_object($this->_currentGroup)) {
                // skip unnamed group items unless radios: no name -> no static access
                // identification: have the same key string as the parent group
                if ($this->_currentGroup->keys == $keys && 'radio' != $ret->type) {
                    return false;
                }
                // reduce string of keys by remove leading group keys
                if (0 === strpos($keys, $this->_currentGroup->keys)) {
                    $keys = substr_replace($keys, '', 0, strlen($this->_currentGroup->keys));
                }
            }
        } elseif (0 == strlen($ret->name)) {
            $keys = '->{\'element_' . $this->_elementIdx . '\'}';
        } else {
            $keys = '->{\'' . str_replace(array('\\', '\''), array('\\\\', '\\\''), $ret->name) . '\'}';
        }
        // for radios: add extra key from value
        if ('radio' == $ret->type && '[]' != substr($keys, -2)) {
            $keys .= '->{\'' . str_replace(array('\\', '\''), array('\\\\', '\\\''), $ret->value) . '\'}';
        }
        $ret->keys = $keys;
        $this->_elementIdx++;
        return $ret;
    }

    /**
     * Stores an object representation of an element in the 
     * QuickformFormObject instance
     *
     * @access private
     * @param QuickformElement  Object representation of an element
     * @return void
     */
    function _storeObject($elObj) 
    {
        if ($elObj) {
            $keys = $elObj->keys;
            unset($elObj->keys);
            if(is_object($this->_currentGroup) && ('group' != $elObj->type)) {
                $code = '$this->_currentGroup' . $keys . ' = $elObj;';
            } else {
                $code = '$this->_obj' . $keys . ' = $elObj;';
            }
            eval($code);
        }
    }

    /**
     * Set the filename of the template to render html elements.
     * In your template, {html} is replaced by the unmodified html.
     * If the element is required, {required} will be true.
     * Eg.
     * <pre>
     * {if:error}
     *   <font color="red" size="1">{error:h}</font><br />
     * {end:}
     * {html:h}
     * </pre>
     *
     * @access public
     * @param string   Filename of template
     * @return void
     */
    function setHtmlTemplate($template)
    {
        $this->_html = $template;
    } 

    /**
     * Set the filename of the template to render form labels
     * In your template, {label} is replaced by the unmodified label.
     * {error} will be set to the error, if any.  {required} will
     * be true if this is a required field
     * Eg.
     * <pre>
     * {if:required}
     * <font color="orange" size="1">*</font>
     * {end:}
     * {label:h}
     * </pre>
     *
     * @access public
     * @param string   Filename of template
     * @return void
     */
    function setLabelTemplate($template) 
    {
        $this->_label = $template;
    }

    function _renderLabel(&$ret)
    {
        $this->_flexy->compile($this->_label);
        $ret->label = $this->_flexy->bufferedOutputObject($ret);
    }

    function _renderHtml(&$ret)
    {
        $this->_flexy->compile($this->_html);
        $ret->html = $this->_flexy->bufferedOutputObject($ret);
    }
} // end class HTML_QuickForm_Renderer_ObjectFlexy

/**
 * Adds nothing to QuickformForm, left for backwards compatibility
 *
 * @category    HTML
 * @package     HTML_QuickForm
 * @ignore
 */
class QuickformFlexyForm extends QuickformForm
{
}

/**
 * Adds nothing to QuickformElement, left for backwards compatibility
 *
 * @category    HTML
 * @package     HTML_QuickForm
 * @ignore
 */
class QuickformFlexyElement extends QuickformElement
{
}
?>
