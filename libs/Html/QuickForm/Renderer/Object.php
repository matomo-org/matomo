<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * A concrete renderer for HTML_QuickForm, makes an object from form contents
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
 * @author      Ron McClain <ron@humaniq.com>
 * @copyright   2001-2007 The PHP Group
 * @license     http://www.php.net/license/3_01.txt PHP License 3.01
 * @version     CVS: $Id: Object.php,v 1.5 2007/05/29 18:34:36 avb Exp $
 * @link        http://pear.php.net/package/HTML_QuickForm
 */

/**
 * An abstract base class for QuickForm renderers
 */
require_once 'HTML/QuickForm/Renderer.php';

/**
 * A concrete renderer for HTML_QuickForm, makes an object from form contents
 *
 * Based on HTML_Quickform_Renderer_Array code
 *
 * @category    HTML
 * @package     HTML_QuickForm
 * @author      Ron McClain <ron@humaniq.com>
 * @version     Release: 3.2.9
 * @since       3.1.1
 */
class HTML_QuickForm_Renderer_Object extends HTML_QuickForm_Renderer
{
   /**#@+
    * @access private
    */
    /**
     * The object being generated
     * @var QuickformForm
     */
    var $_obj= null;

    /**
     * Number of sections in the form (i.e. number of headers in it)
     * @var integer $_sectionCount
     */
    var $_sectionCount;

    /**
    * Current section number
    * @var integer $_currentSection
    */
    var $_currentSection;

    /**
    * Object representing current group
    * @var object $_currentGroup
    */
    var $_currentGroup = null;

    /**
     * Class of Element Objects
     * @var object $_elementType
     */
    var $_elementType = 'QuickFormElement';

    /**
    * Additional style information for different elements  
    * @var array $_elementStyles
    */
    var $_elementStyles = array();

    /**
    * true: collect all hidden elements into string; false: process them as usual form elements
    * @var bool $_collectHidden
    */
    var $_collectHidden = false;
   /**#@-*/


    /**
     * Constructor
     *
     * @param bool    true: collect all hidden elements
     * @access public
     */
    function HTML_QuickForm_Renderer_Object($collecthidden = false) 
    {
        $this->HTML_QuickForm_Renderer();
        $this->_collectHidden = $collecthidden;
        $this->_obj = new QuickformForm;
    }

    /**
     * Return the rendered Object
     * @access public
     */
    function toObject() 
    {
        return $this->_obj;
    }

    /**
     * Set the class of the form elements.  Defaults to QuickformElement.
     * @param string   Name of element class
     * @access public
     */
    function setElementType($type)
    {
        $this->_elementType = $type;
    }

    function startForm(&$form) 
    {
        $this->_obj->frozen = $form->isFrozen();
        $this->_obj->javascript = $form->getValidationScript();
        $this->_obj->attributes = $form->getAttributes(true);
        $this->_obj->requirednote = $form->getRequiredNote();
        $this->_obj->errors = new StdClass;

        if($this->_collectHidden) {
            $this->_obj->hidden = '';
        }
        $this->_elementIdx = 1;
        $this->_currentSection = null;
        $this->_sectionCount = 0;
    } // end func startForm

    function renderHeader(&$header) 
    {
        $hobj = new StdClass;
        $hobj->header = $header->toHtml();
        $this->_obj->sections[$this->_sectionCount] = $hobj;
        $this->_currentSection = $this->_sectionCount++;
    }

    function renderElement(&$element, $required, $error) 
    {
        $elObj = $this->_elementToObject($element, $required, $error);
        if(!empty($error)) {
            $name = $elObj->name;
            $this->_obj->errors->$name = $error;
        }
        $this->_storeObject($elObj);
    } // end func renderElement

    function renderHidden(&$element)
    {
        if($this->_collectHidden) {
            $this->_obj->hidden .= $element->toHtml() . "\n";
        } else {
            $this->renderElement($element, false, null);
        }
    } //end func renderHidden

    function startGroup(&$group, $required, $error) 
    {
        $this->_currentGroup = $this->_elementToObject($group, $required, $error);
        if(!empty($error)) {
            $name = $this->_currentGroup->name;
            $this->_obj->errors->$name = $error;
        }
    } // end func startGroup

    function finishGroup(&$group) 
    {
        $this->_storeObject($this->_currentGroup);
        $this->_currentGroup = null;
    } // end func finishGroup

    /**
     * Creates an object representing an element
     *
     * @access private
     * @param HTML_QuickForm_element    form element being rendered
     * @param required bool         Whether an element is required
     * @param error string    Error associated with the element
     * @return object
     */
    function _elementToObject(&$element, $required, $error) 
    {
        if($this->_elementType) {
            $ret = new $this->_elementType;
        }
        $ret->name = $element->getName();
        $ret->value = $element->getValue();
        $ret->type = $element->getType();
        $ret->frozen = $element->isFrozen();
        $labels = $element->getLabel();
        if (is_array($labels)) {
            $ret->label = array_shift($labels);
            foreach ($labels as $key => $label) {
                $key = is_int($key)? $key + 2: $key;
                $ret->{'label_' . $key} = $label;
            }
        } else {
            $ret->label = $labels;
        }
        $ret->required = $required;
        $ret->error = $error;

        if(isset($this->_elementStyles[$ret->name])) {
            $ret->style = $this->_elementStyles[$ret->name];
            $ret->styleTemplate = "styles/". $ret->style .".html";
        }
        if($ret->type == 'group') {
            $ret->separator = $element->_separator;
            $ret->elements = array();
        } else {
            $ret->html = $element->toHtml();
        }
        return $ret;
    }

    /** 
     * Stores an object representation of an element in the form array
     *
     * @access private
     * @param QuickformElement     Object representation of an element
     * @return void
     */
    function _storeObject($elObj) 
    {
        $name = $elObj->name;
        if(is_object($this->_currentGroup) && $elObj->type != 'group') {
            $this->_currentGroup->elements[] = $elObj;
        } elseif (isset($this->_currentSection)) {
            $this->_obj->sections[$this->_currentSection]->elements[] = $elObj;
        } else {
            $this->_obj->elements[] = $elObj;
        }
    }

    function setElementStyle($elementName, $styleName = null)
    {
        if(is_array($elementName)) {
            $this->_elementStyles = array_merge($this->_elementStyles, $elementName);
        } else {
            $this->_elementStyles[$elementName] = $styleName;
        }
    }

} // end class HTML_QuickForm_Renderer_Object



/**
 * Convenience class for the form object passed to outputObject()
 * 
 * Eg.
 * <pre>  
 * {form.outputJavaScript():h}
 * {form.outputHeader():h}
 *   <table>
 *     <tr>
 *       <td>{form.name.label:h}</td><td>{form.name.html:h}</td>
 *     </tr>
 *   </table>
 * </form>
 * </pre>
 * 
 * @category    HTML
 * @package     HTML_QuickForm
 * @author      Ron McClain <ron@humaniq.com>
 * @version     Release: 3.2.9
 * @since       3.1.1
 */
class QuickformForm
{
   /**
    * Whether the form has been frozen
    * @var boolean $frozen
    */
    var $frozen;

   /**
    * Javascript for client-side validation
    * @var string $javascript
    */
    var $javascript;

   /**
    * Attributes for form tag
    * @var string $attributes
    */
    var $attributes;

   /**
    * Note about required elements
    * @var string $requirednote
    */
    var $requirednote;

   /**
    * Collected html of all hidden variables
    * @var string $hidden
    */
    var $hidden;

   /**
    * Set if there were validation errors.  
    * StdClass object with element names for keys and their
    * error messages as values
    * @var object $errors
    */
    var $errors;

   /**
    * Array of QuickformElementObject elements.  If there are headers in the form
    * this will be empty and the elements will be in the 
    * separate sections
    * @var array $elements
    */
    var $elements;

   /**
    * Array of sections contained in the document
    * @var array $sections
    */
    var $sections;

   /**
    * Output &lt;form&gt; header
    * {form.outputHeader():h} 
    * @return string    &lt;form attributes&gt;
    */
    function outputHeader()
    {
        return "<form " . $this->attributes . ">\n";
    }

   /**
    * Output form javascript
    * {form.outputJavaScript():h}
    * @return string    Javascript
    */
    function outputJavaScript()
    {
        return $this->javascript;
    }
} // end class QuickformForm


/**
 * Convenience class describing a form element.
 *
 * The properties defined here will be available from 
 * your flexy templates by referencing
 * {form.zip.label:h}, {form.zip.html:h}, etc.
 *
 * @category    HTML
 * @package     HTML_QuickForm
 * @author      Ron McClain <ron@humaniq.com>
 * @version     Release: 3.2.9
 * @since       3.1.1
 */
class QuickformElement
{
    /**
     * Element name
     * @var string $name
     */
    var $name;

    /**
     * Element value
     * @var mixed $value
     */
    var $value;

    /**
     * Type of element
     * @var string $type
     */
    var $type;

    /**
     * Whether the element is frozen
     * @var boolean $frozen
     */
    var $frozen;

    /**
     * Label for the element
     * @var string $label
     */
    var $label;

    /**
     * Whether element is required
     * @var boolean $required
     */
    var $required;

    /**
     * Error associated with the element
     * @var string $error
     */
    var $error;

    /**
     * Some information about element style
     * @var string $style
     */
    var $style;

    /**
     * HTML for the element
     * @var string $html
     */
    var $html;

    /**
     * If element is a group, the group separator
     * @var mixed $separator
     */
    var $separator;

    /**
     * If element is a group, an array of subelements
     * @var array $elements
     */
    var $elements;

    function isType($type)
    {
        return ($this->type == $type);
    }

    function notFrozen()
    {
        return !$this->frozen;
    }

    function isButton()
    {
        return ($this->type == "submit" || $this->type == "reset");
    }


   /**
    * XXX: why does it use Flexy when all other stuff here does not depend on it?
    */
    function outputStyle()
    {
        ob_start();
        HTML_Template_Flexy::staticQuickTemplate('styles/' . $this->style . '.html', $this);
        $ret = ob_get_contents();
        ob_end_clean();
        return $ret;
    }
} // end class QuickformElement
?>
