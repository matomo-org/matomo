<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Diagnostics\Diagnostic;

use Piwik\Db;
use Piwik\Piwik;
use Piwik\SettingsPiwik;
use Piwik\Translation\Translator;

/**
 * Check if Piwik can use LOAD DATA INFILE.
 */
class DbReaderCheck implements Diagnostic
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
            // Skip the diagnostic if Matomo is being installed
            return [];
        }

        if (!Db::hasReaderConfigured()) {
            // only show an entry when reader is actually configured
            return [];
        }

        $label = $this->translator->translate('Diagnostics_DatabaseReaderConnection');

        try {
            Db::getReader();
            return array(DiagnosticResult::singleResult($label, DiagnosticResult::STATUS_OK, ''));
        } catch (\Exception $e) {
        }

        $comment = Piwik::translate('Installation_CannotConnectToDb');
        return array(DiagnosticResult::singleResult($label, DiagnosticResult::STATUS_WARNING, $comment));
    }
}
