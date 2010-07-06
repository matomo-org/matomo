<?php
/**
 * Rule checking that uploaded file is of the correct MIME type
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
 * @version    SVN: $Id: MimeType.php 294057 2010-01-26 21:10:28Z avb $
 * @link       http://pear.php.net/package/HTML_QuickForm2
 */

/**
 * Base class for HTML_QuickForm2 rules
 */
// require_once 'HTML/QuickForm2/Rule.php';

/**
 * Rule checking that uploaded file is of the correct MIME type
 *
 * The Rule needs one configuration parameter for its work: a string with a
 * desired MIME type or array of such strings. This parameter can be passed
 * either to {@link HTML_QuickForm2_Rule::__construct() the Rule constructor}
 * as local configuration or to {@link HTML_QuickForm2_Factory::registerRule()}
 * as global one. As usual, global configuration overrides local one.
 *
 * The Rule considers missing file uploads (UPLOAD_ERR_NO_FILE) valid.
 *
 * @category   HTML
 * @package    HTML_QuickForm2
 * @author     Alexey Borzov <avb@php.net>
 * @author     Bertrand Mansion <golgote@mamasam.com>
 * @version    Release: @package_version@
 */
class HTML_QuickForm2_Rule_MimeType extends HTML_QuickForm2_Rule
{
   /**
    * Validates the owner element
    *
    * @return   bool    whether uploaded file's MIME type is correct
    */
    protected function validateOwner()
    {
        $value = $this->owner->getValue();
        if (!isset($value['error']) || UPLOAD_ERR_NO_FILE == $value['error']) {
            return true;
        }
        $mime = $this->getConfig();
        return is_array($mime)? in_array($value['type'], $mime):
                                $value['type'] == $mime;
    }

   /**
    * Sets allowed MIME type(s) for the uploaded file
    *
    * @param    string|array    Allowed MIME type or an array of types
    * @return   HTML_QuickForm2_Rule
    * @throws   HTML_QuickForm2_InvalidArgumentException    if bogus configuration provided
    */
    public function setConfig($config)
    {
        if (0 == count($config) || !is_string($config) && !is_array($config)) {
            throw new HTML_QuickForm2_InvalidArgumentException(
                'MimeType Rule requires MIME type(s), ' .
                preg_replace('/\s+/', ' ', var_export($config, true)) . ' given'
            );
        }
        return parent::setConfig($config);
    }

   /**
    * Sets the element that will be validated by this rule
    *
    * @param    HTML_QuickForm2_Element_InputFile   File upload field to validate
    * @throws   HTML_QuickForm2_InvalidArgumentException    if trying to use
    *           this Rule on something that isn't a file upload field
    */
    public function setOwner(HTML_QuickForm2_Node $owner)
    {
        if (!$owner instanceof HTML_QuickForm2_Element_InputFile) {
            throw new HTML_QuickForm2_InvalidArgumentException(
                'MimeType Rule can only validate file upload fields, '.
                get_class($owner) . ' given'
            );
        }
        parent::setOwner($owner);
    }
}
?>
