<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * A renderer that makes it quick and easy to create customized forms.
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
 * @author      Jason Rust <jrust@rustyparts.com>
 * @copyright   2001-2009 The PHP Group
 * @license     http://www.php.net/license/3_01.txt PHP License 3.01
 * @version     CVS: $Id$
 * @link        http://pear.php.net/package/HTML_QuickForm
 */

/**
 * A concrete renderer for HTML_QuickForm, based on QuickForm 2.x built-in one
 */ 
require_once 'HTML/QuickForm/Renderer/Default.php';

/**
 * A renderer that makes it quick and easy to create customized forms.
 *
 * This renderer has three main distinctives: an easy way to create
 * custom-looking forms, the ability to separate the creation of form
 * elements from their display, and being able to use QuickForm in
 * widget-based template systems.  See the online docs for more info.
 * For a usage example see: docs/renderers/QuickHtml_example.php
 * 
 * @category    HTML
 * @package     HTML_QuickForm
 * @author      Jason Rust <jrust@rustyparts.com>
 * @version     Release: 3.2.11
 * @since       3.1.1
 */
class HTML_QuickForm_Renderer_QuickHtml extends HTML_QuickForm_Renderer_Default {
    // {{{ properties

    /**
     * The array of rendered elements
     * @var array
     */
    var $renderedElements = array();

    // }}}
    // {{{ constructor
    
    /**
     * Constructor
     *
     * @access public
     * @return void
     */
    function HTML_QuickForm_Renderer_QuickHtml()
    {
        $this->HTML_QuickForm_Renderer_Default();
        // The default templates aren't used for this renderer
        $this->clearAllTemplates();
    } // end constructor

    // }}}
    // {{{ toHtml()

    /**
     * returns the HTML generated for the form
     *
     * @param string $data (optional) Any extra data to put before the end of the form
     *
     * @access public
     * @return string
     */
    function toHtml($data = '')
    {
        // Render any elements that haven't been rendered explicitly by elementToHtml()
        foreach (array_keys($this->renderedElements) as $key) {
            if (!$this->renderedElements[$key]['rendered']) {
                $this->renderedElements[$key]['rendered'] = true;
                $data .= $this->renderedElements[$key]['html'] . "\n";
            }
        }

        // Insert the extra data and form elements at the end of the form
        $this->_html = str_replace('</form>', $data . "\n</form>", $this->_html);
        return $this->_html;
    } // end func toHtml

    // }}}
    // {{{ elementToHtml()

    /**
     * Gets the html for an element and marks it as rendered.
     *
     * @param string $elementName The element name
     * @param string $elementValue (optional) The value of the element.  This is only useful
     *               for elements that have the same name (i.e. radio and checkbox), but
     *               different values
     *
     * @access public
     * @return string The html for the QuickForm element
     * @throws HTML_QuickForm_Error
     */
    function elementToHtml($elementName, $elementValue = null)
    {
        $elementKey = null;
        // Find the key for the element
        foreach ($this->renderedElements as $key => $data) {
            if ($data['name'] == $elementName && 
                // See if the value must match as well
                (is_null($elementValue) ||
                 $data['value'] == $elementValue)) {
                $elementKey = $key;
                break;
            }
        }

        if (is_null($elementKey)) {
            $msg = is_null($elementValue) ? "Element $elementName does not exist." : 
                "Element $elementName with value of $elementValue does not exist.";
            return PEAR::raiseError(null, QUICKFORM_UNREGISTERED_ELEMENT, null, E_USER_WARNING, $msg, 'HTML_QuickForm_Error', true);
        } else {
            if ($this->renderedElements[$elementKey]['rendered']) {
                $msg = is_null($elementValue) ? "Element $elementName has already been rendered." : 
                    "Element $elementName with value of $elementValue has already been rendered.";
                return PEAR::raiseError(null, QUICKFORM_ERROR, null, E_USER_WARNING, $msg, 'HTML_QuickForm_Error', true);
            } else {
                $this->renderedElements[$elementKey]['rendered'] = true;
                return $this->renderedElements[$elementKey]['html'];
            }
        }
    } // end func elementToHtml

    // }}}
    // {{{ renderElement()

    /**
     * Gets the html for an element and adds it to the array by calling
     * parent::renderElement()
     *
     * @param HTML_QuickForm_element    form element being visited
     * @param bool                      Whether an element is required
     * @param string                    An error message associated with an element
     *
     * @access public
     * @return mixed HTML string of element if $immediateRender is set, else we just add the
     *               html to the global _html string 
     */
    function renderElement(&$element, $required, $error)
    {
        $this->_html = '';
        parent::renderElement($element, $required, $error);
        if (!$this->_inGroup) {
            $this->renderedElements[] = array(
                    'name' => $element->getName(), 
                    'value' => $element->getValue(), 
                    'html' => $this->_html, 
                    'rendered' => false);
        }
        $this->_html = '';
    } // end func renderElement

    // }}}
    // {{{ renderHidden()

    /**
     * Gets the html for a hidden element and adds it to the array.
     * 
     * @param HTML_QuickForm_element    hidden form element being visited
     * @access public
     * @return void
     */
    function renderHidden(&$element)
    {
        $this->renderedElements[] = array(
                'name' => $element->getName(), 
                'value' => $element->getValue(), 
                'html' => $element->toHtml(), 
                'rendered' => false);
    } // end func renderHidden
    
    // }}}
    // {{{ finishGroup()

    /**
     * Gets the html for the group element and adds it to the array by calling
     * parent::finishGroup()
     *
     * @param    HTML_QuickForm_group   group being visited
     * @access   public
     * @return   void
     */
    function finishGroup(&$group)
    {
        $this->_html = '';
        parent::finishGroup($group);
        $this->renderedElements[] = array(
                'name' => $group->getName(), 
                'value' => $group->getValue(), 
                'html' => $this->_html, 
                'rendered' => false);
        $this->_html = '';
    } // end func finishGroup

    // }}}
} // end class HTML_QuickForm_Renderer_QuickHtml
?>
