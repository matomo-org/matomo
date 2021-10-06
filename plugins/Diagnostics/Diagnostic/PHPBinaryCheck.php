<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\Diagnostics\Diagnostic;

use Piwik\Translation\Translator;

/**
 * Check the PHP Binary is set to 64 bit
 */
class PHPBinaryCheck implements Diagnostic
{
    /**
     * @var Translator
     */
    private $translator;

    /**
     * @var int
     */

    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    public function execute()
    {
        $label = $this->translator->translate('Installation_PhpBinaryCheck');

        if (PHP_INT_SIZE === 8) {
            $status = DiagnosticResult::STATUS_OK;
            $comment = "";
        } else {
            $status = DiagnosticResult::STATUS_WARNING;
            $comment = $this->translator->translate('Installation_PhpBinaryCheckHelp');
        }

        return array(DiagnosticResult::singleResult($label, $status, $comment));
    }
}
