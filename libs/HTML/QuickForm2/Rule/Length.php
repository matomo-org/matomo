<?php
/**
 * Rule checking the value's length
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
 * @version    SVN: $Id: Length.php 299480 2010-05-19 06:55:03Z avb $
 * @link       http://pear.php.net/package/HTML_QuickForm2
 */

/**
 * Base class for HTML_QuickForm2 rules
 */
// require_once 'HTML/QuickForm2/Rule.php';

/**
 * Rule checking the value's length
 *
 * The rule needs an "allowed length" parameter for its work, it can be either
 *  - a scalar: the value will be valid if it is exactly this long
 *  - an array: the value will be valid if its length is between the given values
 *    (inclusive). If one of these evaluates to 0, then length will be compared
 *    only with the remaining one.
 * See {@link mergeConfig()} for description of possible ways to pass
 * configuration parameters.
 *
 * The Rule considers empty fields as valid and doesn't try to compare their
 * lengths with provided limits.
 *
 * For convenience this Rule is also registered with the names 'minlength' and
 * 'maxlength' (having, respectively, 'max' and 'min' parameters set to 0):
 * <code>
 * $password->addRule('minlength', 'The password should be at least 6 characters long', 6);
 * $message->addRule('maxlength', 'Your message is too verbose', 1000);
 * </code>
 *
 * @category   HTML
 * @package    HTML_QuickForm2
 * @author     Alexey Borzov <avb@php.net>
 * @author     Bertrand Mansion <golgote@mamasam.com>
 * @version    Release: @package_version@
 */
class HTML_QuickForm2_Rule_Length extends HTML_QuickForm2_Rule
{
   /**
    * Validates the owner element
    *
    * @return   bool    whether length of the element's value is within allowed range
    */
    protected function validateOwner()
    {
        if (0 == ($valueLength = strlen($this->owner->getValue()))) {
            return true;
        }

        $allowedLength = $this->getConfig();
        if (is_scalar($allowedLength)) {
            return $valueLength == $allowedLength;
        } else {
            return (empty($allowedLength['min']) || $valueLength >= $allowedLength['min']) &&
                   (empty($allowedLength['max']) || $valueLength <= $allowedLength['max']);
        }
    }

    protected function getJavascriptCallback()
    {
        $allowedLength = $this->getConfig();
        if (is_scalar($allowedLength)) {
            $check = "length == {$allowedLength}";
        } else {
            $checks = array();
            if (!empty($allowedLength['min'])) {
                $checks[] = "length >= {$allowedLength['min']}";
            }
            if (!empty($allowedLength['max'])) {
                $checks[] = "length <= {$allowedLength['max']}";
            }
            $check = implode(' && ', $checks);
        }
        return "function() { var length = " . $this->owner->getJavascriptValue() .
               ".length; if (0 == length) { return true; } else { return {$check}; } }";
    }

   /**
    * Adds the 'min' and 'max' fields from one array to the other
    *
    * @param    array   Rule configuration, array with 'min' and 'max' keys
    * @param    array   Additional configuration, fields will be added to
    *                   $length if it doesn't contain such a key already
    * @return   array
    */
    protected static function mergeMinMaxLength($length, $config)
    {
        if (array_key_exists('min', $config) || array_key_exists('max', $config)) {
            if (!array_key_exists('min', $length) && array_key_exists('min', $config)) {
                $length['min'] = $config['min'];
            }
            if (!array_key_exists('max', $length) && array_key_exists('max', $config)) {
                $length['max'] = $config['max'];
            }
        } else {
            if (!array_key_exists('min', $length)) {
                $length['min'] = reset($config);
            }
            if (!array_key_exists('max', $length)) {
                $length['max'] = end($config);
            }
        }
        return $length;
    }

   /**
    * Merges length limits given on rule creation with those given to registerRule()
    *
    * "Global" length limits may be passed to
    * {@link HTML_QuickForm2_Factory::registerRule()} in either of the
    * following formats
    *  - scalar (exact length)
    *  - array(minlength, maxlength)
    *  - array(['min' => minlength, ]['max' => maxlength])
    *
    * "Local" length limits may be passed to the constructor in either of
    * the following formats
    *  - scalar (if global config is unset then it is treated as an exact
    *    length, if 'min' or 'max' is in global config then it is treated
    *    as 'max' or 'min', respectively)
    *  - array(minlength, maxlength)
    *  - array(['min' => minlength, ]['max' => maxlength])
    *
    * As usual, global configuration overrides local one.
    *
    * @param    int|array   Local length limits
    * @param    int|array   Global length limits, usually provided to {@link HTML_QuickForm2_Factory::registerRule()}
    * @return   int|array   Merged length limits
    */
    public static function mergeConfig($localConfig, $globalConfig)
    {
        if (!isset($globalConfig)) {
            $length = $localConfig;

        } elseif (!is_array($globalConfig)) {
            $length = $globalConfig;

        } else {
            $length = self::mergeMinMaxLength(array(), $globalConfig);
            if (isset($localConfig)) {
                $length = self::mergeMinMaxLength(
                    $length, is_array($localConfig)? $localConfig: array($localConfig)
                );
            }
        }
        return $length;
    }

   /**
    * Sets the allowed length limits
    *
    * $config can be either of the following
    *  - integer (rule checks for exact length)
    *  - array(minlength, maxlength)
    *  - array(['min' => minlength, ]['max' => maxlength])
    *
    * @param    int|array   Length limits
    * @return   HTML_QuickForm2_Rule
    * @throws   HTML_QuickForm2_InvalidArgumentException if bogus length limits
    *           were provided
    */
    public function setConfig($config)
    {
        if (is_array($config)) {
            $config = self::mergeMinMaxLength(array(), $config)
                      + array('min' => 0, 'max' => 0);
        }
        if (is_array($config) && ($config['min'] < 0 || $config['max'] < 0) ||
            !is_array($config) && $config < 0)
        {
            throw new HTML_QuickForm2_InvalidArgumentException(
                'Length Rule requires limits to be nonnegative, ' .
                preg_replace('/\s+/', ' ', var_export($config, true)) . ' given'
            );

        } elseif (is_array($config) && $config['min'] == 0 && $config['max'] == 0 ||
                  !is_array($config) && 0 == $config)
        {
            throw new HTML_QuickForm2_InvalidArgumentException(
                'Length Rule requires at least one non-zero limit, ' .
                preg_replace('/\s+/', ' ', var_export($config, true)) . ' given'
            );
        }

        if (!empty($config['min']) && !empty($config['max'])) {
            if ($config['min'] > $config['max']) {
                list($config['min'], $config['max']) = array($config['max'], $config['min']);
            } elseif ($config['min'] == $config['max']) {
                $config = $config['min'];
            }
        }
        return parent::setConfig($config);
    }
}
?>
