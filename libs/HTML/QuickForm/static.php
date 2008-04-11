<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * HTML class for static data
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
 * @author      Wojciech Gdela <eltehaem@poczta.onet.pl>
 * @copyright   2001-2007 The PHP Group
 * @license     http://www.php.net/license/3_01.txt PHP License 3.01
 * @version     CVS: $Id$
 * @link        http://pear.php.net/package/HTML_QuickForm
 */

/**
 * Base class for form elements
 */ 
require_once 'HTML/QuickForm/element.php';

/**
 * HTML class for static data
 * 
 * @category    HTML
 * @package     HTML_QuickForm
 * @author      Wojciech Gdela <eltehaem@poczta.onet.pl>
 * @version     Release: 3.2.9
 * @since       2.7
 */
class HTML_QuickForm_static extends HTML_QuickForm_element {
    
    // {{{ properties

    /**
     * Display text
     * @var       string
     * @access    private
     */
    var $_text = null;

    // }}}
    // {{{ constructor
    
    /**
     * Class constructor
     * 
     * @param     string    $elementLabel   (optional)Label
     * @param     string    $text           (optional)Display text
     * @access    public
     * @return    void
     */
    function HTML_QuickForm_static($elementName=null, $elementLabel=null, $text=null)
    {
        HTML_QuickForm_element::HTML_QuickForm_element($elementName, $elementLabel);
        $this->_persistantFreeze = false;
        $this->_type = 'static';
        $this->_text = $text;
    } //end constructor
    
    // }}}
    // {{{ setName()

    /**
     * Sets the element name
     * 
     * @param     string    $name   Element name
     * @access    public
     * @return    void
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
     * @access    public
     * @return    string
     */
    function getName()
    {
        return $this->getAttribute('name');
    } //end func getName

    // }}}
    // {{{ setText()

    /**
     * Sets the text
     *
     * @param     string    $text
     * @access    public
     * @return    void
     */
    function setText($text)
    {
        $this->_text = $text;
    } // end func setText

    // }}}
    // {{{ setValue()

    /**
     * Sets the text (uses the standard setValue call to emulate a form element.
     *
     * @param     string    $text
     * @access    public
     * @return    void
     */
    function setValue($text)
    {
        $this->setText($text);
    } // end func setValue

    // }}}    
    // {{{ toHtml()

    /**
     * Returns the static text element in HTML
     * 
     * @access    public
     * @return    string
     */
    function toHtml()
    {
        return $this->_getTabs() . $this->_text;
    } //end func toHtml
    
    // }}}
    // {{{ getFrozenHtml()

    /**
     * Returns the value of field without HTML tags
     * 
     * @access    public
     * @return    string
     */
    function getFrozenHtml()
    {
        return $this->toHtml();
    } //end func getFrozenHtml

    // }}}
    // {{{ onQuickFormEvent()

    /**
     * Called by HTML_QuickForm whenever form event is made on this element
     *
     * @param     string    $event  Name of event
     * @param     mixed     $arg    event arguments
     * @param     object    &$caller calling object
     * @since     1.0
     * @access    public
     * @return    void
     * @throws    
     */
    function onQuickFormEvent($event, $arg, &$caller)
    {
        switch ($event) {
            case 'updateValue':
                // do NOT use submitted values for static elements
                $value = $this->_findValue($caller->_constantValues);
                if (null === $value) {
                    $value = $this->_findValue($caller->_defaultValues);
                }
                if (null !== $value) {
                    $this->setValue($value);
                }
                break;
            default:
                parent::onQuickFormEvent($event, $arg, $caller);
        }
        return true;
    } // end func onQuickFormEvent

    // }}}
    // {{{ exportValue()

   /**
    * We override this here because we don't want any values from static elements
    */
    function exportValue(&$submitValues, $assoc = false)
    {
        return null;
    }
    
    // }}}
} //end class HTML_QuickForm_static
?>
