<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty {html_image} function plugin
 *
 * Type:     function<br>
 * Name:     html_image<br>
 * Date:     Feb 24, 2003<br>
 * Purpose:  format HTML tags for the image<br>
 * Input:<br>
 *         - file = file (and path) of image (required)
 *         - border = border width (optional, default 0)
 *         - height = image height (optional, default actual height)
 *         - image =image width (optional, default actual width)
 *         - basedir = base directory for absolute paths, default
 *                     is environment variable DOCUMENT_ROOT
 *
 * Examples: {html_image file="images/masthead.gif"}
 * Output:   <img src="images/masthead.gif" border=0 width=400 height=23>
 * @link http://smarty.php.net/manual/en/language.function.html.image.php {html_image}
 *      (Smarty online manual)
 * @author   Monte Ohrt <monte@ispi.net>
 * @author credits to Duda <duda@big.hu> - wrote first image function
 *           in repository, helped with lots of functionality
 * @version  1.0
 * @param array
 * @param Smarty
 * @return string
 * @uses smarty_function_escape_special_chars()
 */
function smarty_function_html_image($params, &$smarty)
{
    require_once $smarty->_get_plugin_filepath('shared','escape_special_chars');
    
    $alt = '';
    $file = '';
    $border = 0;
    $height = '';
    $width = '';
    $extra = '';
    $prefix = '';
    $suffix = '';
    $basedir = isset($GLOBALS['HTTP_SERVER_VARS']['DOCUMENT_ROOT'])
        ? $GLOBALS['HTTP_SERVER_VARS']['DOCUMENT_ROOT'] : '';
    if(strstr($GLOBALS['HTTP_SERVER_VARS']['HTTP_USER_AGENT'], 'Mac')) {
        $dpi_default = 72;
    } else {
        $dpi_default = 96;
    }

    foreach($params as $_key => $_val) {
        switch($_key) {
            case 'file':
            case 'border':
            case 'height':
            case 'width':
            case 'dpi':
            case 'basedir':
                $$_key = $_val;
                break;

            case 'alt':
                if(!is_array($_val)) {
                    $$_key = smarty_function_escape_special_chars($_val);
                } else {
                    $smarty->trigger_error("html_image: extra attribute '$_key' cannot be an array", E_USER_NOTICE);
                }
                break;

            case 'link':
            case 'href':
                $prefix = '<a href="' . $_val . '">';
                $suffix = '</a>';
                break;

            default:
                if(!is_array($_val)) {
                    $extra .= ' '.$_key.'="'.smarty_function_escape_special_chars($_val).'"';
                } else {
                    $smarty->trigger_error("html_image: extra attribute '$_key' cannot be an array", E_USER_NOTICE);
                }
                break;
        }
    }

    if (empty($file)) {
        $smarty->trigger_error("html_image: missing 'file' parameter", E_USER_NOTICE);
        return;
    }

    if (substr($file,0,1) == '/') {
        $_image_path = $basedir . $file;
    } else {
        $_image_path = $file;
    }

    if(!isset($params['width']) || !isset($params['height'])) {
        if(!$_image_data = @getimagesize($_image_path)) {
            if(!file_exists($_image_path)) {
                $smarty->trigger_error("html_image: unable to find '$_image_path'", E_USER_NOTICE);
                return;
            } else if(!is_readable($_image_path)) {
                $smarty->trigger_error("html_image: unable to read '$_image_path'", E_USER_NOTICE);
                return;
            } else {
                $smarty->trigger_error("html_image: '$_image_path' is not a valid image file", E_USER_NOTICE);
                return;
            }
        }
        $_params = array('resource_type' => 'file', 'resource_name' => $_image_path);
        require_once(SMARTY_DIR . 'core' . DIRECTORY_SEPARATOR . 'core.is_secure.php');
        if(!$smarty->security && !smarty_core_is_secure($_params, $smarty)) {
            $smarty->trigger_error("html_image: (secure) '$_image_path' not in secure directory", E_USER_NOTICE);
            return;
        }

        if(!isset($params['width'])) {
            $width = $_image_data[0];
        }
        if(!isset($params['height'])) {
            $height = $_image_data[1];
        }

    }

    if(isset($params['dpi'])) {
        $_resize = $dpi_default/$params['dpi'];
        $width = round($width * $_resize);
        $height = round($height * $_resize);
    }

    return $prefix . '<img src="'.$file.'" alt="'.$alt.'" border="'.$border.'" width="'.$width.'" height="'.$height.'"'.$extra.' />' . $suffix;
}

/* vim: set expandtab: */

?>
