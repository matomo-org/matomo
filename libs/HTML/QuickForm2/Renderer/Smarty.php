<?php
/**
 * A renderer for HTML_QuickForm2 suitable for using with the Smarty template engine.
 * See: http://www.smarty.net/
 *
 * PHP version 5
 *
 * LICENSE:
 *
 * Copyright (c) 2009, Alain D D Williams <addw@phcomp.co.uk>
 * Based on the QuickForm2 Array renderer.
 *
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
 * @author     Alain D D Williams <addw@phcomp.co.uk>
 * @license    http://opensource.org/licenses/bsd-license.php New BSD License
 * @version    SCCS: %W% %G% %U%
 * @link       http://pear.php.net/package/HTML_QuickForm2
 */

/**
 * This generates an array, bring in the array renderer and extend it a bit:
 */
// require_once 'HTML/QuickForm2/Renderer/Array.php';

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
 *   'requirednote'      => note about the required elements (string),
 *                           NB: no '_' in the middle
 *                           In old_compat this is a span style="font-size:80%;"
 *                           with the '*' also color:#ff0000;
 *                           Not old_compat it is in a div class="reqnote"
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
 *   '1st_elements_name'  => array for the 1st element,
 *     ...                   references into 'elements' above
 *   'nth_elements_name'  => array for the nth element,
 *   )
 * );
 * </pre>
 * Where element_i is an array of the form
 * <pre>
 * array(
 *   'id'        => element id (string),
 *   'name'      => element name (string),
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
 *
 * If the type is 'radio' an element (type = 'radio') is created for each choice that the user has,
 * keyed by the 'id' value. An 'element' will be created having an array with one element key '0'.
 * The 'id' above will be set to the value in 'name'.
 * The 'type' of each element will be 'radio'
 * );
 * </pre>
 *
 * The following additional options are available:
 * <ul>
 *   <li>'old_compat'    - generate something compatible with an old renderer</li>
 *   <li>'key_id'        - the key to elements is the field's 'id' rather than 'name'</li>
 * </ul>
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
 * @author     Alain D D Williams <addw@phcomp.co.uk>
 * @version    Release: SCCS: %W% %G% %U%
 */
class HTML_QuickForm2_Renderer_Smarty extends HTML_QuickForm2_Renderer_Array
{
   /**
    * Constructor, adds new options
    */
    protected function __construct()
    {
        parent::__construct();
        $this->options += array(
            'old_compat' => false,
            'key_id'     => false,
            );
    }

   /**
    * Creates an array with fields that are common to all elements
    *
    * @param    HTML_QuickForm2_Node    Element being rendered
    *
    * @return   array
    */
    public function buildCommonFields(HTML_QuickForm2_Node $element)
    {
        $keyn = $this->options['key_id'] ? 'id' : 'name';

        $ary = array(
            'id'     => $element->getId(),
            'frozen' => $element->toggleFrozen(),
            'name'   => $element->getName(),
        );

        // Key that we use for putting into arrays so that smarty can extract them.
        // Note that the name may be empty.
        $key_val = $ary[$keyn];
        if($key_val == '')
            $key_val = $ary['id'];

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

        // Smarty: group_errors under 'name' or 'id' depending on key_id option:
        if (($error = $element->getError()) && $this->options['group_errors']) {
            $this->array['errors'][$key_val] = $error;
        } elseif ($error) {
            $ary['error'] = $error;
        }
        if (isset($this->styles[$key_val])) {
            $ary['style'] = $this->styles[$key_val];
        }
        if (!$element instanceof HTML_QuickForm2_Container) {
            $ary['html']       = $element->__toString();
        } else {
            $ary['elements']   = array();
            $ary['attributes'] = $element->getAttributes(true);
        }
        return $ary;
    }

    public function startForm(HTML_QuickForm2_Node $form)
    {
        if($this->options['old_compat'])
            $this->options['group_hiddens'] = true;

        parent::startForm($form);
    }

    public function finishForm(HTML_QuickForm2_Node $form)
    {
        parent::finishForm($form);

        if ($this->hasRequired) {

            // Create element 'requirednote' - note no '_'
            if($this->options['old_compat']) {
                // Old QuickForm had the requirednote styled & a different name:
                $this->array['requirednote'] = preg_replace('|<em>([^<]+)</em>(.*)|',
                    '<span style="font-size:80%; color:#ff0000;">$1</span><span style="font-size:80%;">$2</span>',
                    $this->options['required_note']);
            } else {
                $this->array['requirednote'] = '<div class="reqnote">'. $this->options['required_note'] . '</div>';
            }
        }

        // Create top level elements keyed by form field 'name' or 'id'
        if(isset($this->array['elements']['0']))
            $this->linkToLevelAbove($this->array, $this->array['elements']);

        // For compat: it is expected that 'hidden' is a string, not an array:
        if($this->options['old_compat'] && isset($this->array['hidden']) && is_array($this->array['hidden']))
            $this->array['hidden'] = join(' ', $this->array['hidden']);
    }

    // Look through the elements (numerically indexed) array, make fields
    // members of the level above. This is so that they can be easily accessed by smarty templates.
    // If we find a group, recurse down. Used for smarty only.
    // Key is 'name' or 'id'.
    private function linkToLevelAbove(&$top, $elements, $inGroup = false)
    {
        $key = $this->options['key_id'] ? 'id' : 'name';

        foreach($elements as &$elem) {
            $top_key = $elem[$key];

            // If in a group, convert something like inGrp[F4grp][F4_1] to F4_1
            // Don't do if key_id as the value is a straight id.
            if( !$this->options['key_id'] && $inGroup && $top_key != '') {
                if(!(preg_match("/\[?([\w_]+)\]?$/i", $top_key, $match)))
                    throw new HTML_QuickForm2_InvalidArgumentException(
                        "linkToLevelAbove can't obtain the name from '$top_key'");
                $top_key = $match[1];
            }

            // Radio buttons: several elements with the same name, make an array
            if(isset($elem['type']) && $elem['type'] == 'radio') {
                if( ! isset($top[$top_key]))
                    $top[$top_key] = array('id' => $top_key, 'type' => 'radio', 'elements' => array(0));
                $top[$top_key][$elem['id']] = &$elem;
            } else    // Normal field, just link into the level above.
                if( ! isset($top[$top_key]))
                    $top[$top_key] = &$elem;    // Link into the level above

            // If we have a group link its fields up to this level:
            if(isset($elem['elements']['0']))
                $this->linkToLevelAbove($elem, $elem['elements'], true);

            // Link errors to the top level:
            if(isset($elem['error']) && isset($this->array[$elem['error']]))
                $this->array['errors'][$top_key] = $this->array[$elem['error']];
        }
    }

    /**#@-*/
}
