<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Validates values using regular expressions
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
 * @author      Bertrand Mansion <bmansion@mamasam.com>
 * @copyright   2001-2007 The PHP Group
 * @license     http://www.php.net/license/3_01.txt PHP License 3.01
 * @version     CVS: $Id$
 * @link        http://pear.php.net/package/HTML_QuickForm
 */

/**
 * Abstract base class for QuickForm validation rules 
 */
require_once 'HTML/QuickForm/Rule.php';

/**
 * Validates values using regular expressions
 *
 * @category    HTML
 * @package     HTML_QuickForm
 * @author      Bertrand Mansion <bmansion@mamasam.com>
 * @version     Release: 3.2.9
 * @since       3.2
 */
class HTML_QuickForm_Rule_Regex extends HTML_QuickForm_Rule
{
    /**
     * Array of regular expressions
     *
     * Array is in the format:
     * $_data['rulename'] = 'pattern';
     *
     * @var     array
     * @access  private
     */
    var $_data = array(
                    'lettersonly'   => '/^[a-zA-Z]+$/',
                    'alphanumeric'  => '/^[a-zA-Z0-9]+$/',
                    'numeric'       => '/(^-?\d\d*\.\d*$)|(^-?\d\d*$)|(^-?\.\d\d*$)/',
                    'nopunctuation' => '/^[^().\/\*\^\?#!@$%+=,\"\'><~\[\]{}]+$/',
                    'nonzero'       => '/^-?[1-9][0-9]*/'
                    );

    /**
     * Validates a value using a regular expression
     *
     * @param     string    $value      Value to be checked
     * @param     string    $regex      Regular expression
     * @access    public
     * @return    boolean   true if value is valid
     */
    function validate($value, $regex = null)
    {
        // Fix for bug #10799: add 'D' modifier to regex
        if (isset($this->_data[$this->name])) {
            if (!preg_match($this->_data[$this->name] . 'D', $value)) {
                return false;
            }
        } else {
            if (!preg_match($regex . 'D', $value)) {
                return false;
            }
        }
        return true;
    } // end func validate

    /**
     * Adds new regular expressions to the list
     *
     * @param     string    $name       Name of rule
     * @param     string    $pattern    Regular expression pattern
     * @access    public
     */
    function addData($name, $pattern)
    {
        $this->_data[$name] = $pattern;
    } // end func addData


    function getValidationScript($options = null)
    {
        $regex = isset($this->_data[$this->name]) ? $this->_data[$this->name] : $options;

        return array("  var regex = " . $regex . ";\n", "{jsVar} != '' && !regex.test({jsVar})");
    } // end func getValidationScript

} // end class HTML_QuickForm_Rule_Regex
?>