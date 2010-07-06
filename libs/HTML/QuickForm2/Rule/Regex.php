<?php
/**
 * Validates values using regular expressions
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
 * @version    SVN: $Id: Regex.php 299480 2010-05-19 06:55:03Z avb $
 * @link       http://pear.php.net/package/HTML_QuickForm2
 */

/**
 * Base class for HTML_QuickForm2 rules
 */
// require_once 'HTML/QuickForm2/Rule.php';

/**
 * Validates values using regular expressions
 *
 * The Rule needs one configuration parameter for its work: a Perl-compatible
 * regular expression. This parameter can be passed either to
 * {@link HTML_QuickForm2_Rule::__construct() the Rule constructor} as local
 * configuration or to {@link HTML_QuickForm2_Factory::registerRule()}
 * as global one. As usual, global configuration overrides local one.
 *
 * The Rule can also validate file uploads, in this case the regular expression
 * is applied to upload's 'name' field.
 *
 * The Rule considers empty fields (file upload fields with UPLOAD_ERR_NO_FILE)
 * as valid and doesn't try to test them with the regular expression.
 *
 * @category   HTML
 * @package    HTML_QuickForm2
 * @author     Alexey Borzov <avb@php.net>
 * @author     Bertrand Mansion <golgote@mamasam.com>
 * @version    Release: @package_version@
 */
class HTML_QuickForm2_Rule_Regex extends HTML_QuickForm2_Rule
{
   /**
    * Validates the owner element
    *
    * @return   bool    whether element's value matches given regular expression
    */
    protected function validateOwner()
    {
        $value = $this->owner->getValue();
        if ($this->owner instanceof HTML_QuickForm2_Element_InputFile) {
            if (!isset($value['error']) || UPLOAD_ERR_NO_FILE == $value['error']) {
                return true;
            }
            $value = $value['name'];
        } elseif (!strlen($value)) {
            return true;
        }
        return preg_match($this->getConfig() . 'D', $value);
    }

   /**
    * Sets the regular expression to validate with
    *
    * @param    string  Regular expression
    * @return   HTML_QuickForm2_Rule
    * @throws   HTML_QuickForm2_InvalidArgumentException    if $config is not a string
    */
    public function setConfig($config)
    {
        if (!is_string($config)) {
            throw new HTML_QuickForm2_InvalidArgumentException(
                'Regex Rule requires a regular expression, ' .
                preg_replace('/\s+/', ' ', var_export($config, true)) . ' given'
            );
        }
        return parent::setConfig($config);
    }

   /**
    * Returns the client-side validation callback
    *
    * For this to work properly, slashes have to be used as regex delimiters.
    * The method takes care of transforming PHP unicode escapes in regexps to
    * JS unicode escapes if using 'u' modifier (see bug #12376)
    *
    * @return   string
    */
    protected function getJavascriptCallback()
    {
        $regex = $this->getConfig();

        if ($pos = strpos($regex, 'u', strrpos($regex, '/'))) {
            $regex = substr($regex, 0, $pos) . substr($regex, $pos + 1);
            $regex = preg_replace('/(?<!\\\\)(?>\\\\\\\\)*\\\\x{([a-fA-F0-9]+)}/', '\\u$1', $regex);
        }

        return "function() { var regex = {$regex}; var value = " . $this->owner->getJavascriptValue() .
               "; return value == '' || regex.test(value); }";
    }
}
?>
