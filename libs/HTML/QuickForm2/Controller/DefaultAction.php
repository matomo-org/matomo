<?php
/**
 * A hidden button used to submit the form when the user presses Enter
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
 * @version    SVN: $Id: DefaultAction.php 293465 2010-01-12 18:24:37Z avb $
 * @link       http://pear.php.net/package/HTML_QuickForm2
 */

/** Class for <input type="image" /> elements */
// require_once 'HTML/QuickForm2/Element/InputImage.php';

/**
 * A hidden button used to submit the form when the user presses Enter
 *
 * This element is used by {@link HTML_QuickForm2_Controller_Page::setDefaultAction()}
 * to define the action that will take place if the user presses Enter on one
 * of the form elements instead of explicitly clicking one of the submit
 * buttons. Injecting a hidden <input type="image" /> element is about the
 * only cross-browser way to achieve this.
 *
 * @category   HTML
 * @package    HTML_QuickForm2
 * @author     Alexey Borzov <avb@php.net>
 * @author     Bertrand Mansion <golgote@mamasam.com>
 * @version    Release: @package_version@
 * @link       http://www.alanflavell.org.uk/www/formquestion.html
 * @link       http://muffinresearch.co.uk/archives/2005/12/08/fun-with-multiple-submit-buttons/
 */
class HTML_QuickForm2_Controller_DefaultAction
    extends HTML_QuickForm2_Element_InputImage
{
    protected $attributes = array('type' => 'image', 'id' => '_qf_default',
                                  'width' => '1', 'height' => '1');

   /**
    * Disallow changing the 'id' attribute
    *
    * @param    string  Attribute name
    * @param    string  Attribute value, null if attribute is being removed
    */
    protected function onAttributeChange($name, $value = null)
    {
        if ('id' == $name) {
            throw new HTML_QuickForm2_InvalidArgumentException(
                "Attribute 'id' is read-only"
            );
        }
        parent::onAttributeChange($name, $value);
    }

   /**
    * This element is rendered using renderHidden() method
    *
    * renderHidden() is used to
    *   - prevent using the standard element template as this button is
    *     expected to be hidden
    *   - render it above all other submit buttons since hidden elements
    *     are usually at the top of the form
    *
    * @param    HTML_QuickForm2_Renderer    Renderer instance
    * @return   HTML_QuickForm2_Renderer
    */
    public function render(HTML_QuickForm2_Renderer $renderer)
    {
        $renderer->renderHidden($this);
        return $renderer;
    }
}
?>
