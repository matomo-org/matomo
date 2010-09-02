<?php
/**
 * Javascript aggregator and builder class
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
 * @version    SVN: $Id: JavascriptBuilder.php 299480 2010-05-19 06:55:03Z avb $
 * @link       http://pear.php.net/package/HTML_QuickForm2
 */

/**
 * Exception classes for HTML_QuickForm2
 */
// require_once 'HTML/QuickForm2/Exception.php';
require_once dirname(__FILE__) . '/Exception.php';

/**
 * Javascript aggregator and builder class
 *
 * @category   HTML
 * @package    HTML_QuickForm2
 * @author     Alexey Borzov <avb@php.net>
 * @author     Bertrand Mansion <golgote@mamasam.com>
 * @version    Release: @package_version@
 */
class HTML_QuickForm2_JavascriptBuilder
{
   /**
    * Client-side rules
    * @var array
    */
    protected $rules = array();

   /**
    * Current form ID
    * @var string
    */
    protected $formId = null;

   /**
    * Sets the form currently being processed
    *
    * @param HTML_QuickForm2
    */
    public function startForm(HTML_QuickForm2 $form)
    {
        $this->formId = $form->getId();
        $this->rules[$this->formId] = array();
    }

   /**
    * Adds the Rule javascript to the list of current form Rules
    *
    * @param HTML_QuickForm2_Rule
    */
    public function addRule(HTML_QuickForm2_Rule $rule)
    {
        $this->rules[$this->formId][] = $rule->getJavascript();
    }

   /**
    * Returns client-side validation code
    *
    * @todo This shouldn't probably be __toString() as we can't throw exceptions from that
    * @todo Of course we shouldn't put library files into each page, need some means to include them via <script> tags
    */
    public function __toString()
    {
        $js = '';
        foreach ($this->rules as $formId => $rules) {
            if (!empty($rules)) {
                $js .= "new qf.validator(document.getElementById('{$formId}'), [\n" .
                       implode(",\n", $rules) .
                       "\n]);";
            }
        }
        if ('' != $js) {
            $js = "<script type=\"text/javascript\">\n//<![CDATA[\n" .
                  file_get_contents('@data_dir@/HTML_QuickForm2/quickform.js') .
                  "qf.events.contentReady(function() {\n{$js}\n});\n" .
                  "//]]>\n</script>";
        }
        return $js;
    }
}
?>
