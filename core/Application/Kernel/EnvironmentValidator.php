<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Application\Kernel;

use Piwik\Common;
use Piwik\Exception\NotYetInstalledException;
use Piwik\Filechecks;
use Piwik\Piwik;
use Piwik\SettingsPiwik;
use Piwik\SettingsServer;
use Piwik\Translation\Translator;

/**
 * Validates the Piwik environment. This includes making sure the required config files
 * are present, and triggering the correct behaviour if otherwise.
 */
class EnvironmentValidator
{
    /**
     * @var GlobalSettingsProvider
     */
    protected $settingsProvider;

    /**
     * @var Translator
     */
    protected $translator;

    public function __construct(GlobalSettingsProvider $settingsProvider, Translator $translator)
    {
        $this->settingsProvider = $settingsProvider;
        $this->translator = $translator;
    }

    public function validate()
    {
        $this->checkConfigFileExists($this->settingsProvider->getPathGlobal());

        if (SettingsPiwik::isMatomoInstalled()) {
            $this->checkConfigFileExists($this->settingsProvider->getPathLocal(), $startInstaller = false);
            return;
        }

        $startInstaller = true;

        if (SettingsServer::isTrackerApiRequest()) {
            // if Piwik is not installed yet, the piwik.php should do nothing and not return an error
            throw new NotYetInstalledException("As Matomo is not installed yet, the Tracking API cannot proceed and will exit without error.");
        }

        if (Common::isPhpCliMode()) {
            // in CLI, do not start/redirect to installer, simply output the exception at the top
            $startInstaller = false;
        }

        // Start the installation when config file not found
        $this->checkConfigFileExists($this->settingsProvider->getPathLocal(), $startInstaller);
    }

    /**
     * @param $path
     * @param bool $startInstaller
     * @throws \Exception
     */
    private function checkConfigFileExists($path, $startInstaller = false)
    {
        if (is_readable($path) && !$startInstaller) {
            return;
        }

        $general = $this->settingsProvider->getSection('General');

        if (
            isset($general['installation_in_progress'])
            && $general['installation_in_progress']
            && $startInstaller
        ) {
            return;
        }

        if (isset($general['enable_installer']) && !$general['enable_installer']) {
            throw new NotYetInstalledException('Matomo is not set up yet');
        }

        $message = $this->getSpecificMessageWhetherFileExistsOrNot($path);

        $exception = new NotYetInstalledException($message);

        if ($startInstaller) {
            $this->startInstallation($exception);
        } else {
            throw $exception;
        }
    }

    /**
     * @param $exception
     */
    private function startInstallation($exception)
    {
        /**
         * Triggered when the configuration file cannot be found or read, which usually
         * means Piwik is not installed yet.
         *
         * This event can be used to start the installation process or to display a custom error message.
         *
         * @param \Exception $exception The exception that was thrown by `Config::getInstance()`.
         */
        Piwik::postEvent('Config.NoConfigurationFile', array($exception), $pending = true);
    }

    /**
     * @param $path
     * @return string
     */
    private function getMessageWhenFileExistsButNotReadable($path)
    {
        $format = " \n<b>» %s </b>";
        if (Common::isPhpCliMode()) {
            $format = "\n » %s \n";
        }

        return sprintf(
            $format,
            $this->translator->translate(
                'General_ExceptionConfigurationFilePleaseCheckReadableByUser',
                array($path, Filechecks::getUser())
            )
        );
    }

    /**
     * @param $path
     * @return string
     */
    private function getSpecificMessageWhetherFileExistsOrNot($path)
    {
        if (!file_exists($path)) {
            $message = $this->translator->translate('General_ExceptionConfigurationFileNotFound', array($path));
            if (Common::isPhpCliMode()) {
                $message .= $this->getMessageWhenFileExistsButNotReadable($path);
            }
        } else {
            $message = $this->translator->translate(
                'General_ExceptionConfigurationFileExistsButNotReadable',
                array($path)
            );
            $message .= $this->getMessageWhenFileExistsButNotReadable($path);
        }

        if (Common::isPhpCliMode()) {
            $message = "\n" . $message;
        }
        return $message;
    }
}
