<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * HTML class for an advanced checkbox type field
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
 * @author      Jason Rust <jrust@php.net>
 * @author      Alexey Borzov <avb@php.net>
 * @copyright   2001-2007 The PHP Group
 * @license     http://www.php.net/license/3_01.txt PHP License 3.01
 * @version     CVS: $Id: advcheckbox.php,v 1.17 2007/05/29 18:34:36 avb Exp $
 * @link        http://pear.php.net/package/HTML_QuickForm
 */

/**
 * HTML class for a checkbox type field
 */
require_once 'HTML/QuickForm/checkbox.php';

/**
 * HTML class for an advanced checkbox type field
 *
 * Basically this fixes a problem that HTML has had
 * where checkboxes can only pass a single value (the
 * value of the checkbox when checked).  A value for when
 * the checkbox is not checked cannot be passed, and 
 * furthermore the checkbox variable doesn't even exist if
 * the checkbox was submitted unchecked.
 *
 * It works by prepending a hidden field with the same name and
 * another "unchecked" value to the checbox. If the checkbox is
 * checked, PHP overwrites the value of the hidden field with
 * its value. 
 * 
 * @category    HTML
 * @package     HTML_QuickForm
 * @author      Jason Rust <jrust@php.net>
 * @author      Alexey Borzov <avb@php.net>
 * @version     Release: 3.2.9
 * @since       2.0
 */
class HTML_QuickForm_advcheckbox extends HTML_QuickForm_checkbox
{
    // {{{ properties

    /**
     * The values passed by the hidden elment
     *
     * @var array
     * @access private
     */
    var $_values = null;

    /**
     * The default value
     *
     * @var boolean
     * @access private
     */
    var $_currentValue = null;

    // }}}
    // {{{ constructor

    /**
     * Class constructor
     * 
     * @param     string    $elementName    (optional)Input field name attribute
     * @param     string    $elementLabel   (optional)Input field label 
     * @param     string    $text           (optional)Text to put after the checkbox
     * @param     mixed     $attributes     (optional)Either a typical HTML attribute string 
     *                                      or an associative array
     * @param     mixed     $values         (optional)Values to pass if checked or not checked 
     *
     * @since     1.0
     * @access    public
     * @return    void
     */
    function HTML_QuickForm_advcheckbox($elementName=null, $elementLabel=null, $text=null, $attributes=null, $values=null)
    {
        $this->HTML_QuickForm_checkbox($elementName, $elementLabel, $text, $attributes);
        $this->setValues($values);
    } //end constructor
    
    // }}}
    // {{{ getPrivateName()

    /**
     * Gets the private name for the element
     *
     * @param   string  $elementName The element name to make private
     *
     * @access public
     * @return string
     *
     * @deprecated          Deprecated since 3.2.6, both generated elements have the same name
     */
    function getPrivateName($elementName)
    {
        return '__'.$elementName;
    }

    // }}}
    // {{{ getOnclickJs()

    /**
     * Create the javascript for the onclick event which will
     * set the value of the hidden field
     *
     * @param     string    $elementName    The element name
     *
     * @access public
     * @return string
     *
     * @deprecated          Deprecated since 3.2.6, this element no longer uses any javascript
     */
    function getOnclickJs($elementName)
    {
        $onclickJs = 'if (this.checked) { this.form[\''.$elementName.'\'].value=\''.addcslashes($this->_values[1], '\'').'\'; }';
        $onclickJs .= 'else { this.form[\''.$elementName.'\'].value=\''.addcslashes($this->_values[0], '\'').'\'; }';
        return $onclickJs;
    }

    // }}}
    // {{{ setValues()

    /**
     * Sets the values used by the hidden element
     *
     * @param   mixed   $values The values, either a string or an array
     *
     * @access public
     * @return void
     */
    function setValues($values)
    {
        if (empty($values)) {
            // give it default checkbox behavior
            $this->_values = array('', 1);
        } elseif (is_scalar($values)) {
            // if it's string, then assume the value to 
            // be passed is for when the element is checked
            $this->_values = array('', $values);
        } else {
            $this->_values = $values;
        }
        $this->updateAttributes(array('value' => $this->_values[1]));
        $this->setChecked($this->_currentValue == $this->_values[1]);
    }

    // }}}
    // {{{ setValue()

   /**
    * Sets the element's value
    * 
    * @param    mixed   Element's value
    * @access   public
    */
    function setValue($value)
    {
        $this->setChecked(isset($this->_values[1]) && $value == $this->_values[1]);
        $this->_currentValue = $value;
    }

    // }}}
    // {{{ getValue()

   /**
    * Returns the element's value
    *
    * @access   public
    * @return   mixed
    */
    function getValue()
    {
        if (is_array($this->_values)) {
            return $this->_values[$this->getChecked()? 1: 0];
        } else {
            return null;
        }
    }

    // }}}
    // {{{ toHtml()

    /**
     * Returns the checkbox element in HTML
     * and the additional hidden element in HTML
     * 
     * @access    public
     * @return    string
     */
    function toHtml()
    {
        if ($this->_flagFrozen) {
            return parent::toHtml();
        } else {
            return '<input' . $this->_getAttrString(array(
                        'type'  => 'hidden', 
                        'name'  => $this->getName(), 
                        'value' => $this->_values[0]
                   )) . ' />' . parent::toHtml();
            
        }
    } //end func toHtml
    
    // }}}
    // {{{ getFrozenHtml()

   /**
    * Unlike checkbox, this has to append a hidden input in both
    * checked and non-checked states
    */
    function getFrozenHtml()
    {
        return ($this->getChecked()? '<tt>[x]</tt>': '<tt>[ ]</tt>') .
               $this->_getPersistantData();
    }

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
     */
    function onQuickFormEvent($event, $arg, &$caller)
    {
        switch ($event) {
            case 'updateValue':
                // constant values override both default and submitted ones
                // default values are overriden by submitted
                $value = $this->_findValue($caller->_constantValues);
                if (null === $value) {
                    $value = $this->_findValue($caller->_submitValues);
                    if (null === $value) {
                        $value = $this->_findValue($caller->_defaultValues);
                    }
                }
                if (null !== $value) {
                    $this->setValue($value);
                }
                break;
            default:
                parent::onQuickFormEvent($event, $arg, $caller);
        }
        return true;
    } // end func onQuickFormLoad

    // }}}
    // {{{ exportValue()

   /**
    * This element has a value even if it is not checked, thus we override
    * checkbox's behaviour here
    */
    function exportValue(&$submitValues, $assoc)
    {
        $value = $this->_findValue($submitValues);
        if (null === $value) {
            $value = $this->getValue();
        } elseif (is_array($this->_values) && ($value != $this->_values[0]) && ($value != $this->_values[1])) {
            $value = null;
        }
        return $this->_prepareValue($value, $assoc);
    }
    // }}}
} //end class HTML_QuickForm_advcheckbox
?>
