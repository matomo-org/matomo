<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * An abstract base class for QuickForm renderers
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
 * @author      Alexey Borzov <avb@php.net>
 * @copyright   2001-2009 The PHP Group
 * @license     http://www.php.net/license/3_01.txt PHP License 3.01
 * @version     CVS: $Id$
 * @link        http://pear.php.net/package/HTML_QuickForm
 */

/**
 * An abstract base class for QuickForm renderers
 * 
 * The class implements a Visitor design pattern
 *
 * @category    HTML
 * @package     HTML_QuickForm
 * @author      Alexey Borzov <avb@php.net>
 * @version     Release: 3.2.11
 * @since       3.0
 * @abstract
 */
class HTML_QuickForm_Renderer
{
   /**
    * Constructor
    *
    * @access public
    */
    function HTML_QuickForm_Renderer()
    {
    } // end constructor

   /**
    * Called when visiting a form, before processing any form elements
    *
    * @param    HTML_QuickForm  a form being visited
    * @access   public
    * @return   void 
    * @abstract
    */
    function startForm(&$form)
    {
        return;
    } // end func startForm

   /**
    * Called when visiting a form, after processing all form elements
    * 
    * @param    HTML_QuickForm  a form being visited
    * @access   public
    * @return   void 
    * @abstract
    */
    function finishForm(&$form)
    {
        return;
    } // end func finishForm

   /**
    * Called when visiting a header element
    *
    * @param    HTML_QuickForm_header   a header element being visited
    * @access   public
    * @return   void 
    * @abstract
    */
    function renderHeader(&$header)
    {
        return;
    } // end func renderHeader

   /**
    * Called when visiting an element
    *
    * @param    HTML_QuickForm_element  form element being visited
    * @param    bool                    Whether an element is required
    * @param    string                  An error message associated with an element
    * @access   public
    * @return   void 
    * @abstract
    */
    function renderElement(&$element, $required, $error)
    {
        return;
    } // end func renderElement

   /**
    * Called when visiting a hidden element
    * 
    * @param    HTML_QuickForm_element  a hidden element being visited
    * @access   public
    * @return   void
    * @abstract 
    */
    function renderHidden(&$element)
    {
        return;
    } // end func renderHidden

   /**
    * Called when visiting a raw HTML/text pseudo-element
    * 
    * Only implemented in Default renderer. Usage of 'html' elements is 
    * discouraged, templates should be used instead.
    *
    * @param    HTML_QuickForm_html     a 'raw html' element being visited
    * @access   public
    * @return   void 
    * @abstract
    */
    function renderHtml(&$data)
    {
        return;
    } // end func renderHtml

   /**
    * Called when visiting a group, before processing any group elements
    *
    * @param    HTML_QuickForm_group    A group being visited
    * @param    bool                    Whether a group is required
    * @param    string                  An error message associated with a group
    * @access   public
    * @return   void 
    * @abstract
    */
    function startGroup(&$group, $required, $error)
    {
        return;
    } // end func startGroup

   /**
    * Called when visiting a group, after processing all group elements
    *
    * @param    HTML_QuickForm_group    A group being visited
    * @access   public
    * @return   void 
    * @abstract
    */
    function finishGroup(&$group)
    {
        return;
    } // end func finishGroup
} // end class HTML_QuickForm_Renderer
?>
