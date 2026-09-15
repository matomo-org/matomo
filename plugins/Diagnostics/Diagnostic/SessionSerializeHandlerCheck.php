<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Diagnostics\Diagnostic;

use Piwik\Common;
use Piwik\SettingsPiwik;
use Piwik\Translation\Translator;

/**
 * Check that sessions are stored in a format Matomo can read back.
 */
class SessionSerializeHandlerCheck implements Diagnostic
{
    private Translator $translator;

    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    public function execute()
    {
        // Matomo picks the format when it starts a session, which it only does once it is
        // installed and never on the command line. Until then the setting says nothing about
        // how sessions actually end up being stored.
        if (!SettingsPiwik::isMatomoInstalled() || Common::isPhpCliMode()) {
            return array();
        }

        $label = $this->translator->translate('Diagnostics_SessionStorageFormat');
        $handler = (string) @ini_get('session.serialize_handler');

        if ($handler === 'php_serialize') {
            return array(DiagnosticResult::singleResult($label, DiagnosticResult::STATUS_OK));
        }

        $comment = $this->translator->translate('Diagnostics_SessionStorageFormatWarning', array($handler));

        return array(DiagnosticResult::singleResult($label, DiagnosticResult::STATUS_WARNING, $comment));
    }
}
