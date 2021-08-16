<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Diagnostics\Diagnostic;

use Piwik\Common;
use Piwik\Container\StaticContainer;
use Piwik\Filesystem;
use Piwik\Http;
use Piwik\Piwik;
use Piwik\Plugins\Diagnostics\Diagnostic\Diagnostic;
use Piwik\Plugins\Diagnostics\Diagnostic\DiagnosticResult;
use Piwik\SettingsPiwik;
use Piwik\Translation\Translator;

class RecommendedPrivateDirectoriesCheck implements Diagnostic
{

    public function execute()
    {
        if (!SettingsPiwik::isMatomoInstalled()) {
            return [];
        }
        $tmpPath = StaticContainer::get('path.tmp');
        $privatePaths = [];
        // create test file to check if tmp/empty exists Note: This won't work in a load balanced environment!
        Filesystem::mkdir($tmpPath);
        file_put_contents($tmpPath . '/empty', 'test'); // do we want to move this line to THISLINE?
        if (false !== strpos($tmpPath, PIWIK_INCLUDE_PATH)) {
            // THISLINE
            $privatePaths[] = ["tmp/empty"]; // tmp/empty is created by this diagnostic
        }
        $privatePaths[] = ['lang/en.json'];

        $baseUrl = SettingsPiwik::getPiwikUrl();
        if (!Common::stringEndsWith($baseUrl, '/')) {
            $baseUrl .= '/';
        }

        $label = Piwik::translate('Diagnostics_RecommendedPrivateDirectories');
        $manualCheck = Piwik::translate('Diagnostics_PrivateDirectoryManualCheck');

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

            $unknown = Piwik::translate('Diagnostics_PrivateDirectoryInternetDisabled') . ' ' . $manualCheck
                . $testUrlsList;
            $results[] = DiagnosticResult::singleResult($label, DiagnosticResult::STATUS_INFORMATIONAL, $unknown);
            return $results;
        }

        $comment = Piwik::translate('Diagnostics_RecommendPrivateFiles') . ' ' .
            Piwik::translate('General_ReadThisToLearnMore', [
                '<a href="https://matomo.org/faq/troubleshooting/how-do-i-fix-the-error-private-directories-are-accessible/" target="_blank" rel="noopener noreferrer">',
                '</a>',
            ]);
        $result = DiagnosticResult::informationalResult($label, $comment, false);

        $atLeastOneIsAccessible = false;
        foreach ($testUrls as $testUrl) {
            if ($this->isAccessible($result, $testUrl, '', '')) {
                $atLeastOneIsAccessible = true;
            }
        }

        if (!$atLeastOneIsAccessible) {
            $result->addItem(new DiagnosticResultItem(DiagnosticResult::STATUS_OK, Piwik::translate('Diagnostics_AllPrivateDirectoriesAreInaccessible')));
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

    /**
     * copied from RequiredPrivateDirectoriesCheck for now
     * TODO: refactor?
     * @param \Piwik\Plugins\Diagnostics\Diagnostic\DiagnosticResult $result
     * @param $testUrl
     * @param $publicIfResponseEquals
     * @param $publicIfResponseContains
     * @return bool
     */
    private function isAccessible(DiagnosticResult $result, $testUrl, $publicIfResponseEquals, $publicIfResponseContains)
    {
        try {
            $response = Http::sendHttpRequest($testUrl, $timeout = 2, null, null, null, false, false, true);
            $status = $response['status'];

            if ($status >= 400 && $status < 500) {
                return false;
            } elseif ($status >= 300 && $status < 400) {
                // follow the redirect
                $response = Http::sendHttpRequest($testUrl, $timeout = 5, null, null, 5, false, false, true);
                $isResolvedRedirectProtected = $response['status'] >= 400 &&  $response['status'] < 500;
                if ($isResolvedRedirectProtected) {
                    // eg someone redirect from http to https or the other way around
                    return false;
                }

                // we check for content if possible as they may redirect these files eg to /home or something else
                if (!$publicIfResponseContains || !$publicIfResponseEquals) {
                    // it may or may not be an issue depending where they redirect to
                    // TODO ideally we make this more clear maybe?
                    $result->addItem(new DiagnosticResultItem(DiagnosticResult::STATUS_INFORMATIONAL, $testUrl));
                    return true;
                }

                if (trim($response['data']) === $publicIfResponseEquals) {
                    // we assume it is publicly accessible because either the exact expected content is returned or because we don't check for content match
                    $result->addItem(new DiagnosticResultItem(DiagnosticResult::STATUS_WARNING, $testUrl));
                    return true;
                } elseif (strpos($response['data'], $publicIfResponseContains) !== false) {
                    // we assume it is publicly accessible because a unique content is included in the response or because we don't check for content contains
                    $result->addItem(new DiagnosticResultItem(DiagnosticResult::STATUS_WARNING, $testUrl));
                    return true;
                }
                // in other cases we assume it's not publicly accessible because we didn't get any expected output in the response
                // so it seems like they redirect eg to the homepage or another page

            } else {
                // we assume the file is accessible publicly
                $result->addItem(new DiagnosticResultItem(DiagnosticResult::STATUS_WARNING, $testUrl));
                return true;
            }
        } catch (\Exception $e) {
            $error = $e->getMessage();
            $result->addItem(new DiagnosticResultItem(DiagnosticResult::STATUS_INFORMATIONAL, 'Unable to execute check for '
                . Common::sanitizeInputValue($testUrl) . ': ' . Common::sanitizeInputValue($error)));
        }
        return false;
    }
}
