<?php
/**
 * Validates all elements in a Container using a template Rule
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
 * @version    SVN: $Id: Each.php 294057 2010-01-26 21:10:28Z avb $
 * @link       http://pear.php.net/package/HTML_QuickForm2
 */

/**
 * Base class for HTML_QuickForm2 rules
 */
// require_once 'HTML/QuickForm2/Rule.php';

/**
 * Validates all elements in a Container using a template Rule
 *
 * This Rule needs one configuration parameter for its work: the template Rule
 * to use for actual validation. It can be passed either to
 * {@link HTML_QuickForm2_Rule::__construct() the Rule constructor} as local
 * configuration or to {@link HTML_QuickForm2_Factory::registerRule()} as
 * global one. As usual, global configuration overrides local.
 *
 * The container will be considered valid if all its elements are valid
 * according to a template Rule.
 *
 * <code>
 * $group->addRule('each', 'The fields should contain only letters',
 *                 $group->createRule('regex', '/^[a-z]+$/i'));
 * </code>
 *
 * @category   HTML
 * @package    HTML_QuickForm2
 * @author     Alexey Borzov <avb@php.net>
 * @author     Bertrand Mansion <golgote@mamasam.com>
 * @version    Release: @package_version@
 */
class HTML_QuickForm2_Rule_Each extends HTML_QuickForm2_Rule
{
   /**
    * Validates the owner's children using the template Rule
    *
    * @return   bool    Whether all children are valid according to a template Rule
    */
    protected function validateOwner()
    {
        $rule = clone $this->getConfig();
        foreach ($this->owner->getRecursiveIterator(RecursiveIteratorIterator::LEAVES_ONLY) as $child) {
            $rule->setOwner($child);
            if (!$rule->validateOwner()) {
                return false;
            }
        }
        return true;
    }

   /**
    * Sets the template Rule to use for actual validation
    *
    * We do not allow using Required rules here, they are able to validate
    * containers themselves without the help of Each rule.
    *
    * @param    HTML_QuickForm2_Rule    Template Rule
    * @return   HTML_QuickForm2_Rule
    * @throws   HTML_QuickForm2_InvalidArgumentException if $config is either not
    *               an instance of Rule or is an instance of Rule_Required
    */
    public function setConfig($config)
    {
        if (!$config instanceof HTML_QuickForm2_Rule) {
            throw new HTML_QuickForm2_InvalidArgumentException(
                'Each Rule requires a template Rule to validate with, ' .
                preg_replace('/\s+/', ' ', var_export($config, true)) . ' given'
            );
        } elseif ($config instanceof HTML_QuickForm2_Rule_Required) {
            throw new HTML_QuickForm2_InvalidArgumentException(
                'Cannot use "required" Rule as a template'
            );
        }
        return parent::setConfig($config);
    }

   /**
    * Sets the element that will be validated by this rule
    *
    * @param    HTML_QuickForm2_Container   Container to validate
    * @throws   HTML_QuickForm2_InvalidArgumentException    if trying to use
    *           this Rule on something that isn't a Container
    */
    public function setOwner(HTML_QuickForm2_Node $owner)
    {
        if (!$owner instanceof HTML_QuickForm2_Container) {
            throw new HTML_QuickForm2_InvalidArgumentException(
                'Each Rule can only validate Containers, '.
                get_class($owner) . ' given'
            );
        }
        parent::setOwner($owner);
    }
}
?>
