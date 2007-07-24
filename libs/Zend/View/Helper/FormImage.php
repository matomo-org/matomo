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
 * Helper to generate an "image" element
 * 
 * @category   Zend
 * @package    Zend_View
 * @subpackage Helper
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_View_Helper_FormImage extends Zend_View_Helper_FormElement 
{
    /**
     * Generates an 'image' element.
     * 
     * @access public
     * 
     * @param string|array $name If a string, the element name.  If an
     * array, all other parameters are ignored, and the array elements
     * are extracted in place of added parameters.
     * 
     * @param mixed $value The source ('src="..."') for the image.
     * 
     * @param array $attribs Attributes for the element tag.
     * 
     * @return string The element XHTML.
     */
    public function formImage($name, $value = null, $attribs = null)
    {
        $info = $this->_getInfo($name, $value, $attribs);
        extract($info); // name, value, attribs, options, listsep, disable
        
        // unset any 'src' attrib
        if (isset($attribs['src'])) {
            unset($attribs['src']);
        }
        
        // unset any 'alt' attrib
        if (isset($attribs['alt'])) {
            unset($attribs['alt']);
        }
        
        // build the element
        if ($disable) {
            // disabled, just an image tag
            $xhtml = '<image'
                   . ' alt="' . $this->view->escape($name) . '"'
                   . ' src="' . $this->view->escape($value) . '"'
                   . $this->_htmlAttribs($attribs) . ' />';
        } else {
            // enabled
            $xhtml = '<input type="image"'
                   . ' name="' . $this->view->escape($name) . '"'
                   . ' id="' . $this->view->escape($id) . '"'
                   . ' src="' . $this->view->escape($value) . '"'
                   . $this->_htmlAttribs($attribs) . ' />';
        }
        
        return $xhtml;
    }
}
