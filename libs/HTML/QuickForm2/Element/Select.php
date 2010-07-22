<?php
/**
 * Classes for <select> elements
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
 * @license    http://opensource.org/licenses/bsd-license.php New BSD License
 * @version    SVN: $Id: Select.php 300722 2010-06-24 10:15:52Z mansion $
 * @link       http://pear.php.net/package/HTML_QuickForm2
 */

/**
 * Base class for simple HTML_QuickForm2 elements
 */
// require_once 'HTML/QuickForm2/Element.php';


/**
 * Collection of <option>s and <optgroup>s
 *
 * This class handles the output of <option> tags. The class is not intended to
 * be used directly.
 *
 * @category   HTML
 * @package    HTML_QuickForm2
 * @author     Alexey Borzov <avb@php.net>
 * @author     Bertrand Mansion <golgote@mamasam.com>
 * @version    Release: @package_version@
 */
class HTML_QuickForm2_Element_Select_OptionContainer extends HTML_Common2
    implements IteratorAggregate, Countable
{
   /**
    * List of options and optgroups in this container
    *
    * Options are stored as arrays (for performance reasons), optgroups as
    * instances of Optgroup class.
    *
    * @var array
    */
    protected $options = array();

   /**
    * Reference to parent <select>'s values
    * @var array
    */
    protected $values;

   /**
    * Reference to parent <select>'s possible values
    * @var array
    */
    protected $possibleValues;


   /**
    * Class constructor
    *
    * @param    array   Reference to values of parent <select> element
    * @param    array   Reference to possible values of parent <select> element
    */
    public function __construct(&$values, &$possibleValues)
    {
        $this->values         =& $values;
        $this->possibleValues =& $possibleValues;
    }

   /**
    * Adds a new option
    *
    * Please note that if you pass 'selected' attribute in the $attributes
    * parameter then this option's value will be added to <select>'s values.
    *
    * @param    string  Option text
    * @param    string  'value' attribute for <option> tag
    * @param    mixed   Additional attributes for <option> tag (either as a
    *                   string or as an associative array)
    */
    public function addOption($text, $value, $attributes = null)
    {
        if (null === $attributes) {
            $attributes = array('value' => (string)$value);
        } else {
            $attributes = self::prepareAttributes($attributes);
            if (isset($attributes['selected'])) {
                // the 'selected' attribute will be set in __toString()
                unset($attributes['selected']);
                if (!in_array($value, $this->values)) {
                    $this->values[] = $value;
                }
            }
            $attributes['value'] = (string)$value;
        }
        if (!isset($attributes['disabled'])) {
            $this->possibleValues[(string)$value] = true;
        }
        $this->options[] = array('text' => $text, 'attr' => $attributes);
    }

   /**
    * Adds a new optgroup
    *
    * @param    string  'label' attribute for optgroup tag
    * @param    mixed   Additional attributes for <optgroup> tag (either as a
    *                   string or as an associative array)
    * @return   HTML_QuickForm2_Element_Select_Optgroup
    */
    public function addOptgroup($label, $attributes = null)
    {
        $optgroup = new HTML_QuickForm2_Element_Select_Optgroup(
                            $this->values, $this->possibleValues,
                            $label, $attributes
                        );
        $this->options[] = $optgroup;
        return $optgroup;
    }

   /**
    * Returns an array of contained options
    *
    * @return   array
    */
    public function getOptions()
    {
        return $this->options;
    }

    public function __toString()
    {
        $indentLvl = $this->getIndentLevel();
        $indent    = $this->getIndent() . self::getOption('indent');
        $linebreak = self::getOption('linebreak');
        $html      = '';
        $strValues = array_map('strval', $this->values);
        foreach ($this->options as $option) {
            if (is_array($option)) {
                if (in_array($option['attr']['value'], $strValues, true)) {
                    $option['attr']['selected'] = 'selected';
                }
                $html .= $indent . '<option' .
                         self::getAttributesString($option['attr']) .
                         '>' . $option['text'] . '</option>' . $linebreak;
            } elseif ($option instanceof HTML_QuickForm2_Element_Select_OptionContainer) {
                $option->setIndentLevel($indentLvl + 1);
                $html .= $option->__toString();
            }
        }
        return $html;
    }

   /**
    * Returns an iterator over contained elements
    *
    * @return   HTML_QuickForm2_Element_Select_OptionIterator
    */
    public function getIterator()
    {
        return new HTML_QuickForm2_Element_Select_OptionIterator($this->options);
    }

   /**
    * Returns a recursive iterator over contained elements
    *
    * @return   RecursiveIteratorIterator
    */
    public function getRecursiveIterator()
    {
        return new RecursiveIteratorIterator(
            new HTML_QuickForm2_Element_Select_OptionIterator($this->options),
            RecursiveIteratorIterator::SELF_FIRST
        );
    }

   /**
    * Returns the number of options in the container
    *
    * @return   int
    */
    public function count()
    {
        return count($this->options);
    }
}


/**
 * Class representing an <optgroup> tag
 *
 * Do not instantiate this class yourself, use
 * {@link HTML_QuickForm2_Element_Select::addOptgroup()} method
 *
 * @category   HTML
 * @package    HTML_QuickForm2
 * @author     Alexey Borzov <avb@php.net>
 * @author     Bertrand Mansion <golgote@mamasam.com>
 * @version    Release: @package_version@
 */
class HTML_QuickForm2_Element_Select_Optgroup
    extends HTML_QuickForm2_Element_Select_OptionContainer
{
   /**
    * Class constructor
    *
    * @param    array   Reference to values of parent <select> element
    * @param    array   Reference to possible values of parent <select> element
    * @param    string  'label' attribute for optgroup tag
    * @param    mixed   Additional attributes for <optgroup> tag (either as a
    *                   string or as an associative array)
    */
    public function __construct(&$values, &$possibleValues, $label, $attributes = null)
    {
        parent::__construct($values, $possibleValues);
        $this->setAttributes($attributes);
        $this->attributes['label'] = (string)$label;
    }

    public function __toString()
    {
        $indent    = $this->getIndent();
        $linebreak = self::getOption('linebreak');
        return $indent . '<optgroup' . $this->getAttributes(true) . '>' .
               $linebreak . parent::__toString() . $indent . '</optgroup>' . $linebreak;
    }
}

/**
 * Implements a recursive iterator for options arrays
 *
 * @category   HTML
 * @package    HTML_QuickForm2
 * @author     Alexey Borzov <avb@php.net>
 * @author     Bertrand Mansion <golgote@mamasam.com>
 * @version    Release: @package_version@
 */
class HTML_QuickForm2_Element_Select_OptionIterator extends RecursiveArrayIterator
    implements RecursiveIterator
{
    public function hasChildren()
    {
        return $this->current() instanceof HTML_QuickForm2_Element_Select_OptionContainer;
    }

    public function getChildren()
    {
        return new HTML_QuickForm2_Element_Select_OptionIterator(
            $this->current()->getOptions()
        );
    }
}


/**
 * Class representing a <select> element
 *
 * @category   HTML
 * @package    HTML_QuickForm2
 * @author     Alexey Borzov <avb@php.net>
 * @author     Bertrand Mansion <golgote@mamasam.com>
 * @version    Release: @package_version@
 */
class HTML_QuickForm2_Element_Select extends HTML_QuickForm2_Element
{
    protected $persistent = true;

   /**
    * Values for the select element (i.e. values of the selected options)
    * @var  array
    */
    protected $values = array();

   /**
    * Possible values for select elements
    *
    * A value is considered possible if it is present as a value attribute of
    * some option and that option is not disabled.
    * @var array
    */
    protected $possibleValues = array();


   /**
    * Object containing options for the <select> element
    * @var  HTML_QuickForm2_Element_Select_OptionContainer
    */
    protected $optionContainer;

   /**
    * Enable intrinsic validation by default
    * @var  array
    */
    protected $data = array('intrinsic_validation' => true);

   /**
    * Class constructor
    *
    * Select element can understand the following keys in $data parameter:
    *   - 'options': data to populate element's options with. Passed to
    *     {@link loadOptions()} method.
    *   - 'intrinsic_validation': setting this to false will disable
    *     that validation, {@link getValue()} will then return all submit
    *     values, not just those corresponding to options present in the
    *     element. May be useful in AJAX scenarios.
    *
    * @param    string  Element name
    * @param    mixed   Attributes (either a string or an array)
    * @param    array   Additional element data
    * @throws   HTML_QuickForm2_InvalidArgumentException    if junk is given in $options
    */
    public function __construct($name = null, $attributes = null, array $data = array())
    {
        $options = isset($data['options'])? $data['options']: array();
        unset($data['options']);
        parent::__construct($name, $attributes, $data);
        $this->loadOptions($options);
    }

    public function getType()
    {
        return 'select';
    }

    public function __toString()
    {
        if ($this->frozen) {
            return $this->getFrozenHtml();
        } else {
            if (empty($this->attributes['multiple'])) {
                $attrString = $this->getAttributes(true);
            } else {
                $this->attributes['name'] .= '[]';
                $attrString = $this->getAttributes(true);
                $this->attributes['name']  = substr($this->attributes['name'], 0, -2);
            }
            $indent = $this->getIndent();
            return $indent . '<select' . $attrString . '>' .
                   self::getOption('linebreak') .
                   $this->optionContainer->__toString() .
                   $indent . '</select>';
        }
    }

    protected function getFrozenHtml()
    {
        if (null === ($value = $this->getValue())) {
            return '&nbsp;';
        }
        $valueHash = is_array($value)? array_flip($value): array($value => true);
        $options   = array();
        foreach ($this->optionContainer->getRecursiveIterator() as $child) {
            if (is_array($child) && isset($valueHash[$child['attr']['value']]) &&
                empty($child['attr']['disabled']))
            {
                $options[] = $child['text'];
            }
        }

        $html = implode('<br />', $options);
        if ($this->persistent) {
            $name = $this->attributes['name'] .
                    (empty($this->attributes['multiple'])? '': '[]');
            // Only use id attribute if doing single hidden input
            $idAttr = (1 == count($valueHash))? array('id' => $this->getId()): array();
            foreach ($valueHash as $key => $item) {
                $html .= '<input type="hidden"' . self::getAttributesString(array(
                             'name'  => $name,
                             'value' => $key
                         ) + $idAttr) . ' />';
            }
        }
        return $html;
    }

   /**
    * Returns the value of the <select> element
    *
    * Please note that the returned value may not necessarily be equal to that
    * passed to {@link setValue()}. It passes "intrinsic validation" confirming
    * that such value could possibly be submitted by this <select> element.
    * Specifically, this method will return null if the elements "disabled"
    * attribute is set, it will not return values if there are no options having
    * such a "value" attribute or if such options' "disabled" attribute is set.
    * It will also only return a scalar value for single selects, mimicking
    * the common browsers' behaviour.
    *
    * @return   mixed   "value" attribute of selected option in case of single
    *                   select, array of selected options' "value" attributes in
    *                   case of multiple selects, null if no options selected
    */
    public function getValue()
    {
        if (!empty($this->attributes['disabled']) || 0 == count($this->values)
            || ($this->data['intrinsic_validation']
                && (0 == count($this->optionContainer) || 0 == count($this->possibleValues)))
        ) {
            return null;
        }

        $values = array();
        foreach ($this->values as $value) {
            if (!$this->data['intrinsic_validation'] || !empty($this->possibleValues[$value])) {
                $values[] = $value;
            }
        }
        if (0 == count($values)) {
            return null;
        } elseif (!empty($this->attributes['multiple'])) {
            return $this->applyFilters($values);
        } elseif (1 == count($values)) {
            return $this->applyFilters($values[0]);
        } else {
            // The <select> is not multiple, but several options are to be
            // selected. At least IE and Mozilla select the last selected
            // option in this case, we should do the same
            foreach ($this->optionContainer->getRecursiveIterator() as $child) {
                if (is_array($child) && in_array($child['attr']['value'], $values)) {
                    $lastValue = $child['attr']['value'];
                }
            }
            return $this->applyFilters($lastValue);
        }
    }

    public function setValue($value)
    {
        if (is_array($value)) {
            $this->values = array_values($value);
        } else {
            $this->values = array($value);
        }
        return $this;
    }

   /**
    * Loads <option>s (and <optgroup>s) for select element
    *
    * The method expects a array of options and optgroups:
    * <pre>
    * array(
    *     'option value 1' => 'option text 1',
    *     ...
    *     'option value N' => 'option text N',
    *     'optgroup label 1' => array(
    *         'option value' => 'option text',
    *         ...
    *     ),
    *     ...
    * )
    * </pre>
    * If value is a scalar, then array key is treated as "value" attribute of
    * <option> and value as this <option>'s text. If value is an array, then
    * key is treated as a "label" attribute of <optgroup> and value as an
    * array of <option>s for this <optgroup>.
    *
    * If you need to specify additional attributes for <option> and <optgroup>
    * tags, then you need to use {@link addOption()} and {@link addOptgroup()}
    * methods instead of this one.
    *
    * @param    array
    * @throws   HTML_QuickForm2_InvalidArgumentException    if junk is given in $options
    * @return   HTML_QuickForm2_Element_Select
    */
    public function loadOptions(array $options)
    {
        $this->possibleValues  = array();
        $this->optionContainer = new HTML_QuickForm2_Element_Select_OptionContainer(
                                     $this->values, $this->possibleValues
                                 );
        $this->loadOptionsFromArray($this->optionContainer, $options);
        return $this;
    }


   /**
    * Adds options from given array into given container
    *
    * @param    HTML_QuickForm2_Element_Select_OptionContainer  options will be
    *           added to this container
    * @param    array   options array
    */
    protected function loadOptionsFromArray(
        HTML_QuickForm2_Element_Select_OptionContainer $container, $options
    )
    {
        foreach ($options as $key => $value) {
            if (is_array($value)) {
                $optgroup = $container->addOptgroup($key);
                $this->loadOptionsFromArray($optgroup, $value);
            } else {
                $container->addOption($value, $key);
            }
        }
    }


   /**
    * Adds a new option
    *
    * Please note that if you pass 'selected' attribute in the $attributes
    * parameter then this option's value will be added to <select>'s values.
    *
    * @param    string  Option text
    * @param    string  'value' attribute for <option> tag
    * @param    mixed   Additional attributes for <option> tag (either as a
    *                   string or as an associative array)
    */
    public function addOption($text, $value, $attributes = null)
    {
        return $this->optionContainer->addOption($text, $value, $attributes);
    }

   /**
    * Adds a new optgroup
    *
    * @param    string  'label' attribute for optgroup tag
    * @param    mixed   Additional attributes for <optgroup> tag (either as a
    *                   string or as an associative array)
    * @return   HTML_QuickForm2_Element_Select_Optgroup
    */
    public function addOptgroup($label, $attributes = null)
    {
        return $this->optionContainer->addOptgroup($label, $attributes);
    }

    public function updateValue()
    {
        if (!$this->getAttribute('multiple')) {
            parent::updateValue();
        } else {
            $name = $this->getName();
            foreach ($this->getDataSources() as $ds) {
                if (null !== ($value = $ds->getValue($name)) ||
                    $ds instanceof HTML_QuickForm2_DataSource_Submit)
                {
                    $this->setValue(null === $value? array(): $value);
                    return;
                }
            }
        }
    }
}
?>
