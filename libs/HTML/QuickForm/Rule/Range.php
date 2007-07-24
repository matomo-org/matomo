<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Checks that the length of value is within range
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
 * @version     CVS: $Id: Range.php,v 1.7 2007/05/29 18:34:36 avb Exp $
 * @link        http://pear.php.net/package/HTML_QuickForm
 */

/**
 * Abstract base class for QuickForm validation rules 
 */
require_once 'HTML/QuickForm/Rule.php';

/**
 * Checks that the length of value is within range
 *
 * @category    HTML
 * @package     HTML_QuickForm
 * @author      Bertrand Mansion <bmansion@mamasam.com>
 * @version     Release: 3.2.9
 * @since       3.2
 */
class HTML_QuickForm_Rule_Range extends HTML_QuickForm_Rule
{
    /**
     * Validates a value using a range comparison
     *
     * @param     string    $value      Value to be checked
     * @param     mixed     $options    Int for length, array for range
     * @access    public
     * @return    boolean   true if value is valid
     */
    function validate($value, $options)
    {
        $length = strlen($value);
        switch ($this->name) {
            case 'minlength': return ($length >= $options);
            case 'maxlength': return ($length <= $options);
            default:          return ($length >= $options[0] && $length <= $options[1]);
        }
    } // end func validate


    function getValidationScript($options = null)
    {
        switch ($this->name) {
            case 'minlength': 
                $test = '{jsVar}.length < '.$options;
                break;
            case 'maxlength': 
                $test = '{jsVar}.length > '.$options;
                break;
            default: 
                $test = '({jsVar}.length < '.$options[0].' || {jsVar}.length > '.$options[1].')';
        }
        return array('', "{jsVar} != '' && {$test}");
    } // end func getValidationScript

} // end class HTML_QuickForm_Rule_Range
?>