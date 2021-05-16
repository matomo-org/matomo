<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Diagnostics\Diagnostic;

use Piwik\Common;
use Piwik\Filesystem;
use Piwik\Http;
use Piwik\SettingsPiwik;
use Piwik\Translation\Translator;

/**
 * Checks whether certain directories in Matomo that should be private are accessible through the internet.
 */
class RequiredPrivateDirectories implements Diagnostic
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
        if (!SettingsPiwik::isMatomoInstalled()) {
            return [];
        }

        $label = $this->translator->translate('Diagnostics_RequiredPrivateDirectories');

        $privatePaths = [
            ['tmp/', 'tmp/empty', 'tmp/cache/tracker/matomocache_general.php'], // tmp/empty is created by this diagnostic
            ['.git/', '.git/config'],
            ['lang/en.json'],
        ];

        // create test file to check if tmp/empty exists
        Filesystem::mkdir(PIWIK_INCLUDE_PATH . '/tmp');
        file_put_contents(PIWIK_INCLUDE_PATH . '/tmp/empty', 'test');

        $baseUrl = SettingsPiwik::getPiwikUrl();
        if (!Common::stringEndsWith($baseUrl, '/')) {
            $baseUrl .= '/';
        }

        $manualCheck = $this->translator->translate('Diagnostics_PrivateDirectoryManualCheck');

        $testUrls = [];
        foreach ($privatePaths as $checks) {
            foreach ($checks as $path) {
                if (!file_exists($path)) {
                    continue;
                }

                $testUrls[] = $baseUrl . $path;
            }
        }

        $isInternetEnabled = SettingsPiwik::isInternetEnabled();
        if (!$isInternetEnabled) {
            $testUrlsList = $this->getUrlList($testUrls);

            $unknown = $this->translator->translate('Diagnostics_PrivateDirectoryInternetDisabled') . ' ' . $manualCheck
                . $testUrlsList;
            $results[] = DiagnosticResult::singleResult($label, DiagnosticResult::STATUS_WARNING, $unknown);
            return $results;
        }

        $result = new DiagnosticResult($label);

        $isConfigIniAccessible = $this->checkConfigIni($result, $baseUrl);

        $atLeastOneIsAccessible = $isConfigIniAccessible;
        foreach ($testUrls as $testUrl) {
            try {
                $response = Http::sendHttpRequest($testUrl, $timeout = 2, null, null, null, false, false, true);
                $status = $response['status'];

                $isAccessible = !($status >= 400 && $status < 500);
                if ($isAccessible) {
                    $result->addItem(new DiagnosticResultItem(DiagnosticResult::STATUS_ERROR, $testUrl));
                    $atLeastOneIsAccessible = true;
                }
            } catch (\Exception $e) {
                $error = $e->getMessage();
                $result->addItem(new DiagnosticResultItem(DiagnosticResult::STATUS_WARNING, 'Unable to execute check for ' .
                    Common::sanitizeInputValue($testUrl) . ': ' . Common::sanitizeInputValue($error)));
            }
        }

        if ($atLeastOneIsAccessible) {
            $pathIsAccessible = $this->translator->translate('Diagnostics_PrivateDirectoryIsAccessible');
            if ($isConfigIniAccessible) {
                $pathIsAccessible .= '<br/><br/>' . $this->translator->translate('Diagnostics_ConfigIniAccessible');
            }
            $result->setLongErrorMessage($pathIsAccessible);
        } else {
            $result->addItem(new DiagnosticResultItem(DiagnosticResult::STATUS_OK, $this->translator->translate('Diagnostics_AllPrivateDirectoriesAreInaccessible')));
        }

        return [$result];
    }

    private function getUrlList(array $testUrls)
    {
        $testUrlsList = '';
        foreach ($testUrls as $testUrl) {
            $testUrlsList .= '<br/>' . Common::sanitizeInputValue($testUrl);
        }
        return $testUrlsList;
    }

    private function checkConfigIni(DiagnosticResult $result, $baseUrl)
    {
        $testUrl = $baseUrl . 'config/config.ini.php';
        try {
            $response = Http::sendHttpRequest($testUrl, $timeout = 2, null, null, null, false, false, true);
            $status = $response['status'];

            $isAccessible = !($status >= 400 && $status < 500);
            if ($isAccessible) {
                $result->addItem(new DiagnosticResultItem(DiagnosticResult::STATUS_ERROR, $testUrl));
                return true;
            }
        } catch (\Exception $e) {
            $error = $e->getMessage();
            $result->addItem(new DiagnosticResultItem(DiagnosticResult::STATUS_WARNING, 'Unable to execute check for '
                . Common::sanitizeInputValue($testUrl) . ': ' . Common::sanitizeInputValue($error)));
        }
        return false;
    }
}
