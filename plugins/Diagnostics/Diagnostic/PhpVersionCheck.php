<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\Diagnostics\Diagnostic;

use Piwik\Translation\Translator;

/**
 * Check the PHP version.
 */
class PhpVersionCheck implements Diagnostic
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
        global $piwik_minimumPHPVersion;

        $actualVersion = PHP_VERSION;

        $label = sprintf(
            '%s >= %s',
            $this->translator->translate('Installation_SystemCheckPhp'),
            $piwik_minimumPHPVersion
        );

        if ($this->isPhpVersionValid($piwik_minimumPHPVersion, $actualVersion)) {
            $status = DiagnosticResult::STATUS_OK;
            $comment = $actualVersion;
        } else {
            $status = DiagnosticResult::STATUS_ERROR;
            $comment = sprintf(
                '%s: %s',
                $this->translator->translate('General_Error'),
                $this->translator->translate('General_Required', array($label))
            );
        }

        return array(DiagnosticResult::singleResult($label, $status, $comment));
    }

    private function isPhpVersionValid($requiredVersion, $actualVersion)
    {
        return version_compare($requiredVersion, $actualVersion) <= 0;
    }
}
