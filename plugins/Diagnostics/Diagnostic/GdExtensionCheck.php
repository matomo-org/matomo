<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\Diagnostics\Diagnostic;

use Piwik\SettingsServer;
use Piwik\Translation\Translator;

/**
 * Check that the GD extension is installed and the correct version.
 */
class GdExtensionCheck implements Diagnostic
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
        $label = $this->translator->translate('Installation_SystemCheckGDFreeType');

        if (SettingsServer::isGdExtensionEnabled()) {
            return array(DiagnosticResult::singleResult($label, DiagnosticResult::STATUS_OK));
        }

        $comment = sprintf(
            '%s<br />%s',
            $this->translator->translate('Installation_SystemCheckGDFreeType'),
            $this->translator->translate('Installation_SystemCheckGDHelp')
        );

        return array(DiagnosticResult::singleResult($label, DiagnosticResult::STATUS_WARNING, $comment));
    }
}
