<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Validates values using callback functions or methods
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
 * Validates values using callback functions or methods
 *
 * @category    HTML
 * @package     HTML_QuickForm
 * @author      Bertrand Mansion <bmansion@mamasam.com>
 * @version     Release: 3.2.9
 * @since       3.2
 */
class HTML_QuickForm_Rule_Callback extends HTML_QuickForm_Rule
{
    /**
     * Array of callbacks
     *
     * Array is in the format:
     * $_data['rulename'] = array('functionname', 'classname');
     * If the callback is not a method, then the class name is not set.
     *
     * @var     array
     * @access  private
     */
    var $_data = array();

   /**
    * Whether to use BC mode for specific rules
    * 
    * Previous versions of QF passed element's name as a first parameter
    * to validation functions, but not to validation methods. This behaviour
    * is emulated if you are using 'function' as rule type when registering.
    * 
    * @var array
    * @access private
    */
    var $_BCMode = array();

    /**
     * Validates a value using a callback
     *
     * @param     string    $value      Value to be checked
     * @param     mixed     $options    Options for callback
     * @access    public
     * @return    boolean   true if value is valid
     */
    function validate($value, $options = null)
    {
        if (isset($this->_data[$this->name])) {
            $callback = $this->_data[$this->name];
            if (isset($callback[1])) {
                return call_user_func(array($callback[1], $callback[0]), $value, $options);
            } elseif ($this->_BCMode[$this->name]) {
                return $callback[0]('', $value, $options);
            } else {
                return $callback[0]($value, $options);
            }
        } elseif (is_callable($options)) {
            return call_user_func($options, $value);
        } else {
            return true;
        }
    } // end func validate

    /**
     * Adds new callbacks to the callbacks list
     *
     * @param     string    $name       Name of rule
     * @param     string    $callback   Name of function or method
     * @param     string    $class      Name of class containing the method
     * @param     bool      $BCMode     Backwards compatibility mode 
     * @access    public
     */
    function addData($name, $callback, $class = null, $BCMode = false)
    {
        if (!empty($class)) {
            $this->_data[$name] = array($callback, $class);
        } else {
            $this->_data[$name] = array($callback);
        }
        $this->_BCMode[$name] = $BCMode;
    } // end func addData


    function getValidationScript($options = null)
    {
        if (isset($this->_data[$this->name])) {
            $callback = $this->_data[$this->name][0];
            $params   = ($this->_BCMode[$this->name]? "'', {jsVar}": '{jsVar}') .
                        (isset($options)? ", '{$options}'": '');
        } else {
            $callback = is_array($options)? $options[1]: $options;
            $params   = '{jsVar}';
        }
        return array('', "{jsVar} != '' && !{$callback}({$params})");
    } // end func getValidationScript

} // end class HTML_QuickForm_Rule_Callback
?>