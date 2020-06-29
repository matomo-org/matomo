<?php
/**
 * Base class for checkboxes and radios
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
 * @version    SVN: $Id: InputCheckable.php 300722 2010-06-24 10:15:52Z mansion $
 * @link       http://pear.php.net/package/HTML_QuickForm2
 */

/**
 * Base class for <input> elements
 */
// require_once 'HTML/QuickForm2/Element/Input.php';

/**
 * Base class for <input> elements having 'checked' attribute (checkboxes and radios)
 *
 * @category   HTML
 * @package    HTML_QuickForm2
 * @author     Alexey Borzov <avb@php.net>
 * @author     Bertrand Mansion <golgote@mamasam.com>
 * @version    Release: @package_version@
 */
class HTML_QuickForm2_Element_InputCheckable extends HTML_QuickForm2_Element_Input
{
    protected $persistent = true;

   /**
    * HTML to represent the element in "frozen" state
    *
    * Array index "checked" contains HTML for element's "checked" state,
    * "unchecked" for not checked
    * @var  array
    */
    protected $frozenHtml = array(
        'checked'   => 'On',
        'unchecked' => 'Off'
    );

   /**
    * Contains options and data used for the element creation
    * - content: Label "glued" to a checkbox or radio
    * @var  array
    */
    protected $data = array('content' => '');

    public function __construct($name = null, $attributes = null, $data = null)
    {
        parent::__construct($name, $attributes, $data);
        // "checked" attribute should be updated on changes to "value" attribute
        // see bug #15708
        $this->watchedAttributes[] = 'value';
    }

    protected function onAttributeChange($name, $value = null)
    {
        if ('value' != $name) {
            return parent::onAttributeChange($name, $value);
        }
        if (null === $value) {
            unset($this->attributes['value'], $this->attributes['checked']);
        } else {
            $this->attributes['value'] = $value;
            $this->updateValue();
        }
    }


   /**
    * Sets the label to be rendered glued to the element
    *
    * This label is returned by {@link __toString()} method with the element's
    * HTML. It is automatically wrapped into the <label> tag.
    *
    * @param    string
    * @return   HTML_QuickForm2_Element_InputCheckable
    */
    public function setContent($content)
    {
        $this->data['content'] = $content;
        return $this;
    }

   /**
    * Returns the label that will be "glued" to element's HTML
    *
    * @return   string
    */
    public function getContent()
    {
        return $this->data['content'];
    }


    public function setValue($value)
    {
        if ((string)$value == $this->getAttribute('value')) {
            return $this->setAttribute('checked');
        } else {
            return $this->removeAttribute('checked');
        }
    }

    public function getValue()
    {
        if (!empty($this->attributes['checked']) && empty($this->attributes['disabled'])) {
            return $this->applyFilters($this->getAttribute('value'));
        } else {
            return null;
        }
    }

    public function __toString()
    {
        if (0 == strlen($this->data['content'])) {
            $label = '';
        } elseif ($this->frozen) {
            $label = $this->data['content'];
        } else {
            return '<label>' . parent::__toString() . '<span>' . $this->data['content'] . '</span></label>';
        }
        return parent::__toString() . $label;
    }

    public function getFrozenHtml()
    {
        if ($this->getAttribute('checked')) {
            return $this->frozenHtml['checked'] . $this->getPersistentContent();
        } else {
            return $this->frozenHtml['unchecked'];
        }
    }
}
?>
