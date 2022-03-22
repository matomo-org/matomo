<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\Diagnostics\Diagnostic;

use Piwik\Filechecks;
use Piwik\Http;
use Piwik\Translation\Translator;

/**
 * Check that Piwik's HTTP client can work correctly.
 */
class HttpClientCheck implements Diagnostic
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
        $label = $this->translator->translate('Installation_SystemCheckOpenURL');

        $httpMethod = Http::getTransportMethod();

        if ($httpMethod) {
            return array(DiagnosticResult::singleResult($label, DiagnosticResult::STATUS_OK, $httpMethod));
        }

        $canAutoUpdate = Filechecks::canAutoUpdate();

        $comment = $this->translator->translate('Installation_SystemCheckOpenURLHelp');

        if (! $canAutoUpdate) {
            $comment .= '<br/>' . $this->translator->translate('Installation_SystemCheckAutoUpdateHelp');
        }

        return array(DiagnosticResult::singleResult($label, DiagnosticResult::STATUS_WARNING, $comment));
    }
}
