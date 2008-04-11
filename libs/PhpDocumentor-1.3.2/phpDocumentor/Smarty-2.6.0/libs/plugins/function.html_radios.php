<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty {html_radios} function plugin
 *
 * File:       function.html_radios.php<br>
 * Type:       function<br>
 * Name:       html_radios<br>
 * Date:       24.Feb.2003<br>
 * Purpose:    Prints out a list of radio input types<br>
 * Input:<br>
 *           - name       (optional) - string default "radio"
 *           - values     (required) - array
 *           - options    (optional) - associative array
 *           - checked    (optional) - array default not set
 *           - separator  (optional) - ie <br> or &nbsp;
 *           - output     (optional) - without this one the buttons don't have names
 * Examples:
 * <pre>
 * {html_radios values=$ids output=$names}
 * {html_radios values=$ids name='box' separator='<br>' output=$names}
 * {html_radios values=$ids checked=$checked separator='<br>' output=$names}
 * </pre>
 * @link http://smarty.php.net/manual/en/language.function.html.radios.php {html_radios}
 *      (Smarty online manual)
 * @author     Christopher Kvarme <christopher.kvarme@flashjab.com>
 * @author credits to Monte Ohrt <monte@ispi.net>
 * @version    1.0
 * @param array
 * @param Smarty
 * @return string
 * @uses smarty_function_escape_special_chars()
 */
function smarty_function_html_radios($params, &$smarty)
{
   require_once $smarty->_get_plugin_filepath('shared','escape_special_chars');
   
   $name = 'radio';
   $values = null;
   $options = null;
   $selected = null;
   $separator = '';
   $labels = true;
   $output = null;
   $extra = '';

   foreach($params as $_key => $_val) {
		switch($_key) {
		case 'name':
		case 'separator':
		    $$_key = (string)$_val;
		    break;

		case 'checked':
		case 'selected':
			if(is_array($_val)) {
				$smarty->trigger_error('html_radios: the "' . $_key . '" attribute cannot be an array', E_USER_WARNING);
			} else {
				$selected = (string)$_val;
			}
			break;

        case 'labels':
            $$_key = (bool)$_val;
            break;

		case 'options':
			$$_key = (array)$_val;
			break;

		case 'values':
		case 'output':
			$$_key = array_values((array)$_val);
			break;

		case 'radios':
			$smarty->trigger_error('html_radios: the use of the "radios" attribute is deprecated, use "options" instead', E_USER_WARNING);
			$options = (array)$_val;
			break;


		default:
			if(!is_array($_val)) {
				$extra .= ' '.$_key.'="'.smarty_function_escape_special_chars($_val).'"';
			} else {
				$smarty->trigger_error("html_radios: extra attribute '$_key' cannot be an array", E_USER_NOTICE);
			}
			break;
		}
   }

   if (!isset($options) && !isset($values))
      return ''; /* raise error here? */

   $_html_result = '';

   if (isset($options) && is_array($options)) {

      foreach ((array)$options as $_key=>$_val)
	 $_html_result .= smarty_function_html_radios_output($name, $_key, $_val, $selected, $extra, $separator, $labels);

   } else {

      foreach ((array)$values as $_i=>$_key) {
	 $_val = isset($output[$_i]) ? $output[$_i] : '';
	 $_html_result .= smarty_function_html_radios_output($name, $_key, $_val, $selected, $extra, $separator, $labels);
      }

   }

   return $_html_result;

}

function smarty_function_html_radios_output($name, $value, $output, $selected, $extra, $separator, $labels) {
   $_output = '';
   if ($labels) $_output .= '<label>';
   $_output .= '<input type="radio" name="'
      . smarty_function_escape_special_chars($name) . '" value="'
      . smarty_function_escape_special_chars($value) . '"';

   if ($value==$selected) {
      $_output .= ' checked="checked"';
   }
   $_output .= $extra . ' />' . $output;
   if ($labels) $_output .= '</label>';
   $_output .=  $separator . "\n";

   return $_output;
}

?>
