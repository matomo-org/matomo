<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Registers rule objects and uses them for validation
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
 * @author      Adam Daniel <adaniel1@eesus.jnj.com>
 * @author      Bertrand Mansion <bmansion@mamasam.com>
 * @author      Alexey Borzov <avb@php.net>
 * @copyright   2001-2007 The PHP Group
 * @license     http://www.php.net/license/3_01.txt PHP License 3.01
 * @version     CVS: $Id: RuleRegistry.php,v 1.18 2007/05/29 18:34:36 avb Exp $
 * @link        http://pear.php.net/package/HTML_QuickForm
 */

/**
 * Registers rule objects and uses them for validation
 *
 * @category    HTML
 * @package     HTML_QuickForm
 * @author      Adam Daniel <adaniel1@eesus.jnj.com>
 * @author      Bertrand Mansion <bmansion@mamasam.com>
 * @author      Alexey Borzov <avb@php.net>
 * @version     Release: 3.2.9
 * @since       3.2
 */
class HTML_QuickForm_RuleRegistry
{
    /**
     * Array containing references to used rules
     * @var     array
     * @access  private
     */
    var $_rules = array();


    /**
     * Returns a singleton of HTML_QuickForm_RuleRegistry
     *
     * Usually, only one RuleRegistry object is needed, this is the reason
     * why it is recommended to use this method to get the validation object. 
     *
     * @access    public
     * @static
     * @return    HTML_QuickForm_RuleRegistry
     */
    function &singleton()
    {
        static $obj;
        if (!isset($obj)) {
            $obj = new HTML_QuickForm_RuleRegistry();
        }
        return $obj;
    } // end func singleton

    /**
     * Registers a new validation rule
     *
     * In order to use a custom rule in your form, you need to register it
     * first. For regular expressions, one can directly use the 'regex' type
     * rule in addRule(), this is faster than registering the rule.
     *
     * Functions and methods can be registered. Use the 'function' type.
     * When registering a method, specify the class name as second parameter.
     *
     * You can also register an HTML_QuickForm_Rule subclass with its own
     * validate() method.
     *
     * @param     string    $ruleName   Name of validation rule
     * @param     string    $type       Either: 'regex', 'function' or null
     * @param     string    $data1      Name of function, regular expression or
     *                                  HTML_QuickForm_Rule object class name
     * @param     string    $data2      Object parent of above function or HTML_QuickForm_Rule file path
     * @access    public
     * @return    void
     */
    function registerRule($ruleName, $type, $data1, $data2 = null)
    {
        $type = strtolower($type);
        if ($type == 'regex') {
            // Regular expression
            $rule =& $this->getRule('regex');
            $rule->addData($ruleName, $data1);
            $GLOBALS['_HTML_QuickForm_registered_rules'][$ruleName] = $GLOBALS['_HTML_QuickForm_registered_rules']['regex'];

        } elseif ($type == 'function' || $type == 'callback') {
            // Callback function
            $rule =& $this->getRule('callback');
            $rule->addData($ruleName, $data1, $data2, 'function' == $type);
            $GLOBALS['_HTML_QuickForm_registered_rules'][$ruleName] = $GLOBALS['_HTML_QuickForm_registered_rules']['callback'];

        } elseif (is_object($data1)) {
            // An instance of HTML_QuickForm_Rule
            $this->_rules[strtolower(get_class($data1))] = $data1;
            $GLOBALS['_HTML_QuickForm_registered_rules'][$ruleName] = array(strtolower(get_class($data1)), null);

        } else {
            // Rule class name
            $GLOBALS['_HTML_QuickForm_registered_rules'][$ruleName] = array(strtolower($data1), $data2);
        }
    } // end func registerRule

    /**
     * Returns a reference to the requested rule object
     *
     * @param     string   $ruleName        Name of the requested rule
     * @access    public
     * @return    HTML_QuickForm_Rule
     */
    function &getRule($ruleName)
    {
        list($class, $path) = $GLOBALS['_HTML_QuickForm_registered_rules'][$ruleName];

        if (!isset($this->_rules[$class])) {
            if (!empty($path)) {
                include_once($path);
            }
            $this->_rules[$class] =& new $class();
        }
        $this->_rules[$class]->setName($ruleName);
        return $this->_rules[$class];
    } // end func getRule

    /**
     * Performs validation on the given values
     *
     * @param     string   $ruleName        Name of the rule to be used
     * @param     mixed    $values          Can be a scalar or an array of values 
     *                                      to be validated
     * @param     mixed    $options         Options used by the rule
     * @param     mixed    $multiple        Whether to validate an array of values altogether
     * @access    public
     * @return    mixed    true if no error found, int of valid values (when an array of values is given) or false if error
     */
    function validate($ruleName, $values, $options = null, $multiple = false)
    {
        $rule =& $this->getRule($ruleName);

        if (is_array($values) && !$multiple) {
            $result = 0;
            foreach ($values as $value) {
                if ($rule->validate($value, $options) === true) {
                    $result++;
                }
            }
            return ($result == 0) ? false : $result;
        } else {
            return $rule->validate($values, $options);
        }
    } // end func validate

    /**
     * Returns the validation test in javascript code
     *
     * @param     array|HTML_QuickForm_element  Element(s) the rule applies to
     * @param     string                        Element name, in case $element is 
     *                                          not an array
     * @param     array                         Rule data
     * @access    public
     * @return    string    JavaScript for the rule
     */
    function getValidationScript(&$element, $elementName, $ruleData)
    {
        $reset =  (isset($ruleData['reset'])) ? $ruleData['reset'] : false;
        $rule  =& $this->getRule($ruleData['type']);
        if (!is_array($element)) {
            list($jsValue, $jsReset) = $this->_getJsValue($element, $elementName, $reset, null);
        } else {
            $jsValue = "  value = new Array();\n";
            $jsReset = '';
            for ($i = 0; $i < count($element); $i++) {
                list($tmp_value, $tmp_reset) = $this->_getJsValue($element[$i], $element[$i]->getName(), $reset, $i);
                $jsValue .= "\n" . $tmp_value;
                $jsReset .= $tmp_reset;
            }
        }
        $jsField = isset($ruleData['group'])? $ruleData['group']: $elementName;
        list ($jsPrefix, $jsCheck) = $rule->getValidationScript($ruleData['format']);
        if (!isset($ruleData['howmany'])) {
            $js = $jsValue . "\n" . $jsPrefix . 
                  "  if (" . str_replace('{jsVar}', 'value', $jsCheck) . " && !errFlag['{$jsField}']) {\n" .
                  "    errFlag['{$jsField}'] = true;\n" .
                  "    _qfMsg = _qfMsg + '\\n - {$ruleData['message']}';\n" .
                  $jsReset .
                  "  }\n";
        } else {
            $js = $jsValue . "\n" . $jsPrefix . 
                  "  var res = 0;\n" .
                  "  for (var i = 0; i < value.length; i++) {\n" .
                  "    if (!(" . str_replace('{jsVar}', 'value[i]', $jsCheck) . ")) {\n" .
                  "      res++;\n" .
                  "    }\n" .
                  "  }\n" . 
                  "  if (res < {$ruleData['howmany']} && !errFlag['{$jsField}']) {\n" . 
                  "    errFlag['{$jsField}'] = true;\n" .
                  "    _qfMsg = _qfMsg + '\\n - {$ruleData['message']}';\n" .
                  $jsReset .
                  "  }\n";
        }
        return $js;
    } // end func getValidationScript


   /**
    * Returns JavaScript to get and to reset the element's value 
    * 
    * @access private
    * @param  HTML_QuickForm_element    element being processed
    * @param  string                    element's name
    * @param  bool                      whether to generate JavaScript to reset 
    *                                   the value
    * @param  integer                   value's index in the array (only used for
    *                                   multielement rules)
    * @return array     first item is value javascript, second is reset
    */
    function _getJsValue(&$element, $elementName, $reset = false, $index = null)
    {
        $jsIndex = isset($index)? '[' . $index . ']': '';
        $tmp_reset = $reset? "    var field = frm.elements['$elementName'];\n": '';
        if (is_a($element, 'html_quickform_group')) {
            $value = "  _qfGroups['{$elementName}'] = {";
            $elements =& $element->getElements();
            for ($i = 0, $count = count($elements); $i < $count; $i++) {
                $append = ($elements[$i]->getType() == 'select' && $elements[$i]->getMultiple())? '[]': '';
                $value .= "'" . $element->getElementName($i) . $append . "': true" .
                          ($i < $count - 1? ', ': '');
            }
            $value .=
                "};\n" .
                "  value{$jsIndex} = new Array();\n" .
                "  var valueIdx = 0;\n" .
                "  for (var i = 0; i < frm.elements.length; i++) {\n" .
                "    var _element = frm.elements[i];\n" .
                "    if (_element.name in _qfGroups['{$elementName}']) {\n" . 
                "      switch (_element.type) {\n" .
                "        case 'checkbox':\n" .
                "        case 'radio':\n" .
                "          if (_element.checked) {\n" .
                "            value{$jsIndex}[valueIdx++] = _element.value;\n" .
                "          }\n" .
                "          break;\n" .
                "        case 'select-one':\n" .
                "          if (-1 != _element.selectedIndex) {\n" .
                "            value{$jsIndex}[valueIdx++] = _element.options[_element.selectedIndex].value;\n" .
                "          }\n" .
                "          break;\n" .
                "        case 'select-multiple':\n" .
                "          var tmpVal = new Array();\n" .
                "          var tmpIdx = 0;\n" .
                "          for (var j = 0; j < _element.options.length; j++) {\n" .
                "            if (_element.options[j].selected) {\n" .
                "              tmpVal[tmpIdx++] = _element.options[j].value;\n" .
                "            }\n" .
                "          }\n" .
                "          if (tmpIdx > 0) {\n" .
                "            value{$jsIndex}[valueIdx++] = tmpVal;\n" .
                "          }\n" .
                "          break;\n" .
                "        default:\n" .
                "          value{$jsIndex}[valueIdx++] = _element.value;\n" .
                "      }\n" .
                "    }\n" .
                "  }\n";
            if ($reset) {
                $tmp_reset =
                    "    for (var i = 0; i < frm.elements.length; i++) {\n" .
                    "      var _element = frm.elements[i];\n" .
                    "      if (_element.name in _qfGroups['{$elementName}']) {\n" . 
                    "        switch (_element.type) {\n" .
                    "          case 'checkbox':\n" .
                    "          case 'radio':\n" .
                    "            _element.checked = _element.defaultChecked;\n" .
                    "            break;\n" .
                    "          case 'select-one':\n" .
                    "          case 'select-multiple':\n" .
                    "            for (var j = 0; j < _element.options.length; j++) {\n" .
                    "              _element.options[j].selected = _element.options[j].defaultSelected;\n" .
                    "            }\n" .
                    "            break;\n" .
                    "          default:\n" .
                    "            _element.value = _element.defaultValue;\n" .
                    "        }\n" .
                    "      }\n" .
                    "    }\n";
            }

        } elseif ($element->getType() == 'select') {
            if ($element->getMultiple()) {
                $elementName .= '[]';
                $value =
                    "  value{$jsIndex} = new Array();\n" .
                    "  var valueIdx = 0;\n" .
                    "  for (var i = 0; i < frm.elements['{$elementName}'].options.length; i++) {\n" . 
                    "    if (frm.elements['{$elementName}'].options[i].selected) {\n" .
                    "      value{$jsIndex}[valueIdx++] = frm.elements['{$elementName}'].options[i].value;\n" .
                    "    }\n" .
                    "  }\n";
            } else {
                $value = "  value{$jsIndex} = frm.elements['{$elementName}'].selectedIndex == -1? '': frm.elements['{$elementName}'].options[frm.elements['{$elementName}'].selectedIndex].value;\n";
            }
            if ($reset) {
                $tmp_reset .= 
                    "    for (var i = 0; i < field.options.length; i++) {\n" .
                    "      field.options[i].selected = field.options[i].defaultSelected;\n" .
                    "    }\n";
            }

        } elseif ($element->getType() == 'checkbox') {
            if (is_a($element, 'html_quickform_advcheckbox')) {
                $value = "  value{$jsIndex} = frm.elements['$elementName'][1].checked? frm.elements['$elementName'][1].value: frm.elements['$elementName'][0].value;\n";
                $tmp_reset .= $reset ? "    field[1].checked = field[1].defaultChecked;\n" : '';
            } else {
                $value = "  value{$jsIndex} = frm.elements['$elementName'].checked? '1': '';\n";
                $tmp_reset .= $reset ? "    field.checked = field.defaultChecked;\n" : '';
            }

        } elseif ($element->getType() == 'radio') {
            $value = "  value{$jsIndex} = '';\n" .
                     // Fix for bug #5644
                     "  var els = 'length' in frm.elements['$elementName']? frm.elements['$elementName']: [ frm.elements['$elementName'] ];\n" .
                     "  for (var i = 0; i < els.length; i++) {\n" .
                     "    if (els[i].checked) {\n" .
                     "      value{$jsIndex} = els[i].value;\n" .
                     "    }\n" .
                     "  }";
            if ($reset) {
                $tmp_reset .= "    for (var i = 0; i < field.length; i++) {\n" .
                              "      field[i].checked = field[i].defaultChecked;\n" .
                              "    }";
            }

        } else {
            $value = "  value{$jsIndex} = frm.elements['$elementName'].value;";
            $tmp_reset .= ($reset) ? "    field.value = field.defaultValue;\n" : '';
        }
        return array($value, $tmp_reset);
    }
} // end class HTML_QuickForm_RuleRegistry
?>
