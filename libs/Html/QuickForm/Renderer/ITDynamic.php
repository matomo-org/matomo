<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * A concrete renderer for HTML_QuickForm, using Integrated Templates.
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
 * @version     CVS: $Id: ITDynamic.php,v 1.6 2007/05/29 18:34:36 avb Exp $
 * @link        http://pear.php.net/package/HTML_QuickForm
 */

/**
 * An abstract base class for QuickForm renderers
 */
require_once 'HTML/QuickForm/Renderer.php';

/**
 * A concrete renderer for HTML_QuickForm, using Integrated Templates.
 * 
 * This is a "dynamic" renderer, which means that concrete form look 
 * is defined at runtime. This also means that you can define 
 * <b>one</b> template file for <b>all</b> your forms. That template
 * should contain a block for every element 'look' appearing in your 
 * forms and also some special blocks (consult the examples). If a
 * special block is not set for an element, the renderer falls back to
 * a default one.
 * 
 * @category    HTML
 * @package     HTML_QuickForm
 * @author      Alexey Borzov <avb@php.net>
 * @version     Release: 3.2.9
 * @since       3.0
 */
class HTML_QuickForm_Renderer_ITDynamic extends HTML_QuickForm_Renderer
{
   /**#@+
    * @access private
    */
   /**
    * A template class (HTML_Template_ITX or HTML_Template_Sigma) instance
    * @var HTML_Template_ITX|HTML_Template_Sigma
    */
    var $_tpl = null;

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
    * A separator for group elements
    * @var mixed
    */
    var $_groupSeparator = null;

   /**
    * The current element index inside a group
    * @var integer
    */
    var $_groupElementIdx = 0;

   /**
    * Blocks to use for different elements  
    * @var array
    */
    var $_elementBlocks = array();

   /**
    * Block to use for headers
    * @var string
    */
    var $_headerBlock = null;
   /**#@-*/


   /**
    * Constructor
    *
    * @param HTML_Template_ITX|HTML_Template_Sigma     Template object to use
    */
    function HTML_QuickForm_Renderer_ITDynamic(&$tpl)
    {
        $this->HTML_QuickForm_Renderer();
        $this->_tpl =& $tpl;
        $this->_tpl->setCurrentBlock('qf_main_loop');
    }


    function finishForm(&$form)
    {
        // display errors above form
        if (!empty($this->_errors) && $this->_tpl->blockExists('qf_error_loop')) {
            foreach ($this->_errors as $error) {
                $this->_tpl->setVariable('qf_error', $error);
                $this->_tpl->parse('qf_error_loop');
            }
        }
        // show required note
        if ($this->_showRequired) {
            $this->_tpl->setVariable('qf_required_note', $form->getRequiredNote());
        }
        // assign form attributes
        $this->_tpl->setVariable('qf_attributes', $form->getAttributes(true));
        // assign javascript validation rules
        $this->_tpl->setVariable('qf_javascript', $form->getValidationScript());
    }
      

    function renderHeader(&$header)
    {
        $blockName = $this->_matchBlock($header);
        if ('qf_header' == $blockName && isset($this->_headerBlock)) {
            $blockName = $this->_headerBlock;
        }
        $this->_tpl->setVariable('qf_header', $header->toHtml());
        $this->_tpl->parse($blockName);
        $this->_tpl->parse('qf_main_loop');
    }


    function renderElement(&$element, $required, $error)
    {
        $blockName = $this->_matchBlock($element);
        // are we inside a group?
        if ('qf_main_loop' != $this->_tpl->currentBlock) {
            if (0 != $this->_groupElementIdx && $this->_tpl->placeholderExists('qf_separator', $blockName)) {
                if (is_array($this->_groupSeparator)) {
                    $this->_tpl->setVariable('qf_separator', $this->_groupSeparator[($this->_groupElementIdx - 1) % count($this->_groupSeparator)]);
                } else {
                    $this->_tpl->setVariable('qf_separator', (string)$this->_groupSeparator);
                }
            }
            $this->_groupElementIdx++;

        } elseif(!empty($error)) {
            // show the error message or keep it for later use
            if ($this->_tpl->blockExists($blockName . '_error')) {
                $this->_tpl->setVariable('qf_error', $error);
            } else {
                $this->_errors[] = $error;
            }
        }
        // show an '*' near the required element
        if ($required) {
            $this->_showRequired = true;
            if ($this->_tpl->blockExists($blockName . '_required')) {
                $this->_tpl->touchBlock($blockName . '_required');
            }
        }
        // Prepare multiple labels
        $labels = $element->getLabel();
        if (is_array($labels)) {
            $mainLabel = array_shift($labels);
        } else {
            $mainLabel = $labels;
        }
        // render the element itself with its main label
        $this->_tpl->setVariable('qf_element', $element->toHtml());
        if ($this->_tpl->placeholderExists('qf_label', $blockName)) {
            $this->_tpl->setVariable('qf_label', $mainLabel);
        }
        // render extra labels, if any
        if (is_array($labels)) {
            foreach($labels as $key => $label) {
                $key = is_int($key)? $key + 2: $key;
                if ($this->_tpl->blockExists($blockName . '_label_' . $key)) {
                    $this->_tpl->setVariable('qf_label_' . $key, $label);
                }
            }
        }
        $this->_tpl->parse($blockName);
        $this->_tpl->parseCurrentBlock();
    }
   

    function renderHidden(&$element)
    {
        $this->_tpl->setVariable('qf_hidden', $element->toHtml());
        $this->_tpl->parse('qf_hidden_loop');
    }


    function startGroup(&$group, $required, $error)
    {
        $blockName = $this->_matchBlock($group);
        $this->_tpl->setCurrentBlock($blockName . '_loop');
        $this->_groupElementIdx = 0;
        $this->_groupSeparator  = is_null($group->_separator)? '&nbsp;': $group->_separator;
        // show an '*' near the required element
        if ($required) {
            $this->_showRequired = true;
            if ($this->_tpl->blockExists($blockName . '_required')) {
                $this->_tpl->touchBlock($blockName . '_required');
            }
        }
        // show the error message or keep it for later use
        if (!empty($error)) {
            if ($this->_tpl->blockExists($blockName . '_error')) {
                $this->_tpl->setVariable('qf_error', $error);
            } else {
                $this->_errors[] = $error;
            }
        }
        $this->_tpl->setVariable('qf_group_label', $group->getLabel());
    }


    function finishGroup(&$group)
    {
        $this->_tpl->parse($this->_matchBlock($group));
        $this->_tpl->setCurrentBlock('qf_main_loop');
        $this->_tpl->parseCurrentBlock();
    }


   /**
    * Returns the name of a block to use for element rendering
    * 
    * If a name was not explicitly set via setElementBlock(), it tries
    * the names '{prefix}_{element type}' and '{prefix}_{element}', where
    * prefix is either 'qf' or the name of the current group's block
    * 
    * @param HTML_QuickForm_element     form element being rendered
    * @access private
    * @return string    block name
    */
    function _matchBlock(&$element)
    {
        $name = $element->getName();
        $type = $element->getType();
        if (isset($this->_elementBlocks[$name]) && $this->_tpl->blockExists($this->_elementBlocks[$name])) {
            if (('group' == $type) || ($this->_elementBlocks[$name] . '_loop' != $this->_tpl->currentBlock)) {
                return $this->_elementBlocks[$name];
            }
        }
        if ('group' != $type && 'qf_main_loop' != $this->_tpl->currentBlock) {
            $prefix = substr($this->_tpl->currentBlock, 0, -5); // omit '_loop' postfix
        } else {
            $prefix = 'qf';
        }
        if ($this->_tpl->blockExists($prefix . '_' . $type)) {
            return $prefix . '_' . $type;
        } elseif ($this->_tpl->blockExists($prefix . '_' . $name)) {
            return $prefix . '_' . $name;
        } else {
            return $prefix . '_element';
        }
    }


   /**
    * Sets the block to use for element rendering
    * 
    * @param mixed      element name or array ('element name' => 'block name')
    * @param string     block name if $elementName is not an array
    * @access public
    * @return void
    */
    function setElementBlock($elementName, $blockName = null)
    {
        if (is_array($elementName)) {
            $this->_elementBlocks = array_merge($this->_elementBlocks, $elementName);
        } else {
            $this->_elementBlocks[$elementName] = $blockName;
        }
    }


   /**
    * Sets the name of a block to use for header rendering
    *
    * @param string     block name
    * @access public
    * @return void
    */
    function setHeaderBlock($blockName)
    {
        $this->_headerBlock = $blockName;
    }
}
?>
