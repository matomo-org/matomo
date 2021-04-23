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
        $label = $this->translator->translate('Diagnostics_RequiredPrivateDirectories');

        $privatePaths = [
            ['tmp/', 'tmp/empty'], // created by this diagnostic
            ['.git/', '.git/config'],
        ];

        // create test file to check if tmp/empty exists
        Filesystem::mkdir(PIWIK_INCLUDE_PATH . '/tmp');
        file_put_contents(PIWIK_INCLUDE_PATH . '/tmp/empty', 'test');

        $baseUrl = SettingsPiwik::getPiwikUrl();
        if (!Common::stringEndsWith($baseUrl, '/')) {
            $baseUrl .= '/';
        }

        $manualCheck = $this->translator->translate('Diagnostics_PrivateDirectoryManualCheck');

        $results = [];

        $isInternetEnabled = SettingsPiwik::isInternetEnabled();
        foreach ($privatePaths as $checks) {
            foreach ($checks as $path) {
                if (!file_exists($path)) {
                    continue;
                }

                $testUrl = $baseUrl . $path;

                if (!$isInternetEnabled) {
                    $unknown = $this->translator->translate('Diagnostics_PrivateDirectoryInternetDisabled', $testUrl) . ' ' . $manualCheck;
                    $results[] = DiagnosticResult::singleResult($label, DiagnosticResult::STATUS_WARNING, $unknown);
                    continue;
                }

                try {
                    $response = Http::sendHttpRequest($testUrl, $timeout = 2, null, null, null, false, false, true);
                    $status = $response['status'];

                    $isAccessible = !($status >= 400 && $status < 500);
                    if ($isAccessible) {
                        $pathIsAccessible = $this->translator->translate('Diagnostics_PrivateDirectoryIsAccessible', $testUrl);
                        $results[] = DiagnosticResult::singleResult($label, DiagnosticResult::STATUS_ERROR, $pathIsAccessible);

                        break; // skip rest of checks in this batch to provide cleaner output in the UI
                    }
                } catch (\Exception $e) {
                    $error = $e->getMessage();
                    $results[] = DiagnosticResult::singleResult($label, DiagnosticResult::STATUS_WARNING, 'Unable to execute check: ' . $error);
                }
            }
        }

        if (empty($results)) {
            $results[] = DiagnosticResult::singleResult($label, DiagnosticResult::STATUS_OK);
        }

        return $results;
    }
}
