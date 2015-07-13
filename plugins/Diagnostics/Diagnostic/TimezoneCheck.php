<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\Diagnostics\Diagnostic;

use Piwik\Config;
use Piwik\SettingsServer;
use Piwik\Translation\Translator;

/**
 * Check that the PHP timezone setting is set.
 */
class TimezoneCheck implements Diagnostic
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
        $label = $this->translator->translate('SitesManager_Timezone');

        if (SettingsServer::isTimezoneSupportEnabled()) {
            return array(DiagnosticResult::singleResult($label, DiagnosticResult::STATUS_OK));
        }

        $comment = sprintf(
            '%s<br />%s.',
            $this->translator->translate('SitesManager_AdvancedTimezoneSupportNotFound'),
            '<a href="http://php.net/manual/en/datetime.installation.php" rel="noreferrer" target="_blank">Timezone PHP documentation</a>'
        );

        return array(DiagnosticResult::singleResult($label, DiagnosticResult::STATUS_WARNING, $comment));
    }
}
