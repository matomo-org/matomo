<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Base class for form elements
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
 * @author      Alexey Borzov <avb@php.net>
 * @copyright   2001-2009 The PHP Group
 * @license     http://www.php.net/license/3_01.txt PHP License 3.01
 * @version     CVS: $Id$
 * @link        http://pear.php.net/package/HTML_QuickForm
 */

/**
 * Base class for all HTML classes
 */
require_once dirname(__FILE__) . '/../Common.php';

/**
 * Base class for form elements
 * 
 * @category    HTML
 * @package     HTML_QuickForm
 * @author      Adam Daniel <adaniel1@eesus.jnj.com>
 * @author      Bertrand Mansion <bmansion@mamasam.com>
 * @author      Alexey Borzov <avb@php.net>
 * @version     Release: 3.2.11
 * @since       1.0
 * @abstract
 */
class HTML_QuickForm_element extends HTML_Common
{
    // {{{ properties

    /**
     * Label of the field
     * @var       string
     * @since     1.3
     * @access    private
     */
    var $_label = '';

    /**
     * Form element type
     * @var       string
     * @since     1.0
     * @access    private
     */
    var $_type = '';

    /**
     * Flag to tell if element is frozen
     * @var       boolean
     * @since     1.0
     * @access    private
     */
    var $_flagFrozen = false;

    /**
     * Does the element support persistant data when frozen
     * @var       boolean
     * @since     1.3
     * @access    private
     */
    var $_persistantFreeze = false;
    
    // }}}
    // {{{ constructor
    
    /**
     * Class constructor
     * 
     * @param    string     Name of the element
     * @param    mixed      Label(s) for the element
     * @param    mixed      Associative array of tag attributes or HTML attributes name="value" pairs
     * @since     1.0
     * @access    public
     * @return    void
     */
    function HTML_QuickForm_element($elementName=null, $elementLabel=null, $attributes=null)
    {
        HTML_Common::HTML_Common($attributes);
        if (isset($elementName)) {
            $this->setName($elementName);
        }
        if (isset($elementLabel)) {
            $this->setLabel($elementLabel);
        }
    } //end constructor
    
    // }}}
    // {{{ apiVersion()

    /**
     * Returns the current API version
     *
     * @since     1.0
     * @access    public
     * @return    float
     */
    function apiVersion()
    {
        return 3.2;
    } // end func apiVersion

    // }}}
    // {{{ getType()

    /**
     * Returns element type
     *
     * @since     1.0
     * @access    public
     * @return    string
     */
    function getType()
    {
        return $this->_type;
    } // end func getType

    // }}}
    // {{{ setName()

    /**
     * Sets the input field name
     * 
     * @param     string    $name   Input field name attribute
     * @since     1.0
     * @access    public
     * @return    void
     */
    function setName($name)
    {
        // interface method
    } //end func setName
    
    // }}}
    // {{{ getName()

    /**
     * Returns the element name
     * 
     * @since     1.0
     * @access    public
     * @return    string
     */
    function getName()
    {
        // interface method
    } //end func getName
    
    // }}}
    // {{{ setValue()

    /**
     * Sets the value of the form element
     *
     * @param     string    $value      Default value of the form element
     * @since     1.0
     * @access    public
     * @return    void
     */
    function setValue($value)
    {
        // interface
    } // end func setValue

    // }}}
    // {{{ getValue()

    /**
     * Returns the value of the form element
     *
     * @since     1.0
     * @access    public
     * @return    mixed
     */
    function getValue()
    {
        // interface
        return null;
    } // end func getValue
    
    // }}}
    // {{{ freeze()

    /**
     * Freeze the element so that only its value is returned
     * 
     * @access    public
     * @return    void
     */
    function freeze()
    {
        $this->_flagFrozen = true;
    } //end func freeze

    // }}}
    // {{{ unfreeze()

   /**
    * Unfreezes the element so that it becomes editable
    *
    * @access public
    * @return void
    * @since  3.2.4
    */
    function unfreeze()
    {
        $this->_flagFrozen = false;
    }

    // }}}
    // {{{ getFrozenHtml()

    /**
     * Returns the value of field without HTML tags
     * 
     * @since     1.0
     * @access    public
     * @return    string
     */
    function getFrozenHtml()
    {
        $value = $this->getValue();
        return (strlen($value)? htmlspecialchars($value): '&nbsp;') .
               $this->_getPersistantData();
    } //end func getFrozenHtml
    
    // }}}
    // {{{ _getPersistantData()

   /**
    * Used by getFrozenHtml() to pass the element's value if _persistantFreeze is on
    * 
    * @access private
    * @return string
    */
    function _getPersistantData()
    {
        if (!$this->_persistantFreeze) {
            return '';
        } else {
            $id = $this->getAttribute('id');
            return '<input' . $this->_getAttrString(array(
                       'type'  => 'hidden',
                       'name'  => $this->getName(),
                       'value' => $this->getValue()
                   ) + (isset($id)? array('id' => $id): array())) . ' />';
        }
    }

    // }}}
    // {{{ isFrozen()

    /**
     * Returns whether or not the element is frozen
     *
     * @since     1.3
     * @access    public
     * @return    bool
     */
    function isFrozen()
    {
        return $this->_flagFrozen;
    } // end func isFrozen

    // }}}
    // {{{ setPersistantFreeze()

    /**
     * Sets wether an element value should be kept in an hidden field
     * when the element is frozen or not
     * 
     * @param     bool    $persistant   True if persistant value
     * @since     2.0
     * @access    public
     * @return    void
     */
    function setPersistantFreeze($persistant=false)
    {
        $this->_persistantFreeze = $persistant;
    } //end func setPersistantFreeze

    // }}}
    // {{{ setLabel()

    /**
     * Sets display text for the element
     * 
     * @param     string    $label  Display text for the element
     * @since     1.3
     * @access    public
     * @return    void
     */
    function setLabel($label)
    {
        $this->_label = $label;
    } //end func setLabel

    // }}}
    // {{{ getLabel()

    /**
     * Returns display text for the element
     * 
     * @since     1.3
     * @access    public
     * @return    string
     */
    function getLabel()
    {
        return $this->_label;
    } //end func getLabel

    // }}}
    // {{{ _findValue()

    /**
     * Tries to find the element value from the values array
     * 
     * @since     2.7
     * @access    private
     * @return    mixed
     */
    function _findValue(&$values)
    {
        if (empty($values)) {
            return null;
        }
        $elementName = $this->getName();
        if (isset($values[$elementName])) {
            return $values[$elementName];
        } elseif (strpos($elementName, '[')) {
            $myVar = "['" . str_replace(
                         array('\\', '\'', ']', '['), array('\\\\', '\\\'', '', "']['"), 
                         $elementName
                     ) . "']";
            return eval("return (isset(\$values$myVar)) ? \$values$myVar : null;");
        } else {
            return null;
        }
    } //end func _findValue

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
            case 'createElement':
                $className = get_class($this);
                $this->$className($arg[0], $arg[1], $arg[2], $arg[3], $arg[4]);
                break;
            case 'addElement':
                $this->onQuickFormEvent('createElement', $arg, $caller);
                $this->onQuickFormEvent('updateValue', null, $caller);
                break;
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
            case 'setGroupValue':
                $this->setValue($arg);
        }
        return true;
    } // end func onQuickFormEvent

    // }}}
    // {{{ accept()

   /**
    * Accepts a renderer
    *
    * @param HTML_QuickForm_Renderer    renderer object
    * @param bool                       Whether an element is required
    * @param string                     An error message associated with an element
    * @access public
    * @return void 
    */
    function accept(&$renderer, $required=false, $error=null)
    {
        $renderer->renderElement($this, $required, $error);
    } // end func accept

    // }}}
    // {{{ _generateId()

   /**
    * Automatically generates and assigns an 'id' attribute for the element.
    * 
    * Currently used to ensure that labels work on radio buttons and
    * checkboxes. Per idea of Alexander Radivanovich.
    *
    * @access private
    * @return void 
    */
    function _generateId()
    {
        static $idx = 1;

        if (!$this->getAttribute('id')) {
            $this->updateAttributes(array('id' => 'qf_' . substr(md5(microtime() . $idx++), 0, 6)));
        }
    } // end func _generateId

    // }}}
    // {{{ exportValue()

   /**
    * Returns a 'safe' element's value
    *
    * @param  array   array of submitted values to search
    * @param  bool    whether to return the value as associative array
    * @access public
    * @return mixed
    */
    function exportValue(&$submitValues, $assoc = false)
    {
        $value = $this->_findValue($submitValues);
        if (null === $value) {
            $value = $this->getValue();
        }
        return $this->_prepareValue($value, $assoc);
    }
    
    // }}}
    // {{{ _prepareValue()

   /**
    * Used by exportValue() to prepare the value for returning
    *
    * @param  mixed   the value found in exportValue()
    * @param  bool    whether to return the value as associative array
    * @access private
    * @return mixed
    */
    function _prepareValue($value, $assoc)
    {
        if (null === $value) {
            return null;
        } elseif (!$assoc) {
            return $value;
        } else {
            $name = $this->getName();
            if (!strpos($name, '[')) {
                return array($name => $value);
            } else {
                $valueAry = array();
                $myIndex  = "['" . str_replace(
                                array('\\', '\'', ']', '['), array('\\\\', '\\\'', '', "']['"), 
                                $name
                            ) . "']";
                eval("\$valueAry$myIndex = \$value;");
                return $valueAry;
            }
        }
    }
    
    // }}}
} // end class HTML_QuickForm_element
?>
