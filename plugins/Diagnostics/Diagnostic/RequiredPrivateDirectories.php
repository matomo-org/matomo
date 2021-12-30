<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Diagnostics\Diagnostic;

use Piwik\Common;
use Piwik\SettingsPiwik;

/**
 * Checks whether certain directories in Matomo that should be private are accessible through the internet.
 */
class RequiredPrivateDirectories extends AbstractPrivateDirectories
{
    private $configIniAccessible = false;
    protected $privatePaths = [
        'tmp/cache/tracker/matomocache_general.php',
        '.git',
        '.git/config',
    ];

    protected function addError(DiagnosticResult &$result)
    {
        $pathIsAccessible = $this->translator->translate('Diagnostics_PrivateDirectoryIsAccessible');
        if ($this->configIniAccessible) {
            $pathIsAccessible .= '<br/><br/>' . $this->translator->translate('Diagnostics_ConfigIniAccessible');
        }
        $pathIsAccessible .= '<br/><br/><a href="https://matomo.org/faq/troubleshooting/how-do-i-fix-the-error-private-directories-are-accessible/" target="_blank" rel="noopener noreferrer">' . $this->translator->translate('General_ReadThisToLearnMore', ['', '']) . '</a>';
        $result->setLongErrorMessage($pathIsAccessible);
    }

    protected function computeAccessiblePaths(DiagnosticResult &$result, $baseUrl, array $testUrls): bool
    {
        $this->configIniAccessible = $this->isAccessible($result, $baseUrl . 'config/config.ini.php', ';', 'trusted_hosts[]');
        $atLeastOneIsAccessible = parent::computeAccessiblePaths($result, $baseUrl, $testUrls);
        return $this->configIniAccessible || $atLeastOneIsAccessible;
    }

    public function isGlobalConfigIniAccessible()
    {
        $baseUrl = SettingsPiwik::getPiwikUrl();
        if (!Common::stringEndsWith($baseUrl, '/')) {
            $baseUrl .= '/';
        }
        return $this->isAccessible(new DiagnosticResult(''), $baseUrl . 'config/global.ini.php', ';', 'trusted_hosts[]');
    }

}
