<?php
/**
 * A renderer for HTML_QuickForm2 building an array of form elements
 *
 * PHP version 5
 *
 * LICENSE:
 *
 * Copyright (c) 2006-2010, Alexey Borzov <avb@php.net>,
 *                          Bertrand Mansion <golgote@mamasam.com>
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *    * Redistributions of source code must retain the above copyright
 *      notice, this list of conditions and the following disclaimer.
 *    * Redistributions in binary form must reproduce the above copyright
 *      notice, this list of conditions and the following disclaimer in the
 *      documentation and/or other materials provided with the distribution.
 *    * The names of the authors may not be used to endorse or promote products
 *      derived from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS
 * IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO,
 * THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR
 * PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR
 * CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL,
 * EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO,
 * PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR
 * PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY
 * OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
 * NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @category   HTML
 * @package    HTML_QuickForm2
 * @author     Alexey Borzov <avb@php.net>
 * @author     Bertrand Mansion <golgote@mamasam.com>
 * @author     Thomas Schulz <ths@4bconsult.de>
 * @license    http://opensource.org/licenses/bsd-license.php New BSD License
 * @version    SVN: $Id: Array.php 294052 2010-01-26 20:00:22Z avb $
 * @link       http://pear.php.net/package/HTML_QuickForm2
 */

/**
 * Abstract base class for QuickForm2 renderers
 */
// require_once 'HTML/QuickForm2/Renderer.php';

/**
 * A renderer for HTML_QuickForm2 building an array of form elements
 *
 * Based on Array renderer from HTML_QuickForm 3.x package
 *
 * The form array structure is the following:
 * <pre>
 * array(
 *   'id'               => form's "id" attribute (string),
 *   'frozen'           => whether the form is frozen (bool),
 *   'attributes'       => attributes for &lt;form&gt; tag (string),
 *   // if form contains required elements:
 *   'required_note'    => note about the required elements (string),
 *   // if 'group_hiddens' option is true:
 *   'hidden'           => array with html of hidden elements (array),
 *   // if 'group_errors' option is true:
 *   'errors' => array(
 *     '1st element id' => 'Error for the 1st element',
 *     ...
 *     'nth element id' => 'Error for the nth element'
 *   ),
 *   'elements' => array(
 *     element_1,
 *     ...
 *     element_N
 *   )
 * );
 * </pre>
 * Where element_i is an array of the form
 * <pre>
 * array(
 *   'id'        => element id (string),
 *   'type'      => type of the element (string),
 *   'frozen'    => whether element is frozen (bool),
 *   // if element has a label:
 *   'label'     => 'label for the element',
 *   // note that if 'static_labels' option is true and element's label is an
 *   // array then there will be several 'label_*' keys corresponding to
 *   // labels' array keys
 *   'required'  => whether element is required (bool),
 *   // if a validation error is present and 'group_errors' option is false:
 *   'error'     => error associated with the element (string),
 *   // if some style was associated with an element:
 *   'style'     => 'some information about element style (e.g. for Smarty)',
 *
 *   // if element is not a Container
 *   'value'     => element value (mixed),
 *   'html'      => HTML for the element (string),
 *
 *   // if element is a Container
 *   'attributes' => container attributes (string)
 *   // only for groups, if separator is set:
 *   'separator'  => separator for group elements (mixed),
 *   'elements'   => array(
 *     element_1,
 *     ...
 *     element_N
 *   )
 * );
 * </pre>
 *
 * While almost everything in this class is defined as public, its properties
 * and those methods that are not published (i.e. not in array returned by
 * exportMethods()) will be available to renderer plugins only.
 *
 * The following methods are published:
 *   - {@link reset()}
 *   - {@link toArray()}
 *   - {@link setStyleForId()}
 *
 * @category   HTML
 * @package    HTML_QuickForm2
 * @author     Alexey Borzov <avb@php.net>
 * @author     Bertrand Mansion <golgote@mamasam.com>
 * @author     Thomas Schulz <ths@4bconsult.de>
 * @version    Release: @package_version@
 */
class HTML_QuickForm2_Renderer_Array extends HTML_QuickForm2_Renderer
{
   /**
    * An array being generated
    * @var array
    */
    public $array = array();

   /**
    * Array with references to 'elements' fields of currently processed containers
    * @var unknown_type
    */
    public $containers = array();

   /**
    * Whether the form contains required elements
    * @var  bool
    */
    public $hasRequired = false;

   /**
    * Additional style information for elements
    * @var array
    */
    public $styles = array();

   /**
    * Constructor, adds a new 'static_labels' option
    */
    protected function __construct()
    {
        $this->options['static_labels'] = false;
    }

    public function exportMethods()
    {
        return array(
            'reset',
            'toArray',
            'setStyleForId'
        );
    }

   /**
    * Resets the accumulated data
    *
    * This method is called automatically by startForm() method, but should
    * be called manually before calling other rendering methods separately.
    *
    * @return HTML_QuickForm2_Renderer_Array
    */
    public function reset()
    {
        $this->array       = array();
        $this->containers  = array();
        $this->hasRequired = false;

        return $this;
    }

   /**
    * Returns the resultant array
    *
    * @return array
    */
    public function toArray()
    {
        return $this->array;
    }

   /**
    * Creates an array with fields that are common to all elements
    *
    * @param    HTML_QuickForm2_Node    Element being rendered
    * @return   array
    */
    public function buildCommonFields(HTML_QuickForm2_Node $element)
    {
        $ary = array(
            'id'     => $element->getId(),
            'frozen' => $element->toggleFrozen()
        );
        if ($labels = $element->getLabel()) {
            if (!is_array($labels) || !$this->options['static_labels']) {
                $ary['label'] = $labels;
            } else {
                foreach ($labels as $key => $label) {
                    $key = is_int($key)? $key + 1: $key;
                    if (1 === $key) {
                        $ary['label'] = $label;
                    } else {
                        $ary['label_' . $key] = $label;
                    }
                }
            }
        }
        if (($error = $element->getError()) && $this->options['group_errors']) {
            $this->array['errors'][$ary['id']] = $error;
        } elseif ($error) {
            $ary['error'] = $error;
        }
        if (isset($this->styles[$ary['id']])) {
            $ary['style'] = $this->styles[$ary['id']];
        }
        if (!$element instanceof HTML_QuickForm2_Container) {
            $ary['html']       = $element->__toString();
        } else {
            $ary['elements']   = array();
            $ary['attributes'] = $element->getAttributes(true);
        }
        return $ary;
    }

   /**
    * Stores an array representing "scalar" element in the form array
    *
    * @param    array
    */
    public function pushScalar(array $element)
    {
        if (!empty($element['required'])) {
            $this->hasRequired = true;
        }
        if (empty($this->containers)) {
            $this->array += $element;
        } else {
            $this->containers[count($this->containers) - 1][] = $element;
        }
    }

   /**
    * Stores an array representing a Container in the form array
    *
    * @param    array
    */
    public function pushContainer(array $container)
    {
        if (!empty($container['required'])) {
            $this->hasRequired = true;
        }
        if (empty($this->containers)) {
            $this->array      += $container;
            $this->containers  = array(&$this->array['elements']);
        } else {
            $cntIndex = count($this->containers) - 1;
            $myIndex  = count($this->containers[$cntIndex]);
            $this->containers[$cntIndex][$myIndex] = $container;
            $this->containers[$cntIndex + 1] =& $this->containers[$cntIndex][$myIndex]['elements'];
        }
    }

   /**
    * Sets a style for element rendering
    *
    * "Style" is some information that is opaque to Array Renderer but may be
    * of use to e.g. template engine that receives the resultant array.
    *
    * @param    string|array    Element id or array ('element id' => 'style')
    * @param    sting           Element style if $idOrStyles is not an array
    * @return   HTML_QuickForm2_Renderer_Array
    */
    public function setStyleForId($idOrStyles, $style = null)
    {
        if (is_array($idOrStyles)) {
            $this->styles = array_merge($this->styles, $idOrStyles);
        } else {
            $this->styles[$idOrStyles] = $style;
        }
        return $this;
    }

   /**#@+
    * Implementations of abstract methods from {@link HTML_QuickForm2_Renderer}
    */
    public function renderElement(HTML_QuickForm2_Node $element)
    {
        $ary = $this->buildCommonFields($element) + array(
            'value'    => $element->getValue(),
            'type'     => $element->getType(),
            'required' => $element->isRequired(),
        );
        $this->pushScalar($ary);
    }

    public function renderHidden(HTML_QuickForm2_Node $element)
    {
        if ($this->options['group_hiddens']) {
            $this->array['hidden'][] = $element->__toString();
        } else {
            $this->renderElement($element);
        }
    }

    public function startForm(HTML_QuickForm2_Node $form)
    {
        $this->reset();

        $this->array = $this->buildCommonFields($form);
        if ($this->options['group_errors']) {
            $this->array['errors'] = array();
        }
        if ($this->options['group_hiddens']) {
            $this->array['hidden'] = array();
        }
        $this->containers  = array(&$this->array['elements']);
    }

    public function finishForm(HTML_QuickForm2_Node $form)
    {
        $this->finishContainer($form);
        if ($this->hasRequired) {
            $this->array['required_note'] = $this->options['required_note'];
        }
    }

    public function startContainer(HTML_QuickForm2_Node $container)
    {
        $ary = $this->buildCommonFields($container) + array(
            'required' => $container->isRequired(),
            'type'     => $container->getType()
        );
        $this->pushContainer($ary);
    }

    public function finishContainer(HTML_QuickForm2_Node $container)
    {
        array_pop($this->containers);
    }

    public function startGroup(HTML_QuickForm2_Node $group)
    {
        $ary = $this->buildCommonFields($group) + array(
            'required' => $group->isRequired(),
            'type'     => $group->getType()
        );
        if ($separator = $group->getSeparator()) {
            $ary['separator'] = $separator;
        }
        $this->pushContainer($ary);
    }

    public function finishGroup(HTML_QuickForm2_Node $group)
    {
        $this->finishContainer($group);
    }
    /**#@-*/
}
?>
