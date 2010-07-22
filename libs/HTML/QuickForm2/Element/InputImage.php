<?php
/**
 * Class for <input type="image" /> elements
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
 * @version    SVN: $Id: InputImage.php 300722 2010-06-24 10:15:52Z mansion $
 * @link       http://pear.php.net/package/HTML_QuickForm2
 */

/**
 * Base class for <input> elements
 */
// require_once 'HTML/QuickForm2/Element/Input.php';

/**
 * Class for <input type="image" /> elements
 *
 * @category   HTML
 * @package    HTML_QuickForm2
 * @author     Alexey Borzov <avb@php.net>
 * @author     Bertrand Mansion <golgote@mamasam.com>
 * @version    Release: @package_version@
 */
class HTML_QuickForm2_Element_InputImage extends HTML_QuickForm2_Element_Input
{
    protected $attributes = array('type' => 'image');

   /**
    * Coordinates of user click within the image, array contains keys 'x' and 'y'
    * @var  array
    */
    protected $coordinates = null;

   /**
    * Image buttons can not be frozen
    *
    * @param    bool    Whether element should be frozen or editable. This
    *                   parameter is ignored in case of image elements
    * @return   bool    Always returns false
    */
    public function toggleFrozen($freeze = null)
    {
        return false;
    }

   /**
    * Image button's value cannot be set via this method
    *
    * @param    mixed   Element's value, this parameter is ignored
    * @return   HTML_QuickForm2_Element_InputImage
    */
    public function setValue($value)
    {
        return $this;
    }

   /**
    * Returns the element's value
    *
    * The value is only returned if the form was actually submitted and this
    * image button was clicked. Returns null in all other cases.
    *
    * @return   array|null  An array with keys 'x' and 'y' containing the
    *                       coordinates of user click if the image was clicked,
    *                       null otherwise
    */
    public function getValue()
    {
        return $this->getAttribute('disabled')? null: $this->applyFilters($this->coordinates);
    }

   /**
    * Returns the HTML representation of the element
    *
    * The method changes the element's name to foo[bar][] if it was foo[bar]
    * originally. If it is not done, then one of the click coordinates will be
    * lost, see {@link http://bugs.php.net/bug.php?id=745}
    *
    * @return   string
    */
    public function __toString()
    {
        if (false === strpos($this->attributes['name'], '[') ||
            '[]' == substr($this->attributes['name'], -2))
        {
            return parent::__toString();
        } else {
            $this->attributes['name'] .= '[]';
            $html = parent::__toString();
            $this->attributes['name']  = substr($this->attributes['name'], 0, -2);
            return $html;
        }
    }

    public function updateValue()
    {
        foreach ($this->getDataSources() as $ds) {
            if ($ds instanceof HTML_QuickForm2_DataSource_Submit) {
                $name = $this->getName();
                if (false === strpos($name, '[') &&
                    null !== ($value = $ds->getValue($name . '_x')))
                {
                    $this->coordinates = array(
                        'x' => $value,
                        'y' => $ds->getValue($name . '_y')
                    );
                    return;

                } elseif (false !== strpos($name, '[')) {
                    if ('[]' == substr($name, -2)) {
                        $name = substr($name, 0, -2);
                    }
                    if (null !== ($value = $ds->getValue($name))) {
                        $this->coordinates = array(
                            'x' => $value[0],
                            'y' => $value[1]
                        );
                        return;
                    }
                }
            }
        }
        $this->coordinates = null;
    }
}
?>
