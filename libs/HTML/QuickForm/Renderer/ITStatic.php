<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * A static renderer for HTML_QuickForm compatible 
 * with HTML_Template_IT and HTML_Template_Sigma.
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
 * @author      Bertrand Mansion <bmansion@mamasam.com>
 * @copyright   2001-2009 The PHP Group
 * @license     http://www.php.net/license/3_01.txt PHP License 3.01
 * @version     CVS: $Id$
 * @link        http://pear.php.net/package/HTML_QuickForm
 */

/**
 * An abstract base class for QuickForm renderers
 */
require_once 'HTML/QuickForm/Renderer.php';

/**
 * A static renderer for HTML_QuickForm compatible 
 * with HTML_Template_IT and HTML_Template_Sigma.
 *
 * As opposed to the dynamic renderer, this renderer needs
 * every elements and labels in the form to be specified by
 * placeholders at the position you want them to be displayed.
 * 
 * @category    HTML
 * @package     HTML_QuickForm
 * @author      Bertrand Mansion <bmansion@mamasam.com>
 * @version     Release: 3.2.11
 * @since       3.0
 */
class HTML_QuickForm_Renderer_ITStatic extends HTML_QuickForm_Renderer
{
   /**#@+
    * @access private
    */
   /**
    * An HTML_Template_IT or some other API compatible Template instance
    * @var object
    */
    var $_tpl = null;

   /**
    * Rendered form name
    * @var string
    */
    var $_formName = 'form';

   /**
    * The errors that were not shown near concrete fields go here
    * @var array
    */
    var $_errors = array();

   /**
    * Show the block with required note?
    * @var bool
    */
    var $_showRequired = false;

   /**
    * Which group are we currently parsing ?
    * @var string
    */
    var $_inGroup;

   /**
    * Index of the element in its group
    * @var int
    */
    var $_elementIndex = 0;

   /**
    * If elements have been added with the same name
    * @var array
    */
    var $_duplicateElements = array();

   /**
    * How to handle the required tag for required fields
    * @var string
    */
    var $_required = '{label}<font size="1" color="red">*</font>';

   /**
    * How to handle error messages in form validation
    * @var string
    */
    var $_error = '<font color="red">{error}</font><br />{html}';

   /**
    * Collected HTML for hidden elements, if needed  
    * @var string
    */
    var $_hidden = '';
   /**#@-*/

   /**
    * Constructor
    *
    * @param HTML_Template_IT|HTML_Template_Sigma   Template object to use
    */
    function HTML_QuickForm_Renderer_ITStatic(&$tpl)
    {
        $this->HTML_QuickForm_Renderer();
        $this->_tpl =& $tpl;
    } // end constructor

   /**
    * Called when visiting a form, before processing any form elements
    *
    * @param    HTML_QuickForm  form object being visited
    * @access   public
    * @return   void
    */
    function startForm(&$form)
    {
        $this->_formName = $form->getAttribute('id');

        if (count($form->_duplicateIndex) > 0) {
            // Take care of duplicate elements
            foreach ($form->_duplicateIndex as $elementName => $indexes) {
                $this->_duplicateElements[$elementName] = 0;
            }
        }
    } // end func startForm

   /**
    * Called when visiting a form, after processing all form elements
    * 
    * @param    HTML_QuickForm  form object being visited
    * @access   public
    * @return   void
    */
    function finishForm(&$form)
    {
        // display errors above form
        if (!empty($this->_errors) && $this->_tpl->blockExists($this->_formName.'_error_loop')) {
            foreach ($this->_errors as $error) {
                $this->_tpl->setVariable($this->_formName.'_error', $error);
                $this->_tpl->parse($this->_formName.'_error_loop');
            }
        }
        // show required note
        if ($this->_showRequired) {
            $this->_tpl->setVariable($this->_formName.'_required_note', $form->getRequiredNote());
        }
        // add hidden elements, if collected
        if (!empty($this->_hidden)) {
            $this->_tpl->setVariable($this->_formName . '_hidden', $this->_hidden);
        }
        // assign form attributes
        $this->_tpl->setVariable($this->_formName.'_attributes', $form->getAttributes(true));
        // assign javascript validation rules
        $this->_tpl->setVariable($this->_formName.'_javascript', $form->getValidationScript());
    } // end func finishForm

   /**
    * Called when visiting a header element
    *
    * @param    HTML_QuickForm_header   header element being visited
    * @access   public
    * @return   void
    */
    function renderHeader(&$header)
    {
        $name = $header->getName();
        $varName = $this->_formName.'_header';

        // Find placeHolder
        if (!empty($name) && $this->_tpl->placeHolderExists($this->_formName.'_header_'.$name)) {
            $varName = $this->_formName.'_header_'.$name;
        }
        $this->_tpl->setVariable($varName, $header->toHtml());
    } // end func renderHeader

   /**
    * Called when visiting an element
    *
    * @param    HTML_QuickForm_element  form element being visited
    * @param    bool                    Whether an element is required
    * @param    string                  An error message associated with an element
    * @access   public
    * @return   void
    */
    function renderElement(&$element, $required, $error)
    {
        $name = $element->getName();

        // are we inside a group?
        if (!empty($this->_inGroup)) {
            $varName = $this->_formName.'_'.str_replace(array('[', ']'), '_', $name);
            if (substr($varName, -2) == '__') {
                // element name is of type : group[]
                $varName = $this->_inGroup.'_'.$this->_elementIndex.'_';
                $this->_elementIndex++;
            }
            if ($varName != $this->_inGroup) {
                $varName .= '_' == substr($varName, -1)? '': '_';
                // element name is of type : group[name]
                $label = $element->getLabel();
                $html = $element->toHtml();

                if ($required && !$element->isFrozen()) {
                    $this->_renderRequired($label, $html);
                    $this->_showRequired = true;
                }
                if (!empty($label)) {
                    if (is_array($label)) {
                        foreach ($label as $key => $value) {
                            $this->_tpl->setVariable($varName.'label_'.$key, $value);
                        }
                    } else {
                        $this->_tpl->setVariable($varName.'label', $label);
                    }
                }
                $this->_tpl->setVariable($varName.'html', $html);
            }

        } else {

            $name = str_replace(array('[', ']'), array('_', ''), $name);

            if (isset($this->_duplicateElements[$name])) {
                // Element is a duplicate
                $varName = $this->_formName.'_'.$name.'_'.$this->_duplicateElements[$name];
                $this->_duplicateElements[$name]++;
            } else {
                $varName = $this->_formName.'_'.$name;
            }

            $label = $element->getLabel();
            $html = $element->toHtml();

            if ($required) {
                $this->_showRequired = true;
                $this->_renderRequired($label, $html);
            }
            if (!empty($error)) {
                $this->_renderError($label, $html, $error);
            }
            if (is_array($label)) {
                foreach ($label as $key => $value) {
                    $this->_tpl->setVariable($varName.'_label_'.$key, $value);
                }
            } else {
                $this->_tpl->setVariable($varName.'_label', $label);
            }
            $this->_tpl->setVariable($varName.'_html', $html);
        }
    } // end func renderElement

   /**
    * Called when visiting a hidden element
    * 
    * @param    HTML_QuickForm_element  hidden element being visited
    * @access   public
    * @return   void
    */
    function renderHidden(&$element)
    {
        if ($this->_tpl->placeholderExists($this->_formName . '_hidden')) {
            $this->_hidden .= $element->toHtml();
        } else {
            $name = $element->getName();
            $name = str_replace(array('[', ']'), array('_', ''), $name);
            $this->_tpl->setVariable($this->_formName.'_'.$name.'_html', $element->toHtml());
        }
    } // end func renderHidden

   /**
    * Called when visiting a group, before processing any group elements
    *
    * @param    HTML_QuickForm_group    group being visited
    * @param    bool                    Whether a group is required
    * @param    string                  An error message associated with a group
    * @access   public
    * @return   void
    */
    function startGroup(&$group, $required, $error)
    {
        $name = $group->getName();
        $varName = $this->_formName.'_'.$name;

        $this->_elementIndex = 0;

        $html = $this->_tpl->placeholderExists($varName.'_html') ? $group->toHtml() : '';
        $label = $group->getLabel();

        if ($required) {
            $this->_renderRequired($label, $html);
        }
        if (!empty($error)) {
            $this->_renderError($label, $html, $error);
        }
        if (!empty($html)) {
            $this->_tpl->setVariable($varName.'_html', $html);
        } else {
            // Uses error blocks to set the special groups layout error
            // <!-- BEGIN form_group_error -->{form_group_error}<!-- END form_group_error -->
            if (!empty($error)) {
                if ($this->_tpl->placeholderExists($varName.'_error')) {
                    if ($this->_tpl->blockExists($this->_formName . '_error_block')) {
                        $this->_tpl->setVariable($this->_formName . '_error', $error);
                        $error = $this->_getTplBlock($this->_formName . '_error_block');
                    } elseif (strpos($this->_error, '{html}') !== false || strpos($this->_error, '{label}') !== false) {
                        $error = str_replace('{error}', $error, $this->_error);
                    }
                }
                $this->_tpl->setVariable($varName . '_error', $error);
                array_pop($this->_errors);
            }
        }
        if (is_array($label)) {
            foreach ($label as $key => $value) {
                $this->_tpl->setVariable($varName.'_label_'.$key, $value);
            }
        } else {
            $this->_tpl->setVariable($varName.'_label', $label);
        }
        $this->_inGroup = $varName;
    } // end func startGroup

   /**
    * Called when visiting a group, after processing all group elements
    *
    * @param    HTML_QuickForm_group    group being visited
    * @access   public
    * @return   void
    */
    function finishGroup(&$group)
    {
        $this->_inGroup = '';
    } // end func finishGroup

   /**
    * Sets the way required elements are rendered
    *
    * You can use {label} or {html} placeholders to let the renderer know where
    * where the element label or the element html are positionned according to the
    * required tag. They will be replaced accordingly with the right value.
    * For example:
    * <font color="red">*</font>{label}
    * will put a red star in front of the label if the element is required.
    *
    * @param    string      The required element template
    * @access   public
    * @return   void
    */
    function setRequiredTemplate($template)
    {
        $this->_required = $template;
    } // end func setRequiredTemplate

   /**
    * Sets the way elements with validation errors are rendered
    *
    * You can use {label} or {html} placeholders to let the renderer know where
    * where the element label or the element html are positionned according to the
    * error message. They will be replaced accordingly with the right value.
    * The error message will replace the {error} place holder.
    * For example:
    * <font color="red">{error}</font><br />{html}
    * will put the error message in red on top of the element html.
    *
    * If you want all error messages to be output in the main error block, do not specify
    * {html} nor {label}.
    *
    * Groups can have special layouts. With this kind of groups, the renderer will need
    * to know where to place the error message. In this case, use error blocks like:
    * <!-- BEGIN form_group_error -->{form_group_error}<!-- END form_group_error -->
    * where you want the error message to appear in the form.
    *
    * @param    string      The element error template
    * @access   public
    * @return   void
    */
    function setErrorTemplate($template)
    {
        $this->_error = $template;
    } // end func setErrorTemplate

   /**
    * Called when an element is required
    *
    * This method will add the required tag to the element label and/or the element html
    * such as defined with the method setRequiredTemplate
    *
    * @param    string      The element label
    * @param    string      The element html rendering
    * @see      setRequiredTemplate()
    * @access   private
    * @return   void
    */
    function _renderRequired(&$label, &$html)
    {
        if ($this->_tpl->blockExists($tplBlock = $this->_formName . '_required_block')) {
            if (!empty($label) && $this->_tpl->placeholderExists($this->_formName . '_label', $tplBlock)) {
                $this->_tpl->setVariable($this->_formName . '_label', is_array($label)? $label[0]: $label);
                if (is_array($label)) {
                    $label[0] = $this->_getTplBlock($tplBlock);
                } else {
                    $label    = $this->_getTplBlock($tplBlock);
                }
            }
            if (!empty($html) && $this->_tpl->placeholderExists($this->_formName . '_html', $tplBlock)) {
                $this->_tpl->setVariable($this->_formName . '_html', $html);
                $html = $this->_getTplBlock($tplBlock);
            }
        } else {
            if (!empty($label) && strpos($this->_required, '{label}') !== false) {
                if (is_array($label)) {
                    $label[0] = str_replace('{label}', $label[0], $this->_required);
                } else {
                    $label = str_replace('{label}', $label, $this->_required);
                }
            }
            if (!empty($html) && strpos($this->_required, '{html}') !== false) {
                $html = str_replace('{html}', $html, $this->_required);
            }
        }
    } // end func _renderRequired

   /**
    * Called when an element has a validation error
    *
    * This method will add the error message to the element label or the element html
    * such as defined with the method setErrorTemplate. If the error placeholder is not found
    * in the template, the error will be displayed in the form error block.
    *
    * @param    string      The element label
    * @param    string      The element html rendering
    * @param    string      The element error
    * @see      setErrorTemplate()
    * @access   private
    * @return   void
    */
    function _renderError(&$label, &$html, $error)
    {
        if ($this->_tpl->blockExists($tplBlock = $this->_formName . '_error_block')) {
            $this->_tpl->setVariable($this->_formName . '_error', $error);
            if (!empty($label) && $this->_tpl->placeholderExists($this->_formName . '_label', $tplBlock)) {
                $this->_tpl->setVariable($this->_formName . '_label', is_array($label)? $label[0]: $label);
                if (is_array($label)) {
                    $label[0] = $this->_getTplBlock($tplBlock);
                } else {
                    $label    = $this->_getTplBlock($tplBlock);
                }
            } elseif (!empty($html) && $this->_tpl->placeholderExists($this->_formName . '_html', $tplBlock)) {
                $this->_tpl->setVariable($this->_formName . '_html', $html);
                $html = $this->_getTplBlock($tplBlock);
            }
            // clean up after ourselves
            $this->_tpl->setVariable($this->_formName . '_error', null);
        } elseif (!empty($label) && strpos($this->_error, '{label}') !== false) {
            if (is_array($label)) {
                $label[0] = str_replace(array('{label}', '{error}'), array($label[0], $error), $this->_error);
            } else {
                $label = str_replace(array('{label}', '{error}'), array($label, $error), $this->_error);
            }
        } elseif (!empty($html) && strpos($this->_error, '{html}') !== false) {
            $html = str_replace(array('{html}', '{error}'), array($html, $error), $this->_error);
        } else {
            $this->_errors[] = $error;
        }
    }// end func _renderError


   /**
    * Returns the block's contents
    *
    * The method is needed because ITX and Sigma implement clearing
    * the block contents on get() a bit differently
    *
    * @param    string  Block name
    * @return   string  Block contents
    */
    function _getTplBlock($block)
    {
        $this->_tpl->parse($block);
        if (is_a($this->_tpl, 'html_template_sigma')) {
            $ret = $this->_tpl->get($block, true);
        } else {
            $oldClear = $this->_tpl->clearCache;
            $this->_tpl->clearCache = true;
            $ret = $this->_tpl->get($block);
            $this->_tpl->clearCache = $oldClear;
        }
        return $ret;
    }
} // end class HTML_QuickForm_Renderer_ITStatic
?>