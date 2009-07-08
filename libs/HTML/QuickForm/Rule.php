<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Abstract base class for QuickForm validation rules 
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
 * @copyright   2001-2009 The PHP Group
 * @license     http://www.php.net/license/3_01.txt PHP License 3.01
 * @version     CVS: $Id$
 * @link        http://pear.php.net/package/HTML_QuickForm
 */

/**
 * Abstract base class for QuickForm validation rules 
 *
 * @category    HTML
 * @package     HTML_QuickForm
 * @author      Bertrand Mansion <bmansion@mamasam.com>
 * @version     Release: 3.2.11
 * @since       3.2
 * @abstract
 */
class HTML_QuickForm_Rule
{
   /**
    * Name of the rule to use in validate method
    *
    * This property is used in more global rules like Callback and Regex
    * to determine which callback and which regex is to be used for validation
    *
    * @var  string
    * @access   public
    */
    var $name;

   /**
    * Validates a value
    * 
    * @access public
    * @abstract
    */
    function validate($value)
    {
        return true;
    }

   /**
    * Sets the rule name
    *
    * @param  string    rule name
    * @access public
    */
    function setName($ruleName)
    {
        $this->name = $ruleName;
    }

    /**
     * Returns the javascript test (the test should return true if the value is INVALID)
     *
     * @param     mixed     Options for the rule
     * @access    public
     * @return    array     first element is code to setup validation, second is the check itself
     * @abstract
     */
    function getValidationScript($options = null)
    {
        return array('', '');
    }
}
?>