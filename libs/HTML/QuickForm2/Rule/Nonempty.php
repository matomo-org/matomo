<?php
/**
 * Rule checking that the field is not empty
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
 * @version    SVN: $Id: Nonempty.php 299706 2010-05-24 18:32:37Z avb $
 * @link       http://pear.php.net/package/HTML_QuickForm2
 */

/**
 * Base class for HTML_QuickForm2 rules
 */
// require_once 'HTML/QuickForm2/Rule.php';

/**
 * Rule checking that the field is not empty
 *
 * Handles simple form fields, file uploads and Containers.
 *
 * When validating <select multiple> fields and Containers it may use an
 * optional configuration parameter for minimum number of nonempty values,
 * defaulting to 1. It can be passed either to
 * {@link HTML_QuickForm2_Rule::__construct() the Rule constructor} as local
 * configuration or to {@link HTML_QuickForm2_Factory::registerRule()} as
 * global one. As usual, global configuration overrides local.
 *
 * <code>
 * // Required rule is 'nonempty' with a bit of special handling
 * $login->addRule('required', 'Please provide your login');
 * $multiSelect->addRule('required', 'Please select at least two options', 2);
 * </code>
 *
 * @category   HTML
 * @package    HTML_QuickForm2
 * @author     Alexey Borzov <avb@php.net>
 * @author     Bertrand Mansion <golgote@mamasam.com>
 * @version    Release: @package_version@
 */
class HTML_QuickForm2_Rule_Nonempty extends HTML_QuickForm2_Rule
{
    protected function validateOwner()
    {
        if ($this->owner instanceof HTML_QuickForm2_Container) {
            $nonempty = 0;
            foreach ($this->owner->getRecursiveIterator(RecursiveIteratorIterator::LEAVES_ONLY) as $child) {
                $rule = new self($child);
                if ($rule->validateOwner()) {
                    $nonempty++;
                }
            }
            return $nonempty >= $this->getConfig();
        }

        $value = $this->owner->getValue();
        if ($this->owner instanceof HTML_QuickForm2_Element_InputFile) {
            return isset($value['error']) && (UPLOAD_ERR_OK == $value['error']);
        } elseif (is_array($value)) {
            return count(array_filter($value, 'strlen')) >= $this->getConfig();
        } else {
            return (bool)strlen($value);
        }
    }

   /**
    * Sets minimum number of nonempty values
    *
    * This is useful for multiple selects and Containers, will be ignored for
    * all other elements. Defaults to 1, thus multiple select will be
    * considered not empty if at least one option is selected, Container will
    * be considered not empty if at least one contained element is not empty.
    *
    * @param    int     Maximum allowed size
    * @return   HTML_QuickForm2_Rule
    * @throws   HTML_QuickForm2_InvalidArgumentException    if a bogus size limit was provided
    */
    public function setConfig($config)
    {
        if (is_null($config)) {
            $config = 1;
        } elseif (1 > intval($config)) {
            throw new HTML_QuickForm2_InvalidArgumentException(
                'Nonempty Rule accepts a positive count of nonempty values, ' .
                preg_replace('/\s+/', ' ', var_export($config, true)) . ' given'
            );
        }
        return parent::setConfig(intval($config));
    }

    protected function getJavascriptCallback()
    {
        $js = "function() {var value = " . $this->owner->getJavascriptValue() . ";";
        if (!$this->owner instanceof HTML_QuickForm2_Container) {
            $js .= " if (!value instanceof Array) { return value != ''; } else { " .
                   "var valid = 0; for (var i = 0; i < value.length; i++) { " .
                   "if ('' != value[i]) { valid++; } } return valid >= " . $this->getConfig() . "; } }";
        } else {
            $js .= " var values = value.getValues(); var valid = 0; " .
                   "for (var i = 0; i < values.length; i++) { " .
                   "if ('' != values[i]) { valid++; } } return valid >= " . $this->getConfig() . "; }";
        }
        return $js;
    }
}

?>
