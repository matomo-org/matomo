<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Diagnostics\Diagnostic;

use Piwik\Config;
use Piwik\Config\ConfigFileWriter;
use Piwik\Translation\Translator;

/**
 * Reports whether the config file can be replaced atomically.
 *
 * Reasons the operator can fix are warnings; the rest are informational.
 */
class ConfigWriteModeCheck implements Diagnostic
{
    /**
     * @var Translator
     */
    private $translator;

    /** Reasons the operator can act on; everything else is informational. */
    private const ACTIONABLE = [
        ConfigFileWriter::BLOCKED_DIR_NOT_WRITABLE,
        ConfigFileWriter::BLOCKED_FILE_NOT_WRITABLE,
        ConfigFileWriter::BLOCKED_OWNER_MISMATCH,
    ];

    /**
     * @var bool
     */
    private $atomicWriteEnabled;

    public function __construct(Translator $translator, bool $atomicWriteEnabled)
    {
        $this->translator = $translator;
        $this->atomicWriteEnabled = $atomicWriteEnabled;
    }

    public function execute()
    {
        $label = $this->translator->translate('Diagnostics_ConfigWriteMode');
        $path = Config::getInstance()->getLocalPath();

        $blocker = ConfigFileWriter::getLastBlocker() ?: ConfigFileWriter::inspect(
            $path,
            $this->isAtomicWriteEnabled()
        );

        if ($blocker === null) {
            // Rendering this page saves no config, so the pre-check is the answer. Its
            // wording is scoped to that: it cannot see a rename that would be refused.
            return array(DiagnosticResult::singleResult(
                $label,
                DiagnosticResult::STATUS_OK,
                $this->translator->translate('Diagnostics_ConfigWriteModeAtomic')
            ));
        }

        $status = in_array($blocker, self::ACTIONABLE, true)
            ? DiagnosticResult::STATUS_WARNING
            : DiagnosticResult::STATUS_INFORMATIONAL;

        $summary = $this->translator->translate(
            $blocker === ConfigFileWriter::BLOCKED_FILE_NOT_WRITABLE
                ? 'Diagnostics_ConfigWriteModeNotWritable'
                : 'Diagnostics_ConfigWriteModeInPlace'
        );

        $explanation = $this->getExplanation($blocker, $path);

        // setLongErrorMessage() renders in the error styling whatever the status, so
        // only warnings use it; the rest carry their reason in the comment.
        if ($status !== DiagnosticResult::STATUS_WARNING) {
            return array(DiagnosticResult::singleResult($label, $status, $summary . ' ' . $explanation));
        }

        $result = DiagnosticResult::singleResult($label, $status, $summary);

        $result->setLongErrorMessage($explanation);

        return array($result);
    }

    private function getExplanation(string $blocker, string $path): string
    {
        $dir = dirname($path);

        switch ($blocker) {
            case ConfigFileWriter::BLOCKED_DISABLED:
                return $this->translator->translate('Diagnostics_ConfigWriteModeDisabled');

            case ConfigFileWriter::BLOCKED_DIR_NOT_WRITABLE:
                return $this->translator->translate('Diagnostics_ConfigWriteModeDirNotWritable', $dir)
                    . sprintf(' <pre>chmod u+w %s</pre>', htmlspecialchars($dir, ENT_QUOTES, 'UTF-8'));

            case ConfigFileWriter::BLOCKED_FILE_NOT_WRITABLE:
                return $this->translator->translate('Diagnostics_ConfigWriteModeFileNotWritable', $path);

            case ConfigFileWriter::BLOCKED_OWNER_MISMATCH:
                // No account name: the system report does not anonymise this field.
                return $this->translator->translate('Diagnostics_ConfigWriteModeOwnerMismatch', $path);

            case ConfigFileWriter::BLOCKED_HARD_LINKED:
                return $this->translator->translate('Diagnostics_ConfigWriteModeHardLinked', $path);

            case ConfigFileWriter::BLOCKED_MISSING_FUNCTION:
                return $this->translator->translate('Diagnostics_ConfigWriteModeMissingFunction');

            case ConfigFileWriter::BLOCKED_UNRESOLVABLE:
                return $this->translator->translate('Diagnostics_ConfigWriteModeUnresolvable', $path);

            default:
                // BLOCKED_REPLACE_FAILED: bind mount, immutable attribute, ACL, or a
                // Windows target held open by a reader.
                return $this->translator->translate('Diagnostics_ConfigWriteModeUnsupported', $path);
        }
    }

    private function isAtomicWriteEnabled(): bool
    {
        return $this->atomicWriteEnabled;
    }
}
