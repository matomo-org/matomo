<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * HTML class for a link type field
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
 * @author      Adam Daniel <adaniel1@eesus.jnj.com>
 * @author      Bertrand Mansion <bmansion@mamasam.com>
 * @copyright   2001-2009 The PHP Group
 * @license     http://www.php.net/license/3_01.txt PHP License 3.01
 * @version     CVS: $Id$
 * @link        http://pear.php.net/package/HTML_QuickForm
 */

/**
 * HTML class for static data
 */ 
require_once dirname(__FILE__) . '/static.php';

/**
 * HTML class for a link type field
 * 
 * @category    HTML
 * @package     HTML_QuickForm
 * @author      Adam Daniel <adaniel1@eesus.jnj.com>
 * @author      Bertrand Mansion <bmansion@mamasam.com>
 * @version     Release: 3.2.11
 * @since       2.0
 */
class HTML_QuickForm_link extends HTML_QuickForm_static
{
    // {{{ properties

    /**
     * Link display text
     * @var       string
     * @since     1.0
     * @access    private
     */
    var $_text = "";

    // }}}
    // {{{ constructor
    
    /**
     * Class constructor
     * 
     * @param     string    $elementLabel   (optional)Link label
     * @param     string    $href           (optional)Link href
     * @param     string    $text           (optional)Link display text
     * @param     mixed     $attributes     (optional)Either a typical HTML attribute string 
     *                                      or an associative array
     * @since     1.0
     * @access    public
     * @return    void
     * @throws    
     */
    function HTML_QuickForm_link($elementName=null, $elementLabel=null, $href=null, $text=null, $attributes=null)
    {
        HTML_QuickForm_element::HTML_QuickForm_element($elementName, $elementLabel, $attributes);
        $this->_persistantFreeze = false;
        $this->_type = 'link';
        $this->setHref($href);
        $this->_text = $text;
    } //end constructor
    
    // }}}
    // {{{ setName()

    /**
     * Sets the input field name
     * 
     * @param     string    $name   Input field name attribute
     * @since     1.0
     * @access    public
     * @return    void
     * @throws    
     */
    function setName($name)
    {
        $this->updateAttributes(array('name'=>$name));
    } //end func setName
    
    // }}}
    // {{{ getName()

    /**
     * Returns the element name
     * 
     * @since     1.0
     * @access    public
     * @return    string
     * @throws    
     */
    function getName()
    {
        return $this->getAttribute('name');
    } //end func getName

    // }}}
    // {{{ setValue()

    /**
     * Sets value for textarea element
     * 
     * @param     string    $value  Value for password element
     * @since     1.0
     * @access    public
     * @return    void
     * @throws    
     */
    function setValue($value)
    {
        return;
    } //end func setValue
    
    // }}}
    // {{{ getValue()

    /**
     * Returns the value of the form element
     *
     * @since     1.0
     * @access    public
     * @return    void
     * @throws    
     */
    function getValue()
    {
        return;
    } // end func getValue

    
    // }}}
    // {{{ setHref()

    /**
     * Sets the links href
     *
     * @param     string    $href
     * @since     1.0
     * @access    public
     * @return    void
     * @throws    
     */
    function setHref($href)
    {
        $this->updateAttributes(array('href'=>$href));
    } // end func setHref

    // }}}
    // {{{ toHtml()

    /**
     * Returns the textarea element in HTML
     * 
     * @since     1.0
     * @access    public
     * @return    string
     * @throws    
     */
    function toHtml()
    {
        $tabs = $this->_getTabs();
        $html = "$tabs<a".$this->_getAttrString($this->_attributes).">";
        $html .= $this->_text;
        $html .= "</a>";
        return $html;
    } //end func toHtml
    
    // }}}
    // {{{ getFrozenHtml()

    /**
     * Returns the value of field without HTML tags (in this case, value is changed to a mask)
     * 
     * @since     1.0
     * @access    public
     * @return    string
     * @throws    
     */
    function getFrozenHtml()
    {
        return;
    } //end func getFrozenHtml

    // }}}

} //end class HTML_QuickForm_textarea
?>
