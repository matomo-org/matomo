<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Class for HTML 4.0 <button> element
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
 * Class for HTML 4.0 <button> element
 * 
 * @category    HTML
 * @package     HTML_QuickForm
 * @author      Alexey Borzov <avb@php.net>
 * @version     Release: 3.2.9
 * @since       3.2.3
 */
class HTML_QuickForm_xbutton extends HTML_QuickForm_element
{
   /**
    * Contents of the <button> tag
    * @var      string
    * @access   private
    */
    var $_content; 

   /**
    * Class constructor
    * 
    * @param    string  Button name
    * @param    string  Button content (HTML to add between <button></button> tags)
    * @param    mixed   Either a typical HTML attribute string or an associative array
    * @access   public
    */
    function HTML_QuickForm_xbutton($elementName = null, $elementContent = null, $attributes = null)
    {
        $this->HTML_QuickForm_element($elementName, null, $attributes);
        $this->setContent($elementContent);
        $this->setPersistantFreeze(false);
        $this->_type = 'xbutton';
    }


    function toHtml()
    {
        return '<button' . $this->getAttributes(true) . '>' . $this->_content . '</button>';
    }


    function getFrozenHtml()
    {
        return $this->toHtml();
    }


    function freeze()
    {
        return false;
    }


    function setName($name)
    {
        $this->updateAttributes(array(
            'name' => $name 
        ));
    }


    function getName()
    {
        return $this->getAttribute('name');
    }


    function setValue($value)
    {
        $this->updateAttributes(array(
            'value' => $value
        ));
    }


    function getValue()
    {
        return $this->getAttribute('value');
    }


   /**
    * Sets the contents of the button element
    *
    * @param    string  Button content (HTML to add between <button></button> tags)
    */
    function setContent($content)
    {
        $this->_content = $content;
    }


    function onQuickFormEvent($event, $arg, &$caller)
    {
        if ('updateValue' != $event) {
            return parent::onQuickFormEvent($event, $arg, $caller);
        } else {
            $value = $this->_findValue($caller->_constantValues);
            if (null === $value) {
                $value = $this->_findValue($caller->_defaultValues);
            }
            if (null !== $value) {
                $this->setValue($value);
            }
        }
        return true;
    }


   /**
    * Returns a 'safe' element's value
    * 
    * The value is only returned if the button's type is "submit" and if this
    * particlular button was clicked
    */
    function exportValue(&$submitValues, $assoc = false)
    {
        if ('submit' == $this->getAttribute('type')) {
            return $this->_prepareValue($this->_findValue($submitValues), $assoc);
        } else {
            return null;
        }
    }
}
?>
