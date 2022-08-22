<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\Diagnostics\Diagnostic;

use Piwik\Translation\Translator;
use Piwik\Config\GeneralConfig;
use Piwik\SettingsPiwik;

/**
 * Information about the server.
 */
class ServerInformational implements Diagnostic
{
    /**
     * @var Translator
     */
    private $translator;

    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    public function execute()
    {
        $results = [];

        if (!empty($_SERVER['SERVER_SOFTWARE'])) {

            $isGlobalConfigIniAccessible = true; // Assume true if not installed yet
            
            // Only attempt to check file accessibility if the config setting allows it
            $disableFileAccessCheck = (GeneralConfig::getConfigValue('enable_required_directories_diagnostic') == 0);

            if(!$disableFileAccessCheck) {
                if (SettingsPiwik::isMatomoInstalled()) {
                    $rpd = new RequiredPrivateDirectories($this->translator);
                    $isGlobalConfigIniAccessible = $rpd->isGlobalConfigIniAccessible();
                }
            }

            if (strpos(strtolower($_SERVER['SERVER_SOFTWARE']), 'nginx') !== false && $isGlobalConfigIniAccessible && !$disableFileAccessCheck) {

                $comment = $_SERVER['SERVER_SOFTWARE']."<br><br>";
                $comment .= $this->translator->translate('Diagnostics_HtaccessWarningNginx', [
                        '<a href="https://github.com/matomo-org/matomo-nginx#readme" target="_blank">', '</a>']);

                $results[] = DiagnosticResult::singleResult('Server Info', DiagnosticResult::STATUS_WARNING, $comment);

            } else {
                $results[] = DiagnosticResult::informationalResult('Server Info', $_SERVER['SERVER_SOFTWARE']);
            }
        }

        return $results;
    }

}
