<?php
/**
 * Static Factory class for HTML_QuickForm2 package
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
 * @version    SVN: $Id: Factory.php 299305 2010-05-12 20:15:28Z avb $
 * @link       http://pear.php.net/package/HTML_QuickForm2
 */

/**
 * Class with static methods for loading classes and files
 */
// require_once 'HTML/QuickForm2/Loader.php';

/**
 * Static factory class
 *
 * The class handles instantiation of Element and Rule objects as well as
 * registering of new Element and Rule classes.
 *
 * @category   HTML
 * @package    HTML_QuickForm2
 * @author     Alexey Borzov <avb@php.net>
 * @author     Bertrand Mansion <golgote@mamasam.com>
 * @version    Release: @package_version@
 */
class HTML_QuickForm2_Factory
{
   /**
    * List of element types known to Factory
    * @var array
    */
    protected static $elementTypes = array(
        'button'        => array('HTML_QuickForm2_Element_Button', null),
        'checkbox'      => array('HTML_QuickForm2_Element_InputCheckbox', null),
        'date'          => array('HTML_QuickForm2_Element_Date', null),
        'fieldset'      => array('HTML_QuickForm2_Container_Fieldset', null),
        'group'         => array('HTML_QuickForm2_Container_Group', null),
        'file'          => array('HTML_QuickForm2_Element_InputFile', null),
        'hidden'        => array('HTML_QuickForm2_Element_InputHidden', null),
        'image'         => array('HTML_QuickForm2_Element_InputImage', null),
        'inputbutton'   => array('HTML_QuickForm2_Element_InputButton', null),
        'password'      => array('HTML_QuickForm2_Element_InputPassword', null),
        'radio'         => array('HTML_QuickForm2_Element_InputRadio', null),
        'reset'         => array('HTML_QuickForm2_Element_InputReset', null),
        'select'        => array('HTML_QuickForm2_Element_Select', null),
        'submit'        => array('HTML_QuickForm2_Element_InputSubmit', null),
        'text'          => array('HTML_QuickForm2_Element_InputText', null),
        'textarea'      => array('HTML_QuickForm2_Element_Textarea', null)
    );

   /**
    * List of registered rules
    * @var array
    */
    protected static $registeredRules = array(
        'nonempty'      => array('HTML_QuickForm2_Rule_Nonempty', null),
        'empty'         => array('HTML_QuickForm2_Rule_Empty', null),
        'required'      => array('HTML_QuickForm2_Rule_Required', null),
        'compare'       => array('HTML_QuickForm2_Rule_Compare', null),
        'eq'            => array('HTML_QuickForm2_Rule_Compare', null,
                                 array('operator' => '===')),
        'neq'           => array('HTML_QuickForm2_Rule_Compare', null,
                                 array('operator' => '!==')),
        'lt'            => array('HTML_QuickForm2_Rule_Compare', null,
                                 array('operator' => '<')),
        'lte'           => array('HTML_QuickForm2_Rule_Compare', null,
                                 array('operator' => '<=')),
        'gt'            => array('HTML_QuickForm2_Rule_Compare', null,
                                 array('operator' => '>')),
        'gte'           => array('HTML_QuickForm2_Rule_Compare', null,
                                 array('operator' => '>=')),
        'regex'         => array('HTML_QuickForm2_Rule_Regex', null),
        'callback'      => array('HTML_QuickForm2_Rule_Callback', null),
        'length'        => array('HTML_QuickForm2_Rule_Length', null),
        'minlength'     => array('HTML_QuickForm2_Rule_Length', null,
                                 array('max' => 0)),
        'maxlength'     => array('HTML_QuickForm2_Rule_Length', null,
                                 array('min' => 0)),
        'maxfilesize'   => array('HTML_QuickForm2_Rule_MaxFileSize', null),
        'mimetype'      => array('HTML_QuickForm2_Rule_MimeType', null),
        'each'          => array('HTML_QuickForm2_Rule_Each', null),
        'notcallback'   => array('HTML_QuickForm2_Rule_NotCallback', null),
        'notregex'      => array('HTML_QuickForm2_Rule_NotRegex', null)
    );


   /**
    * Registers a new element type
    *
    * @param    string  Type name (treated case-insensitively)
    * @param    string  Class name
    * @param    string  File containing the class, leave empty if class already loaded
    */
    public static function registerElement($type, $className, $includeFile = null)
    {
        self::$elementTypes[strtolower($type)] = array($className, $includeFile);
    }


   /**
    * Checks whether an element type is known to factory
    *
    * @param    string  Type name (treated case-insensitively)
    * @return   bool
    */
    public static function isElementRegistered($type)
    {
        return isset(self::$elementTypes[strtolower($type)]);
    }


   /**
    * Creates a new element object of the given type
    *
    * @param    string  Type name (treated case-insensitively)
    * @param    mixed   Element name (passed to element's constructor)
    * @param    mixed   Element attributes (passed to element's constructor)
    * @param    array   Element-specific data (passed to element's constructor)
    * @return   HTML_QuickForm2_Node     A created element
    * @throws   HTML_QuickForm2_InvalidArgumentException If type name is unknown
    * @throws   HTML_QuickForm2_NotFoundException If class for the element can
    *           not be found and/or loaded from file
    */
    public static function createElement($type, $name = null, $attributes = null,
                                         array $data = array())
    {
        $type = strtolower($type);
        if (!isset(self::$elementTypes[$type])) {
            throw new HTML_QuickForm2_InvalidArgumentException("Element type '$type' is not known");
        }
        list($className, $includeFile) = self::$elementTypes[$type];
        if (!class_exists($className)) {
            HTML_QuickForm2_Loader::loadClass($className, $includeFile);
        }
        return new $className($name, $attributes, $data);
    }


   /**
    * Registers a new rule type
    *
    * @param    string  Rule type name (treated case-insensitively)
    * @param    string  Class name
    * @param    string  File containing the class, leave empty if class already loaded
    * @param    mixed   Configuration data for rules of the given type
    */
    public static function registerRule($type, $className, $includeFile = null,
                                        $config = null)
    {
        self::$registeredRules[strtolower($type)] = array($className, $includeFile, $config);
    }


   /**
    * Checks whether a rule type is known to Factory
    *
    * @param    string  Rule type name (treated case-insensitively)
    * @return   bool
    */
    public static function isRuleRegistered($type)
    {
        return isset(self::$registeredRules[strtolower($type)]);
    }


   /**
    * Creates a new Rule of the given type
    *
    * @param    string                  Rule type name (treated case-insensitively)
    * @param    HTML_QuickForm2_Node    Element to validate by the rule
    * @param    string                  Message to display if validation fails
    * @param    mixed                   Configuration data for the rule
    * @return   HTML_QuickForm2_Rule    A created Rule
    * @throws   HTML_QuickForm2_InvalidArgumentException If rule type is unknown
    * @throws   HTML_QuickForm2_NotFoundException        If class for the rule
    *           can't be found and/or loaded from file
    */
    public static function createRule($type, HTML_QuickForm2_Node $owner,
                                      $message = '', $config = null)
    {
        $type = strtolower($type);
        if (!isset(self::$registeredRules[$type])) {
            throw new HTML_QuickForm2_InvalidArgumentException("Rule '$type' is not known");
        }
        list($className, $includeFile) = self::$registeredRules[$type];
        if (!class_exists($className)) {
            HTML_QuickForm2_Loader::loadClass($className, $includeFile);
        }
        if (isset(self::$registeredRules[$type][2])) {
            $config = call_user_func(array($className, 'mergeConfig'), $config,
                                     self::$registeredRules[$type][2]);
        }
        return new $className($owner, $message, $config);
    }
}
?>
