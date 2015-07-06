<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
use Piwik\Container\StaticContainer;

/**
 * Override settings in libs/tcpdf_config.php
 *
 */

define('K_PATH_MAIN', PIWIK_VENDOR_PATH . '/tecnick.com/tcpdf/');

$pathTmpTCPDF = StaticContainer::get('path.tmp') . '/tcpdf/';

define('K_PATH_CACHE', $pathTmpTCPDF);
define('K_PATH_IMAGES', $pathTmpTCPDF);

if (!defined('K_TCPDF_EXTERNAL_CONFIG')) {

    // DOCUMENT_ROOT fix for IIS Webserver
    if ((!isset($_SERVER['DOCUMENT_ROOT'])) OR (empty($_SERVER['DOCUMENT_ROOT']))) {
        if (isset($_SERVER['SCRIPT_FILENAME'])) {
            $_SERVER['DOCUMENT_ROOT'] = str_replace('\\', '/', substr($_SERVER['SCRIPT_FILENAME'], 0, 0 - strlen($_SERVER['PHP_SELF'])));
        } elseif (isset($_SERVER['PATH_TRANSLATED'])) {
            $_SERVER['DOCUMENT_ROOT'] = str_replace('\\', '/', substr(str_replace('\\\\', '\\', $_SERVER['PATH_TRANSLATED']), 0, 0 - strlen($_SERVER['PHP_SELF'])));
        } else {
            // define here your DOCUMENT_ROOT path if the previous fails
            $_SERVER['DOCUMENT_ROOT'] = '/var/www';
        }
    }

    if (!defined('K_PATH_MAIN')) {
        // Automatic calculation for the following K_PATH_MAIN constant
        $k_path_main = str_replace('\\', '/', realpath(substr(dirname(__FILE__), 0, 0 - strlen('config'))));
        if (substr($k_path_main, -1) != '/') {
            $k_path_main .= '/';
        }

        /**
         * Installation path (/var/www/tcpdf/).
         * By default it is automatically calculated but you can also set it as a fixed string to improve performances.
         */
        if (!defined('K_PATH_MAIN')) {
            define ('K_PATH_MAIN', $k_path_main);
        }
    }

    if (!defined('K_PATH_URL')) {
        // Automatic calculation for the following K_PATH_URL constant
        $k_path_url = K_PATH_MAIN; // default value for console mode
        if (isset($_SERVER['HTTP_HOST']) AND (!empty($_SERVER['HTTP_HOST']))) {
            if (isset($_SERVER['HTTPS']) AND (!empty($_SERVER['HTTPS'])) AND strtolower($_SERVER['HTTPS']) != 'off') {
                $k_path_url = 'https://';
            } else {
                $k_path_url = 'http://';
            }
            $k_path_url .= $_SERVER['HTTP_HOST'];
            $k_path_url .= str_replace('\\', '/', substr(K_PATH_MAIN, (strlen($_SERVER['DOCUMENT_ROOT']) - 1)));
        }

        /**
         * URL path to tcpdf installation folder (http://localhost/tcpdf/).
         * By default it is automatically calculated but you can also set it as a fixed string to improve performances.
         */
        define ('K_PATH_URL', $k_path_url);
    }

    /**
     * path for PDF fonts
     * use K_PATH_MAIN.'fonts/old/' for old non-UTF8 fonts
     */
    define ('K_PATH_FONTS', K_PATH_MAIN . 'fonts/');

    /**
     * cache directory for temporary files (full path)
     */
    if (!defined('K_PATH_CACHE')) {
        define ('K_PATH_CACHE', K_PATH_MAIN . 'cache/');
    }

    /**
     * cache directory for temporary files (url path)
     */
    define ('K_PATH_URL_CACHE', K_PATH_URL . 'cache/');

    /**
     *images directory
     */
    if (!defined('K_PATH_IMAGES')) {
        define ('K_PATH_IMAGES', K_PATH_MAIN . 'images/');
    }

    /**
     * blank image
     */
    define ('K_BLANK_IMAGE', K_PATH_IMAGES . '_blank.png');

    /**
     * page format
     */
    define ('PDF_PAGE_FORMAT', 'A4');

    /**
     * page orientation (P=portrait, L=landscape)
     */
    define ('PDF_PAGE_ORIENTATION', 'P');

    /**
     * document creator
     */
    define ('PDF_CREATOR', 'TCPDF');

    /**
     * document author
     */
    define ('PDF_AUTHOR', 'TCPDF');

    /**
     * header title
     */
    define ('PDF_HEADER_TITLE', 'TCPDF Example');

    /**
     * header description string
     */
    define ('PDF_HEADER_STRING', "by Nicola Asuni - Tecnick.com\nwww.tcpdf.org");

    /**
     * image logo
     */
    define ('PDF_HEADER_LOGO', 'tcpdf_logo.jpg');

    /**
     * header logo image width [mm]
     */
    define ('PDF_HEADER_LOGO_WIDTH', 30);

    /**
     *  document unit of measure [pt=point, mm=millimeter, cm=centimeter, in=inch]
     */
    define ('PDF_UNIT', 'mm');

    /**
     * header margin
     */
    define ('PDF_MARGIN_HEADER', 5);

    /**
     * footer margin
     */
    define ('PDF_MARGIN_FOOTER', 10);

    /**
     * top margin
     */
    define ('PDF_MARGIN_TOP', 27);

    /**
     * bottom margin
     */
    define ('PDF_MARGIN_BOTTOM', 25);

    /**
     * left margin
     */
    define ('PDF_MARGIN_LEFT', 15);

    /**
     * right margin
     */
    define ('PDF_MARGIN_RIGHT', 15);

    /**
     * default main font name
     */
    define ('PDF_FONT_NAME_MAIN', 'helvetica');

    /**
     * default main font size
     */
    define ('PDF_FONT_SIZE_MAIN', 10);

    /**
     * default data font name
     */
    define ('PDF_FONT_NAME_DATA', 'helvetica');

    /**
     * default data font size
     */
    define ('PDF_FONT_SIZE_DATA', 8);

    /**
     * default monospaced font name
     */
    define ('PDF_FONT_MONOSPACED', 'courier');

    /**
     * ratio used to adjust the conversion of pixels to user units
     */
    define ('PDF_IMAGE_SCALE_RATIO', 1.25);

    /**
     * magnification factor for titles
     */
    define('HEAD_MAGNIFICATION', 1.1);

    /**
     * height of cell repect font height
     */
    define('K_CELL_HEIGHT_RATIO', 1.25);

    /**
     * title magnification respect main font size
     */
    define('K_TITLE_MAGNIFICATION', 1.3);

    /**
     * reduction factor for small font
     */
    define('K_SMALL_RATIO', 2 / 3);

    /**
     * set to true to enable the special procedure used to avoid the overlappind of symbols on Thai language
     */
    define('K_THAI_TOPCHARS', true);

    /**
     * if true allows to call TCPDF methods using HTML syntax
     * IMPORTANT: For security reason, disable this feature if you are printing user HTML content.
     */
    define('K_TCPDF_CALLS_IN_HTML', true);
}

// define the constant K_TCPDF_EXTERNAL_CONFIG to ignore tcpdf's default settings
define('K_TCPDF_EXTERNAL_CONFIG', true);

//============================================================+
// END OF FILE
//============================================================+
