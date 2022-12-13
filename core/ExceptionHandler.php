<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik;

use DI\DependencyException;
use Exception;
use Piwik\API\Request;
use Piwik\API\ResponseBuilder;
use Piwik\Container\ContainerDoesNotExistException;
use Piwik\Container\StaticContainer;
use Piwik\Exception\IRedirectException;
use Piwik\Plugins\CoreAdminHome\CustomLogo;
use Piwik\Plugins\Monolog\Processor\ExceptionToTextProcessor;
use Psr\Log\LoggerInterface;

/**
 * Contains Piwik's uncaught exception handler.
 */
class ExceptionHandler
{
    public static function setUp()
    {
        set_exception_handler(['Piwik\ExceptionHandler', 'handleException']);
    }

    /**
     * @param Exception|\Throwable $exception
     */
    public static function handleException($exception)
    {
        if (Common::isPhpCliMode()) {
            self::dieWithCliError($exception);
        }

        self::dieWithHtmlErrorPage($exception);
    }

    /**
     * @param Exception|\Throwable $exception
     */
    public static function dieWithCliError($exception)
    {
        self::logException($exception);

        $message = $exception->getMessage();

        if (!method_exists($exception, 'isHtmlMessage') || !$exception->isHtmlMessage()) {
            $message = strip_tags(str_replace('<br />', PHP_EOL, $message));
        }

        $message = sprintf(
            "Uncaught exception in %s line %d:\n%s\n",
            $exception->getFile(),
            $exception->getLine(),
            ExceptionToTextProcessor::getMessageAndWholeBacktrace($exception)
        );

        echo $message;

        exit(1);
    }

    /**
     * @param Exception|\Throwable $exception
     */
    public static function dieWithHtmlErrorPage($exception)
    {
        // Set an appropriate HTTP response code.
        switch (true) {
            case ( ($exception instanceof \Piwik\Http\HttpCodeException || $exception instanceof \Piwik\Exception\NotSupportedBrowserException) && $exception->getCode() > 0):
                // For these exception types, use the exception-provided error code.
                http_response_code($exception->getCode());
                break;
            case ($exception instanceof \Piwik\Exception\NotYetInstalledException):
                http_response_code(404);
                break;
            default:
                http_response_code(500);
        }

        // Log the error with an appropriate loglevel.
        switch (true) {
            case ($exception instanceof \Piwik\Exception\NotSupportedBrowserException):
                // These unsupported browsers are really a client-side problem, so log only at DEBUG level.
                self::logException($exception, Log::DEBUG);
                break;
            default:
                self::logException($exception);
        }

        Common::sendHeader('Content-Type: text/html; charset=utf-8');

        try {
            echo self::getErrorResponse($exception);
        } catch (Exception $e) {
            // When there are failures while generating the HTML error response itself,
            // we simply print out the error message instead.
            echo $exception->getMessage();
        }

        exit(1);
    }

    /**
     * @param Exception|\Throwable $ex
     */
    private static function getErrorResponse($ex)
    {
        $debugTrace = $ex->getTraceAsString();

        $message = $ex->getMessage();

        $isHtmlMessage = method_exists($ex, 'isHtmlMessage') && $ex->isHtmlMessage();

        if (!$isHtmlMessage && Request::isApiRequest($_GET)) {
            $outputFormat = strtolower(Common::getRequestVar('format', 'xml', 'string', $_GET + $_POST));
            $response = new ResponseBuilder($outputFormat);
            return $response->getResponseException($ex);
        } elseif (!$isHtmlMessage) {
            $message = Common::sanitizeInputValue($message);
        }


        $logoHeaderUrl = 'plugins/Morpheus/images/logo.svg';
        $logoFaviconUrl = 'plugins/CoreHome/images/favicon.png';
        try {
            $logo = new CustomLogo();
            if ($logo->hasSVGLogo()) {
                $logoHeaderUrl = $logo->getSVGLogoUrl();
            } else {
                $logoHeaderUrl = $logo->getHeaderLogoUrl();
            }
            $logoFaviconUrl = $logo->getPathUserFavicon();
        } catch (Exception $ex) {
            try {
                Log::debug($ex);
            } catch (\Exception $otherEx) {
                // DI container may not be setup at this point
            }
        }

        // Unsupported browser errors shouldn't be written to the web server log. At DEBUG logging level this error will
        // be written to the application log instead
        $writeErrorLog = !($ex instanceof \Piwik\Exception\NotSupportedBrowserException);

        $redirectUrl = null;
        $countdownToRedirect = null;
        if ($ex instanceof IRedirectException) {
            $redirectUrl = $ex->getRedirectionUrl();
            $countdownToRedirect = $ex->getCountdown();
        }

        $hostname = Url::getRFCValidHostname();
        $hostStr = $hostname ? "[$hostname] " : '- ';

        $result = Piwik_GetErrorMessagePage(
            $message,
            $debugTrace,
            true,
            true,
            $logoHeaderUrl,
            $logoFaviconUrl,
            null,
            $hostStr,
            $writeErrorLog,
            $redirectUrl,
            $countdownToRedirect
        );

        try {
            /**
             * Triggered before a Piwik error page is displayed to the user.
             *
             * This event can be used to modify the content of the error page that is displayed when
             * an exception is caught.
             *
             * @param string &$result The HTML of the error page.
             * @param Exception $ex The Exception displayed in the error page.
             */
            Piwik::postEvent('FrontController.modifyErrorPage', [&$result, $ex]);
        } catch (ContainerDoesNotExistException $ex) {
            // this can happen when an error occurs before the Piwik environment is created
        }

        return $result;
    }

    private static function logException($exception, $loglevel = Log::ERROR)
    {
        try {
            switch ($loglevel) {
                case (Log::DEBUG):
                    StaticContainer::get(LoggerInterface::class)->debug('Uncaught exception: {exception}', [
                        'exception' => $exception,
                        'ignoreInScreenWriter' => true,
                    ]);
                    break;
                case (Log::ERROR):
                default:
                    StaticContainer::get(LoggerInterface::class)->error('Uncaught exception: {exception}', [
                        'exception' => $exception,
                        'ignoreInScreenWriter' => true,
                    ]);
            }
        } catch (DependencyException $ex) {
            // ignore (occurs if exception is thrown when resolving DI entries)
        } catch (ContainerDoesNotExistException $ex) {
            // ignore
        }
    }
}
