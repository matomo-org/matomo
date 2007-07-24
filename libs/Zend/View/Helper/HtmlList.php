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
 * Zend_View_Helper_FormELement
 */
require_once 'Zend/View/Helper/FormElement.php';

/**
 * Helper for ordered and unordered lists
 * 
 * @uses Zend_View_Helper_FormElement
 * @category   Zend
 * @package    Zend_View
 * @subpackage Helper
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_View_Helper_HtmlList extends Zend_View_Helper_FormElement
{

    /**
     * Generates a 'List' element.
     * 
     * @param array   $items   Array with the elements of the list
     * @param boolean $ordered Specifies ordered/unordered list; default unordered
     * @param array   $attribs Attributes for the ol/ul tag.
     * @return string The list XHTML.
     */
    public function htmlList(array $items, $ordered = false, $attribs = false)
    {
        if (!is_array($items)) {
            require_once 'Zend/View/Exception.php';
            throw new Zend_View_Exception('First param must be an array', $this);
        }

        $list = '';
        
        foreach ($items as $item) {
            if (!is_array($item)) {
                $list .= '<li>' . $item . '</li>';
            } else {
                if (5 < strlen($list)) {
                    $list = substr($list, 0, strlen($list) - 5) . $this->htmlList($item, $ordered) . '</li>';
                } else {
                    $list .= '<li>' . $this->htmlList($item, $ordered) . '</li>';
                }
            }
        }
        
        if ($attribs) {
            $attribs = $this->_htmlAttribs($attribs);
        } else {
            $attribs = '';
        }
        
        $tag = 'ul';
        if ($ordered) {
            $tag = 'ol';
        }

        return '<' . $tag . $attribs . '>' . $list . '</' . $tag . '>';
    }
}
