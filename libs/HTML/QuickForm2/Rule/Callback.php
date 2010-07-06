<?php
/**
 * Rule checking the value via a callback function (method)
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
 * @version    SVN: $Id: Callback.php 294057 2010-01-26 21:10:28Z avb $
 * @link       http://pear.php.net/package/HTML_QuickForm2
 */

/**
 * Base class for HTML_QuickForm2 rules
 */
// require_once 'HTML/QuickForm2/Rule.php';

/**
 * Rule checking the value via a callback function (method)
 *
 * The Rule needs a valid callback as a configuration parameter for its work, it
 * may also be given additional arguments to pass to the callback alongside the
 * element's value. See {@link mergeConfig()} for description of possible ways
 * to pass configuration parameters.
 *
 * The callback will be called with element's value as the first argument, if
 * additional arguments were provided they'll be passed as well. It is expected
 * to return false if the value is invalid and true if it is valid.
 *
 * Checking that the value is not empty:
 * <code>
 * $str->addRule('callback', 'The field should not be empty', 'strlen');
 * </code>
 * Checking that the value is in the given array:
 * <code>
 * $meta->addRule('callback', 'Unknown variable name',
 *                array('callback' => 'in_array',
 *                      'arguments' => array(array('foo', 'bar', 'baz'))));
 * </code>
 * The same, but with rule registering first:
 * <code>
 * HTML_QuickForm2_Factory::registerRule(
 *     'in_array', 'HTML_QuickForm2_Rule_Callback',
 *     'HTML/QuickForm2/Rule/Callback.php', 'in_array'
 * );
 * $meta->addRule('in_array', 'Unknown variable name', array(array('foo', 'bar', 'baz')));
 * </code>
 *
 * @category   HTML
 * @package    HTML_QuickForm2
 * @author     Alexey Borzov <avb@php.net>
 * @author     Bertrand Mansion <golgote@mamasam.com>
 * @version    Release: @package_version@
 */
class HTML_QuickForm2_Rule_Callback extends HTML_QuickForm2_Rule
{
   /**
    * Validates the owner element
    *
    * @return   bool    the value returned by a callback function
    */
    protected function validateOwner()
    {
        $value  = $this->owner->getValue();
        $config = $this->getConfig();
        return (bool)call_user_func_array(
            $config['callback'], array_merge(array($value), $config['arguments'])
        );
    }

   /**
    * Merges local configuration with that provided for registerRule()
    *
    * "Global" configuration may be passed to
    * {@link HTML_QuickForm2_Factory::registerRule()} in either of the
    * following formats
    *  - callback
    *  - array(['callback' => callback, ]['arguments' => array(...)])
    *
    * "Local" configuration may be passed to the constructor in either of
    * the following formats
    *  - callback or arguments (interpretation depends on whether the global
    *    configuration already contains the callback)
    *  - array(['callback' => callback, ]['arguments' => array(...)])
    *
    * As usual, global config overrides local one. It is a good idea to use the
    * associative array format to prevent ambiguity.
    *
    * @param    mixed   Local configuration
    * @param    mixed   Global configuration
    * @return   mixed   Merged configuration
    */
    public static function mergeConfig($localConfig, $globalConfig)
    {
        if (!isset($globalConfig)) {
            $config = $localConfig;

        } else {
            if (!is_array($globalConfig) ||
                !isset($globalConfig['callback']) && !isset($globalConfig['arguments'])
            ) {
                $config = array('callback' => $globalConfig);
            } else {
                $config = $globalConfig;
            }
            if (is_array($localConfig) && (isset($localConfig['callback'])
                || isset($localConfig['arguments']))
            ) {
                $config += $localConfig;
            } elseif(isset($localConfig)) {
                $config += array('callback' => $localConfig, 'arguments' => $localConfig);
            }
        }
        return $config;
    }

   /**
    * Sets the callback to use for validation and its additional arguments
    *
    * @param    callback|array  Callback or array ('callback' => validation callback,
    *                                              'arguments' => additional arguments)
    * @return   HTML_QuickForm2_Rule
    * @throws   HTML_QuickForm2_InvalidArgumentException if callback is missing or invalid
    */
    public function setConfig($config)
    {
        if (!is_array($config) || !isset($config['callback'])) {
            $config = array('callback' => $config);
        }
        if (!is_callable($config['callback'], false, $callbackName)) {
            throw new HTML_QuickForm2_InvalidArgumentException(
                'Callback Rule requires a valid callback, \'' . $callbackName .
                '\' was given'
            );
        }
        return parent::setConfig($config + array('arguments' => array()));
    }
}
?>
