<?php
/**
 * Base class for <input> elements
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
 * @version    SVN: $Id: Input.php 300722 2010-06-24 10:15:52Z mansion $
 * @link       http://pear.php.net/package/HTML_QuickForm2
 */

/**
 * Base class for simple HTML_QuickForm2 elements (not Containers)
 */
// require_once 'HTML/QuickForm2/Element.php';

/**
 * Base class for <input> elements
 *
 * @category   HTML
 * @package    HTML_QuickForm2
 * @author     Alexey Borzov <avb@php.net>
 * @author     Bertrand Mansion <golgote@mamasam.com>
 * @version    Release: @package_version@
 */
class HTML_QuickForm2_Element_Input extends HTML_QuickForm2_Element
{
   /**
    * 'type' attribute should not be changeable
    * @var array
    */
    protected $watchedAttributes = array('id', 'name', 'type');

    protected function onAttributeChange($name, $value = null)
    {
        if ('type' == $name) {
            throw new HTML_QuickForm2_InvalidArgumentException(
                "Attribute 'type' is read-only"
            );
        }
        parent::onAttributeChange($name, $value);
    }

    public function getType()
    {
        return $this->attributes['type'];
    }

    public function setValue($value)
    {
        $this->setAttribute('value', $value);
        return $this;
    }

    public function getValue()
    {
        return $this->getAttribute('disabled')? null: $this->applyFilters($this->getAttribute('value'));
    }

    public function __toString()
    {
        if ($this->frozen) {
            return $this->getFrozenHtml();
        } else {
            return '<input' . $this->getAttributes(true) . ' />';
        }
    }

   /**
    * Returns the field's value without HTML tags
    * @return string
    */
    protected function getFrozenHtml()
    {
        $value = $this->getAttribute('value');
        return (('' != $value)? htmlspecialchars($value, ENT_QUOTES, self::getOption('charset')): '&nbsp;') .
               $this->getPersistentContent();
    }
}
?>
