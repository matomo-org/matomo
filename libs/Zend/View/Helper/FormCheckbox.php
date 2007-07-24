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
 * @package    Zend_View
 * @subpackage Helper
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */


/**
 * Abstract class for extension
 */
require_once 'Zend/View/Helper/FormElement.php';


/**
 * Helper to generate a "checkbox" element
 * 
 * @category   Zend
 * @package    Zend_View
 * @subpackage Helper
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_View_Helper_FormCheckbox extends Zend_View_Helper_FormElement 
{
    /**
     * Generates a 'checkbox' element.
     * 
     * @access public
     * 
     * @param string|array $name If a string, the element name.  If an
     * array, all other parameters are ignored, and the array elements
     * are extracted in place of added parameters.
     * @param mixed $value The element value.
     * @param array $attribs Attributes for the element tag.
     * @param mixed $options If a scalar (single value), the value of the
     * checkbox when checked; if an array, element 0 is the value when
     * checked, and element 1 is the value when not-checked.
     * @return string The element XHTML.
     */
    public function formCheckbox($name, $value = null, $attribs = null,
        $options = array(1,0))
    {
        $info = $this->_getInfo($name, $value, $attribs, $options);
        extract($info); // name, id, value, attribs, options, listsep, disable
        
        // make sure attribs don't overwrite name and value
        unset($attribs['name']);
        unset($attribs['value']);
        
        // set up checked/unchecked options
        if (empty($options)) {
            $options = array(1, 0);
        } else {
            settype($options, 'array');
            if (! isset($options[1])) {
                $options[1] = null;
            }
        }
        
        // build the element
        if ($disable) {
            // disabled.
            if ($value == $options[0]) {
                // checked
                $xhtml = $this->_hidden($name, $options[0]) . '[x]';
            } else {
                // not checked
                $xhtml = $this->_hidden($name, $options[1]) . '[&nbsp;]';
            }
        } else {
            // enabled. add the hidden "unchecked" option first, then
            // the the checkbox itself) next. this way, if not-checked,
            // the "unchecked" option is returned to the server instead.
            $checkboxValue = (null === $value) ? $options[0] : $value;
            $xhtml = $this->_hidden($name, $options[1]) 
                   . '<input type="checkbox"'
                   . ' name="' . $this->view->escape($name) . '"'
                   . ' id="' . $this->view->escape($id) . '"'
                   . ' value="' . $this->view->escape($checkboxValue) . '"';
            
            // is it checked already?
            if ($value == $options[0]) {
                $xhtml .= ' checked="checked"';
            }
            
            // add attributes and close.
            $xhtml .= ' ' . $this->_htmlAttribs($attribs) . ' />';
        }

        return $xhtml;
    }
}
