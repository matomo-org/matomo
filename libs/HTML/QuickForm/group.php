<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * HTML class for a form element group
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
 * @copyright   2001-2007 The PHP Group
 * @license     http://www.php.net/license/3_01.txt PHP License 3.01
 * @version     CVS: $Id: group.php,v 1.39 2007/05/29 18:34:36 avb Exp $
 * @link        http://pear.php.net/package/HTML_QuickForm
 */

/**
 * Base class for form elements
 */ 
require_once 'HTML/QuickForm/element.php';

/**
 * HTML class for a form element group
 * 
 * @category    HTML
 * @package     HTML_QuickForm
 * @author      Adam Daniel <adaniel1@eesus.jnj.com>
 * @author      Bertrand Mansion <bmansion@mamasam.com>
 * @author      Alexey Borzov <avb@php.net>
 * @version     Release: 3.2.9
 * @since       1.0
 */
class HTML_QuickForm_group extends HTML_QuickForm_element
{
    // {{{ properties
        
    /**
     * Name of the element
     * @var       string
     * @since     1.0
     * @access    private
     */
    var $_name = '';

    /**
     * Array of grouped elements
     * @var       array
     * @since     1.0
     * @access    private
     */
    var $_elements = array();

    /**
     * String to separate elements
     * @var       mixed
     * @since     2.5
     * @access    private
     */
    var $_separator = null;

    /**
     * Required elements in this group
     * @var       array
     * @since     2.5
     * @access    private
     */
    var $_required = array();

   /**
    * Whether to change elements' names to $groupName[$elementName] or leave them as is 
    * @var      bool
    * @since    3.0
    * @access   private
    */
    var $_appendName = true;

    // }}}
    // {{{ constructor

    /**
     * Class constructor
     * 
     * @param     string    $elementName    (optional)Group name
     * @param     array     $elementLabel   (optional)Group label
     * @param     array     $elements       (optional)Group elements
     * @param     mixed     $separator      (optional)Use a string for one separator,
     *                                      use an array to alternate the separators.
     * @param     bool      $appendName     (optional)whether to change elements' names to
     *                                      the form $groupName[$elementName] or leave 
     *                                      them as is.
     * @since     1.0
     * @access    public
     * @return    void
     */
    function HTML_QuickForm_group($elementName=null, $elementLabel=null, $elements=null, $separator=null, $appendName = true)
    {
        $this->HTML_QuickForm_element($elementName, $elementLabel);
        $this->_type = 'group';
        if (isset($elements) && is_array($elements)) {
            $this->setElements($elements);
        }
        if (isset($separator)) {
            $this->_separator = $separator;
        }
        if (isset($appendName)) {
            $this->_appendName = $appendName;
        }
    } //end constructor
    
    // }}}
    // {{{ setName()

    /**
     * Sets the group name
     * 
     * @param     string    $name   Group name
     * @since     1.0
     * @access    public
     * @return    void
     */
    function setName($name)
    {
        $this->_name = $name;
    } //end func setName
    
    // }}}
    // {{{ getName()

    /**
     * Returns the group name
     * 
     * @since     1.0
     * @access    public
     * @return    string
     */
    function getName()
    {
        return $this->_name;
    } //end func getName

    // }}}
    // {{{ setValue()

    /**
     * Sets values for group's elements
     * 
     * @param     mixed    Values for group's elements
     * @since     1.0
     * @access    public
     * @return    void
     */
    function setValue($value)
    {
        $this->_createElementsIfNotExist();
        foreach (array_keys($this->_elements) as $key) {
            if (!$this->_appendName) {
                $v = $this->_elements[$key]->_findValue($value);
                if (null !== $v) {
                    $this->_elements[$key]->onQuickFormEvent('setGroupValue', $v, $this);
                }

            } else {
                $elementName = $this->_elements[$key]->getName();
                $index       = strlen($elementName) ? $elementName : $key;
                if (is_array($value)) {
                    if (isset($value[$index])) {
                        $this->_elements[$key]->onQuickFormEvent('setGroupValue', $value[$index], $this);
                    }
                } elseif (isset($value)) {
                    $this->_elements[$key]->onQuickFormEvent('setGroupValue', $value, $this);
                }
            }
        }
    } //end func setValue
    
    // }}}
    // {{{ getValue()

    /**
     * Returns the value of the group
     *
     * @since     1.0
     * @access    public
     * @return    mixed
     */
    function getValue()
    {
        $value = null;
        foreach (array_keys($this->_elements) as $key) {
            $element =& $this->_elements[$key];
            switch ($element->getType()) {
                case 'radio': 
                    $v = $element->getChecked()? $element->getValue(): null;
                    break;
                case 'checkbox': 
                    $v = $element->getChecked()? true: null;
                    break;
                default:
                    $v = $element->getValue();
            }
            if (null !== $v) {
                $elementName = $element->getName();
                if (is_null($elementName)) {
                    $value = $v;
                } else {
                    if (!is_array($value)) {
                        $value = is_null($value)? array(): array($value);
                    }
                    if ('' === $elementName) {
                        $value[] = $v;
                    } else {
                        $value[$elementName] = $v;
                    }
                }
            }
        }
        return $value;
    } // end func getValue

    // }}}
    // {{{ setElements()

    /**
     * Sets the grouped elements
     *
     * @param     array     $elements   Array of elements
     * @since     1.1
     * @access    public
     * @return    void
     */
    function setElements($elements)
    {
        $this->_elements = array_values($elements);
        if ($this->_flagFrozen) {
            $this->freeze();
        }
    } // end func setElements

    // }}}
    // {{{ getElements()

    /**
     * Gets the grouped elements
     *
     * @since     2.4
     * @access    public
     * @return    array
     */
    function &getElements()
    {
        $this->_createElementsIfNotExist();
        return $this->_elements;
    } // end func getElements

    // }}}
    // {{{ getGroupType()

    /**
     * Gets the group type based on its elements
     * Will return 'mixed' if elements contained in the group
     * are of different types.
     *
     * @access    public
     * @return    string    group elements type
     */
    function getGroupType()
    {
        $this->_createElementsIfNotExist();
        $prevType = '';
        foreach (array_keys($this->_elements) as $key) {
            $type = $this->_elements[$key]->getType();
            if ($type != $prevType && $prevType != '') {
                return 'mixed';
            }
            $prevType = $type;
        }
        return $type;
    } // end func getGroupType

    // }}}
    // {{{ toHtml()

    /**
     * Returns Html for the group
     * 
     * @since       1.0
     * @access      public
     * @return      string
     */
    function toHtml()
    {
        include_once('HTML/QuickForm/Renderer/Default.php');
        $renderer = new HTML_QuickForm_Renderer_Default();
        $renderer->setElementTemplate('{element}');
        $this->accept($renderer);
        return $renderer->toHtml();
    } //end func toHtml
    
    // }}}
    // {{{ getElementName()

    /**
     * Returns the element name inside the group such as found in the html form
     * 
     * @param     mixed     $index  Element name or element index in the group
     * @since     3.0
     * @access    public
     * @return    mixed     string with element name, false if not found
     */
    function getElementName($index)
    {
        $this->_createElementsIfNotExist();
        $elementName = false;
        if (is_int($index) && isset($this->_elements[$index])) {
            $elementName = $this->_elements[$index]->getName();
            if (isset($elementName) && $elementName == '') {
                $elementName = $index;
            }
            if ($this->_appendName) {
                if (is_null($elementName)) {
                    $elementName = $this->getName();
                } else {
                    $elementName = $this->getName().'['.$elementName.']';
                }
            }

        } elseif (is_string($index)) {
            foreach (array_keys($this->_elements) as $key) {
                $elementName = $this->_elements[$key]->getName();
                if ($index == $elementName) {
                    if ($this->_appendName) {
                        $elementName = $this->getName().'['.$elementName.']';
                    }
                    break;
                } elseif ($this->_appendName && $this->getName().'['.$elementName.']' == $index) {
                    break;
                }
            }
        }
        return $elementName;
    } //end func getElementName

    // }}}
    // {{{ getFrozenHtml()

    /**
     * Returns the value of field without HTML tags
     * 
     * @since     1.3
     * @access    public
     * @return    string
     */
    function getFrozenHtml()
    {
        $flags = array();
        $this->_createElementsIfNotExist();
        foreach (array_keys($this->_elements) as $key) {
            if (false === ($flags[$key] = $this->_elements[$key]->isFrozen())) {
                $this->_elements[$key]->freeze();
            }
        }
        $html = $this->toHtml();
        foreach (array_keys($this->_elements) as $key) {
            if (!$flags[$key]) {
                $this->_elements[$key]->unfreeze();
            }
        }
        return $html;
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
     */
    function onQuickFormEvent($event, $arg, &$caller)
    {
        switch ($event) {
            case 'updateValue':
                $this->_createElementsIfNotExist();
                foreach (array_keys($this->_elements) as $key) {
                    if ($this->_appendName) {
                        $elementName = $this->_elements[$key]->getName();
                        if (is_null($elementName)) {
                            $this->_elements[$key]->setName($this->getName());
                        } elseif ('' === $elementName) {
                            $this->_elements[$key]->setName($this->getName() . '[' . $key . ']');
                        } else {
                            $this->_elements[$key]->setName($this->getName() . '[' . $elementName . ']');
                        }
                    }
                    $this->_elements[$key]->onQuickFormEvent('updateValue', $arg, $caller);
                    if ($this->_appendName) {
                        $this->_elements[$key]->setName($elementName);
                    }
                }
                break;

            default:
                parent::onQuickFormEvent($event, $arg, $caller);
        }
        return true;
    } // end func onQuickFormEvent

    // }}}
    // {{{ accept()

   /**
    * Accepts a renderer
    *
    * @param HTML_QuickForm_Renderer    renderer object
    * @param bool                       Whether a group is required
    * @param string                     An error message associated with a group
    * @access public
    * @return void 
    */
    function accept(&$renderer, $required = false, $error = null)
    {
        $this->_createElementsIfNotExist();
        $renderer->startGroup($this, $required, $error);
        $name = $this->getName();
        foreach (array_keys($this->_elements) as $key) {
            $element =& $this->_elements[$key];
            
            if ($this->_appendName) {
                $elementName = $element->getName();
                if (isset($elementName)) {
                    $element->setName($name . '['. (strlen($elementName)? $elementName: $key) .']');
                } else {
                    $element->setName($name);
                }
            }

            $required = !$element->isFrozen() && in_array($element->getName(), $this->_required);

            $element->accept($renderer, $required);

            // restore the element's name
            if ($this->_appendName) {
                $element->setName($elementName);
            }
        }
        $renderer->finishGroup($this);
    } // end func accept

    // }}}
    // {{{ exportValue()

   /**
    * As usual, to get the group's value we access its elements and call
    * their exportValue() methods
    */
    function exportValue(&$submitValues, $assoc = false)
    {
        $value = null;
        foreach (array_keys($this->_elements) as $key) {
            $elementName = $this->_elements[$key]->getName();
            if ($this->_appendName) {
                if (is_null($elementName)) {
                    $this->_elements[$key]->setName($this->getName());
                } elseif ('' === $elementName) {
                    $this->_elements[$key]->setName($this->getName() . '[' . $key . ']');
                } else {
                    $this->_elements[$key]->setName($this->getName() . '[' . $elementName . ']');
                }
            }
            $v = $this->_elements[$key]->exportValue($submitValues, $assoc);
            if ($this->_appendName) {
                $this->_elements[$key]->setName($elementName);
            }
            if (null !== $v) {
                // Make $value an array, we will use it like one
                if (null === $value) {
                    $value = array();
                }
                if ($assoc) {
                    // just like HTML_QuickForm::exportValues()
                    $value = HTML_QuickForm::arrayMerge($value, $v);
                } else {
                    // just like getValue(), but should work OK every time here
                    if (is_null($elementName)) {
                        $value = $v;
                    } elseif ('' === $elementName) {
                        $value[] = $v;
                    } else {
                        $value[$elementName] = $v;
                    }
                }
            }
        }
        // do not pass the value through _prepareValue, we took care of this already
        return $value;
    }

    // }}}
    // {{{ _createElements()

   /**
    * Creates the group's elements.
    * 
    * This should be overriden by child classes that need to create their 
    * elements. The method will be called automatically when needed, calling
    * it from the constructor is discouraged as the constructor is usually
    * called _twice_ on element creation, first time with _no_ parameters.
    * 
    * @access private
    * @abstract
    */
    function _createElements()
    {
        // abstract
    }

    // }}}
    // {{{ _createElementsIfNotExist()

   /**
    * A wrapper around _createElements()
    *
    * This method calls _createElements() if the group's _elements array
    * is empty. It also performs some updates, e.g. freezes the created
    * elements if the group is already frozen.
    *
    * @access private
    */
    function _createElementsIfNotExist()
    {
        if (empty($this->_elements)) {
            $this->_createElements();
            if ($this->_flagFrozen) {
                $this->freeze();
            }
        }
    }

    // }}}
    // {{{ freeze()

    function freeze()
    {
        parent::freeze();
        foreach (array_keys($this->_elements) as $key) {
            $this->_elements[$key]->freeze();
        }
    }

    // }}}
    // {{{ unfreeze()

    function unfreeze()
    {
        parent::unfreeze();
        foreach (array_keys($this->_elements) as $key) {
            $this->_elements[$key]->unfreeze();
        }
    }

    // }}}
    // {{{ setPersistantFreeze()

    function setPersistantFreeze($persistant = false)
    {
        parent::setPersistantFreeze($persistant);
        foreach (array_keys($this->_elements) as $key) {
            $this->_elements[$key]->setPersistantFreeze($persistant);
        }
    }

    // }}}
} //end class HTML_QuickForm_group
?>