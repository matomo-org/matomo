<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\Diagnostics\Diagnostic;

use Piwik\CliMulti\CliPhp;
use Piwik\Config\GeneralConfig;
use Piwik\Date;
use Piwik\SettingsPiwik;
use Piwik\Translation\Translator;

/**
 * Information about PHP.
 */
class PhpInformational implements Diagnostic
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

        if (defined('PHP_OS') && PHP_OS) {
            $results[] = DiagnosticResult::informationalResult('PHP_OS', PHP_OS);
        }
        if (SettingsPiwik::isMatomoInstalled() && defined('PHP_BINARY') && PHP_BINARY) {
            $results[] = DiagnosticResult::informationalResult('PHP_BINARY', PHP_BINARY);
        }

        $isGlobalConfigIniAccessible = true; // Assume true if not installed yet
        // Only attempt to check file accessibility if the config setting allows it
        $disableFileAccessCheck = (GeneralConfig::getConfigValue('enable_required_directories_diagnostic') == 0);
        if(!$disableFileAccessCheck) {
            if (SettingsPiwik::isMatomoInstalled()) {
                $rpd = new RequiredPrivateDirectories($this->translator);
                $isGlobalConfigIniAccessible = $rpd->isGlobalConfigIniAccessible();
            }
        }

        if (strpos(strtolower(php_sapi_name()), 'fpm-fcgi') !== false && $isGlobalConfigIniAccessible && !$disableFileAccessCheck) {

            // Using PHP-FPM and private files are accessible
            $comment = php_sapi_name()."<br><br>";

            if (!empty($_SERVER['SERVER_SOFTWARE'])) {
                if (strpos(strtolower($_SERVER['SERVER_SOFTWARE']), 'apache') !== false) {
                    $comment .= $this->translator->translate('Diagnostics_PHPFPMWarningApache', [
                        '<code>ProxyPass /config !</code>', '<code>mod_proxy_fcgi.c</code>', '<code>ProxyPassMatch</code>']);
                } else if (strpos(strtolower($_SERVER['SERVER_SOFTWARE']), 'nginx') !== false) {
                    $comment .= $this->translator->translate('Diagnostics_PHPFPMWarningNginx', [
                        '<a href="https://github.com/matomo-org/matomo-nginx#readme" target="_blank">', '</a>']);
                } else {
                    $comment .= $this->translator->translate('Diagnostics_PHPFPMWarningGeneric');
                }
            } else {
                $comment .= $this->translator->translate('Diagnostics_PHPFPMWarningGeneric');
            }
            $results[] = DiagnosticResult::singleResult('PHP SAPI', DiagnosticResult::STATUS_WARNING, $comment);
        } else {
            $results[] = DiagnosticResult::informationalResult('PHP SAPI', php_sapi_name());
        }

        if (SettingsPiwik::isMatomoInstalled()) {
            $cliPhp = new CliPhp();
            $binary = $cliPhp->findPhpBinary();
            if (!empty($binary)) {
                $binary = basename($binary);
                $rows[] = array(
                    'name' => 'PHP Found Binary',
                    'value' => $binary,
                );
            }
            $results[] = DiagnosticResult::informationalResult('Timezone Version', timezone_version_get());
        }
        $results[] = DiagnosticResult::informationalResult('PHP Timezone', date_default_timezone_get());
        $results[] = DiagnosticResult::informationalResult('PHP Time', time());
        $results[] = DiagnosticResult::informationalResult('PHP Datetime', Date::now()->getDatetime());

        $disabled_functions = ini_get('disable_functions');
        $disabled_functions = implode(", ", explode(",", $disabled_functions));
        if (!empty($disabled_functions)) {
            $results[] = DiagnosticResult::informationalResult('PHP Disabled functions', $disabled_functions);
        }

        foreach (['max_execution_time', 'post_max_size', 'max_input_vars', 'zlib.output_compression'] as $iniSetting) {
            $results[] = DiagnosticResult::informationalResult('PHP INI ' . $iniSetting, @ini_get($iniSetting));
        }

        if (function_exists('curl_version')) {
            $curl_version = curl_version();
            $curl_version = $curl_version['version'] . ', ' . $curl_version['ssl_version'];
            $results[] = DiagnosticResult::informationalResult('Curl Version', $curl_version);
        }
        $suhosin_installed = ( extension_loaded( 'suhosin' ) || ( defined( 'SUHOSIN_PATCH' ) && constant( 'SUHOSIN_PATCH' ) ) );

        $results[] = DiagnosticResult::informationalResult('Suhosin Installed', $suhosin_installed);

        return $results;
    }

}
