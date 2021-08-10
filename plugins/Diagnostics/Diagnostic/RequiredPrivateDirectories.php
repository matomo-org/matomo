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
use Piwik\Plugins\Installation\ServerFilesGenerator;
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

        // create test file to check if tmp/empty exists Note: This won't work in a load balanced environment!
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

        $isConfigIniAccessible = $this->isAccessible($result, $baseUrl . 'config/config.ini.php', ';', 'trusted_hosts[]');

        $atLeastOneIsAccessible = $isConfigIniAccessible;
        foreach ($testUrls as $testUrl) {
            if ($this->isAccessible($result, $testUrl, '', '')) {
                $atLeastOneIsAccessible = true;
            }
        }

        if ($atLeastOneIsAccessible) {
            $pathIsAccessible = $this->translator->translate('Diagnostics_PrivateDirectoryIsAccessible');
            if ($isConfigIniAccessible) {
                $pathIsAccessible .= '<br/><br/>' . $this->translator->translate('Diagnostics_ConfigIniAccessible');
            }
            $pathIsAccessible .= '<br/><br/><a href="https://matomo.org/faq/troubleshooting/how-do-i-fix-the-error-private-directories-are-accessible/" target="_blank" rel="noopener noreferrer">' . $this->translator->translate('General_ReadThisToLearnMore', ['', '']) . '</a>';
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
                    $result->addItem(new DiagnosticResultItem(DiagnosticResult::STATUS_WARNING, $testUrl));
                    return true;
                }

                if (trim($response['data']) === $publicIfResponseEquals) {
                    // we assume it is publicly accessible because either the exact expected content is returned or because we don't check for content match
                    $result->addItem(new DiagnosticResultItem(DiagnosticResult::STATUS_ERROR, $testUrl));
                    return true;
                } elseif (strpos($response['data'], $publicIfResponseContains) !== false) {
                    // we assume it is publicly accessible because a unique content is included in the response or because we don't check for content contains
                    $result->addItem(new DiagnosticResultItem(DiagnosticResult::STATUS_ERROR, $testUrl));
                    return true;
                }
                // in other cases we assume it's not publicly accessible because we didn't get any expected output in the response
                // so it seems like they redirect eg to the homepage or another page

            } else {
                // we assume the file is accessible publicly
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
