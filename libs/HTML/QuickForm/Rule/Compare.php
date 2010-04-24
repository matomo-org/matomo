<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Rule to compare two form fields
 * 
 * PHP versions 4 and 5
 *
 * LICENSE: This source file is subject to version 3.01 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_01.txt If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @category    HTML
 * @package     HTML_QuickForm
 * @author      Alexey Borzov <avb@php.net>
 * @copyright   2001-2009 The PHP Group
 * @license     http://www.php.net/license/3_01.txt PHP License 3.01
 * @version     CVS: $Id$
 * @link        http://pear.php.net/package/HTML_QuickForm
 */

/**
 * Abstract base class for QuickForm validation rules 
 */
require_once dirname(__FILE__) . '/../Rule.php';

/**
 * Rule to compare two form fields
 * 
 * The most common usage for this is to ensure that the password 
 * confirmation field matches the password field
 * 
 * @category    HTML
 * @package     HTML_QuickForm
 * @author      Alexey Borzov <avb@php.net>
 * @version     Release: 3.2.11
 * @since       3.2
 */
class HTML_QuickForm_Rule_Compare extends HTML_QuickForm_Rule
{
   /**
    * Possible operators to use
    * @var array
    * @access private
    */
    var $_operators = array(
        'eq'  => '===',
        'neq' => '!==',
        'gt'  => '>',
        'gte' => '>=',
        'lt'  => '<',
        'lte' => '<=',
        '=='  => '===',
        '!='  => '!=='
    );


   /**
    * Returns the operator to use for comparing the values
    * 
    * @access private
    * @param  string     operator name
    * @return string     operator to use for validation
    */
    function _findOperator($name)
    {
        if (empty($name)) {
            return '===';
        } elseif (isset($this->_operators[$name])) {
            return $this->_operators[$name];
        } elseif (in_array($name, $this->_operators)) {
            return $name;
        } else {
            return '===';
        }
    }


    function validate($values, $operator = null)
    {
        $operator = $this->_findOperator($operator);
        if ('===' != $operator && '!==' != $operator) {
            $compareFn = create_function('$a, $b', 'return floatval($a) ' . $operator . ' floatval($b);');
        } else {
            $compareFn = create_function('$a, $b', 'return strval($a) ' . $operator . ' strval($b);');
        }
        
        return $compareFn($values[0], $values[1]);
    }


    function getValidationScript($operator = null)
    {
        $operator = $this->_findOperator($operator);
        if ('===' != $operator && '!==' != $operator) {
            $check = "!(Number({jsVar}[0]) {$operator} Number({jsVar}[1]))";
        } else {
            $check = "!(String({jsVar}[0]) {$operator} String({jsVar}[1]))";
        }
        return array('', "'' != {jsVar}[0] && {$check}");
    }
}
?>
