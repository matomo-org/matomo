<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Diagnostics\Diagnostic;

use Piwik\Common;
use Piwik\Config;
use Piwik\Http;
use Piwik\SettingsPiwik;
use Piwik\Translation\Translator;

abstract class AbstractPrivateDirectories implements Diagnostic
{
    protected $privatePaths = [];
    protected $accessiblePaths = []; // used like a set, but hashtable used underneath anyway, so map simpler php way

    protected $labelKey = 'Diagnostics_RequiredPrivateDirectories';

    /**
     * @var Translator
     */
    protected $translator;

    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    public function execute()
    {
        if (!SettingsPiwik::isMatomoInstalled()) {
            return [];
        }

        $label = $this->translator->translate($this->labelKey);

        $baseUrl = SettingsPiwik::getPiwikUrl();
        if (!Common::stringEndsWith($baseUrl, '/')) {
            $baseUrl .= '/';
        }

        $manualCheck = $this->translator->translate('Diagnostics_PrivateDirectoryManualCheck');

        $testUrls = [];
        foreach ($this->privatePaths as $path) {
            if (!file_exists($path)) {
                continue;
            }
            $testUrls[$path] = $baseUrl . $path;
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
        if (Config::getInstance()->General['enable_required_directories_diagnostic'] == 0) {
            $result->addItem(
                new DiagnosticResultItem(
                    DiagnosticResult::STATUS_WARNING,
                    $this->translator->translate('Diagnostics_EnableRequiredDirectoriesDiagnostic')
                )
            );
            return [$result];
        }

        $atLeastOneIsAccessible = $this->computeAccessiblePaths($result, $baseUrl, $testUrls);

        if ($atLeastOneIsAccessible) {
            $this->addError($result);
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

    protected function isAccessible(DiagnosticResult $result, $testUrl, $publicIfResponseEquals, $publicIfResponseContains)
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

    protected function computeAccessiblePaths(DiagnosticResult &$result, $baseUrl, array $testUrls): bool
    {
        $atLeastOneIsAccessible = false;
        foreach ($testUrls as $path => $testUrl) {
            if ($this->isAccessible($result, $testUrl, '', '')) {
                $atLeastOneIsAccessible = true;
            }
        }
        return $atLeastOneIsAccessible;
    }

    protected abstract function addError(DiagnosticResult &$result);
}
