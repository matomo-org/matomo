<?php
/**
 * Rule comparing the value of the field with some other value
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
 * @version    SVN: $Id: Compare.php 299480 2010-05-19 06:55:03Z avb $
 * @link       http://pear.php.net/package/HTML_QuickForm2
 */

/**
 * Base class for HTML_QuickForm2 rules
 */
// require_once 'HTML/QuickForm2/Rule.php';

/**
 * Rule comparing the value of the field with some other value
 *
 * The Rule needs two configuration parameters for its work
 *  - comparison operator (defaults to equality)
 *  - operand to compare with; this can be either a constant or another form
 *    element (its value will be used)
 * See {@link mergeConfig()} for description of possible ways to pass
 * configuration parameters.
 *
 * Note that 'less than [or equal]' and 'greater than [or equal]' operators
 * compare the operands numerically, since this is considered as more useful
 * approach by the authors.
 *
 * For convenience, this Rule is already registered in the Factory with the
 * names 'eq', 'neq', 'lt', 'gt', 'lte', 'gte' corresponding to the relevant
 * operators:
 * <code>
 * $password->addRule('eq', 'Passwords do not match', $passwordRepeat);
 * $orderQty->addRule('lte', 'Should not order more than 10 of these', 10);
 * </code>
 *
 * @category   HTML
 * @package    HTML_QuickForm2
 * @author     Alexey Borzov <avb@php.net>
 * @author     Bertrand Mansion <golgote@mamasam.com>
 * @version    Release: @package_version@
 */
class HTML_QuickForm2_Rule_Compare extends HTML_QuickForm2_Rule
{
   /**
    * Possible comparison operators
    * @var array
    */
    protected $operators = array('==', '!=', '===', '!==', '<', '<=', '>', '>=');


   /**
    * Validates the owner element
    *
    * @return   bool    whether (element_value operator operand) expression is true
    */
    protected function validateOwner()
    {
        $value  = $this->owner->getValue();
        $config = $this->getConfig();
        if (!in_array($config['operator'], array('===', '!=='))) {
            $compareFn = create_function(
                '$a, $b', 'return floatval($a) ' . $config['operator'] . ' floatval($b);'
            );
        } else {
            $compareFn = create_function(
                '$a, $b', 'return strval($a) ' . $config['operator'] . ' strval($b);'
            );
        }
        return $compareFn($value, $config['operand'] instanceof HTML_QuickForm2_Node
                                  ? $config['operand']->getValue(): $config['operand']);
    }

    protected function getJavascriptCallback()
    {
        $config   = $this->getConfig();
        $operand1 = $this->owner->getJavascriptValue();
        $operand2 = $config['operand'] instanceof HTML_QuickForm2_Node
                    ? $config['operand']->getJavascriptValue()
                    : "'" . strtr($config['operand'], array(
                                "\r" => '\r',
                                "\n" => '\n',
                                "\t" => '\t',
                                "'"  => "\\'",
                                '"'  => '\"',
                                '\\' => '\\\\'
                            )) . "'";

        if (!in_array($config['operator'], array('===', '!=='))) {
            $check = "Number({$operand1}) {$config['operator']} Number({$operand2})";
        } else {
            $check = "String({$operand1}) {$config['operator']} String({$operand2})";
        }

        return "function () { return {$check}; }";
    }

   /**
    * Merges local configuration with that provided for registerRule()
    *
    * "Global" configuration may be passed to
    * {@link HTML_QuickForm2_Factory::registerRule()} in
    * either of the following formats
    *  - operator
    *  - array(operator[, operand])
    *  - array(['operator' => operator, ]['operand' => operand])

    * "Local" configuration may be passed to the constructor in either of
    * the following formats
    *  - operand
    *  - array([operator, ]operand)
    *  - array(['operator' => operator, ]['operand' => operand])
    *
    * As usual, global configuration overrides local one.
    *
    * @param    mixed   Local configuration
    * @param    mixed   Global configuration
    * @return   mixed   Merged configuration
    */
    public static function mergeConfig($localConfig, $globalConfig)
    {
        $config = null;
        if (0 < count($globalConfig)) {
            $config = self::toCanonicalForm($globalConfig, 'operator');
        }
        if (0 < count($localConfig)) {
            $config = (isset($config)? $config: array())
                      + self::toCanonicalForm($localConfig);
        }
        return $config;
    }

   /**
    * Converts configuration data to a canonical associative array form
    *
    * @param    mixed   Configuration data
    * @param    string  Array key to assign $config to if it is scalar
    * @return   array   Associative array that may contain 'operand' and 'operator' keys
    */
    protected static function toCanonicalForm($config, $key = 'operand')
    {
        if (!is_array($config)) {
            return array($key => $config);

        } elseif (array_key_exists('operator', $config)
                  || array_key_exists('operand', $config)
        ) {
            return $config;

        } elseif (1 == count($config)) {
            return array($key => end($config));

        } else {
            return array('operator' => reset($config), 'operand' => end($config));
        }
    }

   /**
    * Sets the comparison operator and operand to compare to
    *
    * $config can be either of the following
    *  - operand
    *  - array([operator, ]operand)
    *  - array(['operator' => operator, ]['operand' => operand])
    * If operator is missing it will default to '==='
    *
    * @param    mixed   Configuration data
    * @return   HTML_QuickForm2_Rule
    * @throws   HTML_QuickForm2_InvalidArgumentException if a bogus comparison
    *           operator is used for configuration, if an operand is missing
    */
    public function setConfig($config)
    {
        if (0 == count($config)) {
            throw new HTML_QuickForm2_InvalidArgumentException(
                'Compare Rule requires an argument to compare with'
            );
        }
        $config = self::toCanonicalForm($config);

        $config += array('operator' => '===');
        if (!in_array($config['operator'], $this->operators)) {
            throw new HTML_QuickForm2_InvalidArgumentException(
                'Compare Rule requires a valid comparison operator, ' .
                preg_replace('/\s+/', ' ', var_export($config['operator'], true)) . ' given'
            );
        }
        if (in_array($config['operator'], array('==', '!='))) {
            $config['operator'] .= '=';
        }

        return parent::setConfig($config);
    }
}
?>
