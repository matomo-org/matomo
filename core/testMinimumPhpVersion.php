<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package Piwik
 */

/**
 * This file is executed before anything else.
 * It checks the minimum PHP version required to run Piwik.
 * This file must be compatible PHP4.
 */

$piwik_errorMessage = '';

// Minimum requirement: ->newInstanceArgs in 5.1.3
$piwik_minimumPHPVersion = '5.1.3RC';
$piwik_currentPHPVersion = PHP_VERSION;
$minimumPhpInvalid = version_compare($piwik_minimumPHPVersion, $piwik_currentPHPVersion) > 0;
if ($minimumPhpInvalid) {
    $piwik_errorMessage .= "<p><b>To run Piwik you need at least PHP version $piwik_minimumPHPVersion</b></p>
				<p>Unfortunately it seems your webserver is using PHP version $piwik_currentPHPVersion. </p>
				<p>Please try to update your PHP version, Piwik is really worth it! Nowadays most web hosts 
				support PHP $piwik_minimumPHPVersion.</p>
				<p>Also see the FAQ: <a href='http://piwik.org/faq/how-to-install/#faq_77'>My Web host supports PHP4 by default. How can I enable PHP5?</a></p>";
} else {
    $piwik_zend_compatibility_mode = ini_get("zend.ze1_compatibility_mode");
    if ($piwik_zend_compatibility_mode == 1) {
        $piwik_errorMessage .= "<p><b>Piwik is not compatible with the directive <code>zend.ze1_compatibility_mode = On</code></b></p>
					<p>It seems your php.ini file has <pre>zend.ze1_compatibility_mode = On</pre>It makes PHP5 behave like PHP4.
					If you want to use Piwik you need to set <pre>zend.ze1_compatibility_mode = Off</pre> in your php.ini configuration file, and restart your web server. You may have to ask your system administrator.</p>";
    }

    if (!class_exists('ArrayObject')) {
        $piwik_errorMessage .= "<p><b>Piwik and Zend Framework require the SPL extension</b></p>
					<p>It appears your PHP was compiled with <pre>--disable-spl</pre>.
					To enjoy Piwik, you need PHP compiled without that configure option.</p>";
    }

    if (!extension_loaded('session')) {
        $piwik_errorMessage .= "<p><b>Piwik and Zend_Session require the session extension</b></p>
					<p>It appears your PHP was compiled with <pre>--disable-session</pre>.
					To enjoy Piwik, you need PHP compiled without that configure option.</p>";
    }

    if (!function_exists('ini_set')) {
        $piwik_errorMessage .= "<p><b>Piwik and Zend_Session require the <code>ini_set()</code> function</b></p>
					<p>It appears your PHP has disabled this function.
					To enjoy Piwik, you need remove <pre>ini_set</pre> from your <pre>disable_functions</pre> directive in php.ini, and restart your webserver.</p>";
    }
}

if (!function_exists('Piwik_ExitWithMessage')) {
    /**
     * Displays info/warning/error message in a friendly UI and exits.
     *
     * @param string $message Main message, must be html encoded before calling
     * @param bool|string $optionalTrace Backtrace; will be displayed in lighter color
     * @param bool $optionalLinks If true, will show links to the Piwik website for help
     */
    function Piwik_ExitWithMessage($message, $optionalTrace = false, $optionalLinks = false)
    {
        @header('Content-Type: text/html; charset=utf-8');
        if ($optionalTrace) {
            $optionalTrace = '<span style="color:#888888">Backtrace:<br /><pre>' . $optionalTrace . '</pre></span>';
        }
        if ($optionalLinks) {
            $optionalLinks = '<ul>
                            <li><a target="_blank" href="http://piwik.org">Piwik.org homepage</a></li>
                            <li><a target="_blank" href="http://piwik.org/faq/">Piwik Frequently Asked Questions</a></li>
                            <li><a target="_blank" href="http://piwik.org/docs/">Piwik Documentation</a></li>
                            <li><a target="_blank" href="http://forum.piwik.org/">Piwik Forums</a></li>
                            <li><a target="_blank" href="http://demo.piwik.org">Piwik Online Demo</a></li>
                            </ul>';
        }
        $headerPage = file_get_contents(PIWIK_INCLUDE_PATH . '/themes/default/simple_structure_header.tpl');
        $footerPage = file_get_contents(PIWIK_INCLUDE_PATH . '/themes/default/simple_structure_footer.tpl');

        $headerPage = str_replace('{$HTML_TITLE}', 'Piwik &rsaquo; Error', $headerPage);
        $content = '<p>' . $message . '</p>
                    <p><a href="index.php">Go to Piwik</a><br/>
                    <a href="index.php?module=Login">Login</a></p>
                    ' . $optionalTrace . ' ' . $optionalLinks;

        echo $headerPage . $content . $footerPage;
        exit;
    }
}

if (!empty($piwik_errorMessage)) {
    Piwik_ExitWithMessage($piwik_errorMessage, false, true);
}
