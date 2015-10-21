<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

/**
 * This file is executed before anything else.
 * It checks the minimum PHP version required to run Piwik.
 * This file must be compatible PHP4.
 */

$piwik_errorMessage = '';

// Minimum requirement: stream_resolve_include_path, working json_encode in 5.3.3, namespaces in 5.3
// NOTE: when changing this variable, we also need to update
// 1) api.piwik.org
// 2) tests/travis/generator/Generator.php
$piwik_minimumPHPVersion = '5.3.3';
$piwik_currentPHPVersion = PHP_VERSION;
$minimumPhpInvalid = version_compare($piwik_minimumPHPVersion, $piwik_currentPHPVersion) > 0;
if ($minimumPhpInvalid) {
    $piwik_errorMessage .= "<p><strong>To run Piwik you need at least PHP version $piwik_minimumPHPVersion</strong></p>
				<p>Unfortunately it seems your webserver is using PHP version $piwik_currentPHPVersion. </p>
				<p>Please try to update your PHP version, Piwik is really worth it! Nowadays most web hosts
				support PHP $piwik_minimumPHPVersion.</p>
				<p>Also see the FAQ: <a href='http://piwik.org/faq/how-to-install/#faq_77'>My Web host supports PHP4 by default. How can I enable PHP5?</a></p>";
} else {
    if (!extension_loaded('session')) {
        $piwik_errorMessage .= "<p><strong>Piwik and Zend_Session require the session extension</strong></p>
					<p>It appears your PHP was compiled with <pre>--disable-session</pre>.
					To enjoy Piwik, you need PHP compiled without that configure option.</p>";
    }

    if (!function_exists('ini_set')) {
        $piwik_errorMessage .= "<p><strong>Piwik and Zend_Session require the <code>ini_set()</code> function</strong></p>
					<p>It appears your PHP has disabled this function.
					To enjoy Piwik, you need remove <pre>ini_set</pre> from your <pre>disable_functions</pre> directive in php.ini, and restart your webserver.</p>";
    }

    if (ini_get('mbstring.func_overload')) {
        $piwik_errorMessage .= "<p><strong>Piwik does not work when PHP is configured with <pre>mbstring.func_overload = " . ini_get('mbstring.func_overload') . "</pre></strong></p>
					<p>It appears your mbstring extension in PHP is configured to override string functions.
					To enjoy Piwik, you need to modify php.ini <pre>mbstring.func_overload = 0</pre>, and restart your webserver.</p>";
    }

    if (!function_exists('json_encode')) {
        $piwik_errorMessage .= "<p><strong>Piwik requires the php5-json extension which provides the functions <code>json_encode()</code> and <code>json_decode()</code></strong></p>
					<p>It appears your PHP has not yet installed the php5-json extension.
					To use Piwik, please ask your web host to install php5-json or install it yourself, for example on debian system: <code>sudo apt-get install php5-json</code>. <br/>Then restart your webserver and refresh this page.</p>";
    }

    if (!file_exists(PIWIK_VENDOR_PATH . '/autoload.php')) {
        $composerInstall = "In the piwik directory, run in the command line the following (eg. via ssh): \n\n"
            . "<pre> curl -sS https://getcomposer.org/installer | php \n\n php composer.phar install\n\n</pre> ";
        if (DIRECTORY_SEPARATOR === '\\' /* ::isWindows() */) {
            $composerInstall = "Download and run <a href=\"https://getcomposer.org/Composer-Setup.exe\"><b>Composer-Setup.exe</b></a>, it will install the latest Composer version and set up your PATH so that you can just call composer from any directory in your command line. "
                . " <br>Then run this command in a terminal in the piwik directory: <br> $ php composer.phar install ";
        }
        $piwik_errorMessage .= "<p>It appears the <a href='https://getcomposer.org/' rel='noreferrer' target='_blank'>composer</a> tool is not yet installed. You can install Composer in a few easy steps:\n\n".
                    "<br/>" . $composerInstall.
                    " This will initialize composer for Piwik and download libraries we use in vendor/* directory.".
                    "\n\n<br/><br/>Then reload this page to access your analytics reports." .
                    "\n\n<br/><br/>For more information check out this FAQ: <a href='http://piwik.org/faq/how-to-install/faq_18271/' rel='noreferrer' target='_blank'>How do I use Piwik from the Git repository?</a>." .
                    "\n\n<br/><br/>Note: if for some reasons you cannot install composer, instead install the latest Piwik release from ".
                    "<a href='http://builds.piwik.org/piwik.zip'>builds.piwik.org</a>.</p>";
    }
}

define('PAGE_TITLE_WHEN_ERROR', 'Piwik &rsaquo; Error');

if (!function_exists('Piwik_GetErrorMessagePage')) {
    /**
     * Returns true if Piwik should print the backtrace with error messages.
     *
     * To make sure the backtrace is printed, define PIWIK_PRINT_ERROR_BACKTRACE.
     *
     * @return bool
     */
    function Piwik_ShouldPrintBackTraceWithMessage()
    {
        $bool = (defined('PIWIK_PRINT_ERROR_BACKTRACE') && PIWIK_PRINT_ERROR_BACKTRACE)
                || !empty($GLOBALS['PIWIK_TRACKER_DEBUG']);

        return $bool;
    }

    /**
     * Displays info/warning/error message in a friendly UI and exits.
     *
     * Note: this method should not be called by anyone other than FrontController.
     *
     * @param string $message Main message, must be html encoded before calling
     * @param bool|string $optionalTrace Backtrace; will be displayed in lighter color
     * @param bool $optionalLinks If true, will show links to the Piwik website for help
     * @param bool $optionalLinkBack If true, displays a link to go back
     * @param bool|string $logoUrl The URL to the logo to use.
     * @param bool|string $faviconUrl The URL to the favicon to use.
     * @return string
     */
    function Piwik_GetErrorMessagePage($message, $optionalTrace = false, $optionalLinks = false, $optionalLinkBack = false,
                                       $logoUrl = false, $faviconUrl = false, $isCli = null)
    {
        error_log(sprintf("Error in Piwik: %s", str_replace("\n", " ", strip_tags($message))));

        if (!headers_sent()) {
            header('Content-Type: text/html; charset=utf-8');

            $isInternalServerError = preg_match('/(sql|database|mysql)/i', $message);
            if ($isInternalServerError) {
                header('HTTP/1.1 500 Internal Server Error');
            }
        }

        // We return only an HTML fragment for AJAX requests
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])
            && (strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
        ) {
            return "<div class='alert alert-danger'><strong>Error:</strong> $message</div>";
        }

        if (empty($logoUrl)) {
            $logoUrl = "plugins/Morpheus/images/logo-header.png";
        }

        if (empty($faviconUrl)) {
            $faviconUrl = "plugins/CoreHome/images/favicon.ico";
        }

        if ($optionalTrace) {
            $optionalTrace = '<h2>Stack trace</h2><pre>' . htmlentities($optionalTrace) . '</pre>';
        }

        if ($isCli === null) {
            $isCli = PHP_SAPI == 'cli';
        }

        if ($optionalLinks) {
            $optionalLinks = '<ul>
                            <li><a rel="noreferrer" target="_blank" href="http://piwik.org">Piwik.org homepage</a></li>
                            <li><a rel="noreferrer" target="_blank" href="http://piwik.org/faq/">Piwik Frequently Asked Questions</a></li>
                            <li><a rel="noreferrer" target="_blank" href="http://piwik.org/docs/">Piwik Documentation</a></li>
                            <li><a rel="noreferrer" target="_blank" href="http://forum.piwik.org/">Piwik Forums</a></li>
                            <li><a rel="noreferrer" target="_blank" href="http://demo.piwik.org">Piwik Online Demo</a></li>
                            </ul>';
        }
        if ($optionalLinkBack) {
            $optionalLinkBack = '<a href="javascript:window.history.back();">Go Back</a>';
        }

        $headerPage = file_get_contents(PIWIK_INCLUDE_PATH . '/plugins/Morpheus/templates/simpleLayoutHeader.tpl');
        $headerPage = str_replace('%logoUrl%', $logoUrl, $headerPage);
        $headerPage = str_replace('%faviconUrl%', $faviconUrl, $headerPage);

        $footerPage = file_get_contents(PIWIK_INCLUDE_PATH . '/plugins/Morpheus/templates/simpleLayoutFooter.tpl');

        $headerPage = str_replace('{$HTML_TITLE}', PAGE_TITLE_WHEN_ERROR, $headerPage);

        $content = '<h2>' . $message . '</h2>
                    <p>'
            . $optionalLinkBack
            . ' | <a href="index.php">Go to Piwik</a>'
            . '</p>'
            . ' ' . (Piwik_ShouldPrintBackTraceWithMessage() ? $optionalTrace : '')
            . ' ' . $optionalLinks;


        $message = str_replace(array("<br />", "<br>", "<br/>", "</p>"), "\n", $message);
        $message = str_replace("\t", "", $message);
        $message = strip_tags($message);

        if (!$isCli) {
            $message = $headerPage . $content . $footerPage;
        }

        $message .= "\n";

        return $message;
    }
}

if (!empty($piwik_errorMessage)) {
    echo Piwik_GetErrorMessagePage($piwik_errorMessage, false, true);
    exit(1);
}
