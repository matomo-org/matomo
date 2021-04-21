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
use Piwik\Piwik;
use Piwik\Plugins\HeatmapSessionRecording\HeatmapSessionRecording;
use Piwik\SettingsPiwik;
use Piwik\Translation\Translator;

/**
 * TODO: describe
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
        // TODO: translations
        $label = $this->translator->translate('Diagnostics_RequiredPrivateDirectories');

        // TODO: test manually for configurations that allow these w/ nginx. post nginx config used to reproduce.
        $privatePaths = [
            'tmp/',
            'tmp/empty', // created by this diagnostic
            '.git/',
            '.git/config',
        ];

        // create test file to check if tmp/empty exists
        Filesystem::mkdir(PIWIK_INCLUDE_PATH . '/tmp');
        file_put_contents(PIWIK_INCLUDE_PATH . '/tmp/', 'test');

        $baseUrl = SettingsPiwik::getPiwikUrl();
        if (!Common::stringEndsWith($baseUrl, '/')) {
            $baseUrl .= '/';
        }

        $errorResult = $this->translator->translate('Diagnostics_ConfigsPhpErrorResult');
        $manualCheck = $this->translator->translate('Diagnostics_ConfigsPhpManualCheck');

        $isInternetEnabled = SettingsPiwik::isInternetEnabled();
        foreach ($privatePaths as $path) {
            if (!file_exists($path)) {
                continue;
            }

            $testUrl = $baseUrl . $path;

            if (!$isInternetEnabled) {
                $unknown = $this->translator->translate('Diagnostics_ConfigsInternetDisabled', $testUrl) . ' ' . $manualCheck;
                return array(DiagnosticResult::singleResult($label, DiagnosticResult::STATUS_WARNING, $unknown));
            }

            try {
                $response = Http::sendHttpRequest($testUrl, $timeout = 2, null, null, 0, false, false, true);
                $status = $response['status'];
                if ($status >= 400 && $status < 500) {
                    // TODO
                }

                // TODO: check response
            } catch (\Exception $e) {
                $error = $e->getMessage();
            }
        }
        // TODO: Implement execute() method.
    }
}