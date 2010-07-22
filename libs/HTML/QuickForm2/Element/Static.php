<?php
/**
 * Class for static elements that only contain text or markup
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
 * @version    SVN: $Id: Static.php 299206 2010-05-10 10:21:10Z mansion $
 * @link       http://pear.php.net/package/HTML_QuickForm2
 */

/**
 * Base class for simple HTML_QuickForm2 elements (not Containers)
 */
// require_once 'HTML/QuickForm2/Element.php';

/**
 * Class for static elements that only contain text or markup
 *
 * @category   HTML
 * @package    HTML_QuickForm2
 * @author     Alexey Borzov <avb@php.net>
 * @author     Bertrand Mansion <golgote@mamasam.com>
 * @version    Release: @package_version@
 */
class HTML_QuickForm2_Element_Static extends HTML_QuickForm2_Element
{

   /**
    * Contains options and data used for the element creation
    * - content: Content of the static element
    * @var  array
    */
    protected $data = array('content' => '');

    public function getType()
    {
        return 'static';
    }

   /**
    * Static element can not be frozen
    *
    * @param    bool    Whether element should be frozen or editable. This
    *                   parameter is ignored in case of static elements
    * @return   bool    Always returns false
    */
    public function toggleFrozen($freeze = null)
    {
        return false;
    }

   /**
    * Sets the contents of the static element
    *
    * @param    string  Static content
    * @return   HTML_QuickForm2_Element_Static
    */
    function setContent($content)
    {
        $this->data['content'] = $content;
        return $this;
    }

   /**
    * Returns the contents of the static element
    *
    * @return   string
    */
    function getContent()
    {
        return $this->data['content'];
    }

   /**
    * Static element's content can also be set via this method
    *
    * @param    mixed   Element's value, this parameter is ignored
    * @return   HTML_QuickForm2_Element_Static
    */
    public function setValue($value)
    {
        $this->setContent($value);
        return $this;
    }

   /**
    * Static elements have no value
    *
    * @return    null
    */
    public function getValue()
    {
        return null;
    }

    public function __toString()
    {
        return $this->getIndent() . $this->data['content'];
    }

   /**
    * Called when the element needs to update its value from form's data sources
    *
    * Static elements content can be updated with default form values.
    */
    public function updateValue()
    {
        foreach ($this->getDataSources() as $ds) {
            if (!$ds instanceof HTML_QuickForm2_DataSource_Submit &&
                null !== ($value = $ds->getValue($this->getName())))
            {
                $this->setContent($value);
                return;
            }
        }
    }
}
?>
