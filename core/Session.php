<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik;

use Exception;
use Piwik\Session\SaveHandler\DbTable;
use Zend_Session;

/**
 * Session initialization.
 */
class Session extends Zend_Session
{
    const SESSION_NAME = 'PIWIK_SESSID';

    protected static $sessionStarted = false;

    /**
     * Are we using file-based session store?
     *
     * @return bool  True if file-based; false otherwise
     */
    public static function isFileBasedSessions()
    {
        $config = Config::getInstance();
        return !isset($config->General['session_save_handler'])
        || $config->General['session_save_handler'] === 'files';
    }

    /**
     * Start the session
     *
     * @param array|bool $options An array of configuration options; the auto-start (bool) setting is ignored
     * @return void
     */
    public static function start($options = false)
    {
        if (headers_sent()
            || self::$sessionStarted
            || (defined('PIWIK_ENABLE_SESSION_START') && !PIWIK_ENABLE_SESSION_START)
        ) {
            return;
        }
        self::$sessionStarted = true;

        // use cookies to store session id on the client side
        @ini_set('session.use_cookies', '1');

        // prevent attacks involving session ids passed in URLs
        @ini_set('session.use_only_cookies', '1');

        // advise browser that session cookie should only be sent over secure connection
        if (ProxyHttp::isHttps()) {
            @ini_set('session.cookie_secure', '1');
        }

        // advise browser that session cookie should only be accessible through the HTTP protocol (i.e., not JavaScript)
        @ini_set('session.cookie_httponly', '1');

        // don't use the default: PHPSESSID
        @ini_set('session.name', self::SESSION_NAME);

        // proxies may cause the referer check to fail and
        // incorrectly invalidate the session
        @ini_set('session.referer_check', '');

        $currentSaveHandler = ini_get('session.save_handler');
        $config = Config::getInstance();

        if (self::isFileBasedSessions()) {
            // Note: this handler doesn't work well in load-balanced environments and may have a concurrency issue with locked session files

            // for "files", use our own folder to prevent local session file hijacking
            $sessionPath = self::getSessionsDirectory();
            // We always call mkdir since it also chmods the directory which might help when permissions were reverted for some reasons
            Filesystem::mkdir($sessionPath);

            @ini_set('session.save_handler', 'files');
            @ini_set('session.save_path', $sessionPath);
        } else if ($config->General['session_save_handler'] === 'dbtable'
            || in_array($currentSaveHandler, array('user', 'mm'))
        ) {
            // We consider these to be misconfigurations, in that:
            // - user  - we can't verify that user-defined session handler functions have already been set via session_set_save_handler()
            // - mm    - this handler is not recommended, unsupported, not available for Windows, and has a potential concurrency issue

            $config = array(
                'name'           => Common::prefixTable('session'),
                'primary'        => 'id',
                'modifiedColumn' => 'modified',
                'dataColumn'     => 'data',
                'lifetimeColumn' => 'lifetime',
            );

            $saveHandler = new DbTable($config);
            if ($saveHandler) {
                self::setSaveHandler($saveHandler);
            }
        }

        // garbage collection may disabled by default (e.g., Debian)
        if (ini_get('session.gc_probability') == 0) {
            @ini_set('session.gc_probability', 1);
        }

        try {
            parent::start();
            register_shutdown_function(array('Zend_Session', 'writeClose'), true);
        } catch (Exception $e) {
            Log::warning('Unable to start session: ' . $e->getMessage());

            $enableDbSessions = '';
            if (DbHelper::isInstalled()) {
                $enableDbSessions = "<br/>If you still experience issues after trying these changes,
			            			we recommend that you <a href='http://piwik.org/faq/how-to-install/#faq_133' target='_blank'>enable database session storage</a>.";
            }

            $pathToSessions = Filechecks::getErrorMessageMissingPermissions(Filesystem::getPathToPiwikRoot() . '/tmp/sessions/');
            $pathToSessions = SettingsPiwik::rewriteTmpPathWithInstanceId($pathToSessions);
            $message = sprintf("Error: %s %s %s\n<pre>Debug: the original error was \n%s</pre>",
                Piwik::translate('General_ExceptionUnableToStartSession'),
                $pathToSessions,
                $enableDbSessions,
                $e->getMessage()
            );

            Piwik_ExitWithMessage($message, $e->getTraceAsString());
        }
    }

    /**
     * Returns the directory session files are stored in.
     *
     * @return string
     */
    public static function getSessionsDirectory()
    {
        $path = PIWIK_USER_PATH . '/tmp/sessions';
        return SettingsPiwik::rewriteTmpPathWithInstanceId($path);
    }

    public static function close()
    {
        parent::writeClose();
    }
}
