<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Application\Kernel;

use Piwik\Application\Kernel\GlobalSettingsProvider\IniSettingsProvider;
use Piwik\Common;
use Piwik\Piwik;
use Piwik\SettingsServer;
use Piwik\Translation\Translator;

/**
 * Validates the Piwik environment. This includes making sure the required config files
 * are present, and triggering the correct behaviour if otherwise.
 */
class EnvironmentValidator
{
    /**
     * @var IniSettingsProvider
     */
    protected $iniSettingsProvider;

    /**
     * @var Translator
     */
    protected $translator;

    public function __construct(GlobalSettingsProvider $settingsProvider, Translator $translator)
    {
        $this->iniSettingsProvider = $settingsProvider;
    }

    public function validate()
    {
        $inTrackerRequest = SettingsServer::isTrackerApiRequest();
        $inConsole = Common::isPhpCliMode();

        $this->checkConfigFileExists('global.ini.php');
        $this->checkConfigFileExists('config.ini.php', $startInstaller = !$inTrackerRequest && !$inConsole);
    }

    /**
     * @param $filename
     * @param bool $startInstaller
     * @throws \Exception
     */
    private function checkConfigFileExists($filename, $startInstaller = false)
    {
        $path = PIWIK_INCLUDE_PATH . '/config/' . $filename;

        if (is_readable($path)) {
            return;
        }

        $message = $this->translator->translate('General_ExceptionConfigurationFileNotFound', array($path));
        $exception = new \Exception($message);

        if ($startInstaller) {
            /**
             * Triggered when the configuration file cannot be found or read, which usually
             * means Piwik is not installed yet.
             *
             * This event can be used to start the installation process or to display a custom error message.
             *
             * @param \Exception $exception The exception that was thrown by `Config::getInstance()`.
             */
            Piwik::postEvent('Config.NoConfigurationFile', array($exception), $pending = true);
        } else {
            throw $exception;
        }
    }
}