<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\CustomPiwikJs\Diagnostic;

use Piwik\Filechecks;
use Piwik\Filesystem;
use Piwik\Plugins\CustomPiwikJs\File;
use Piwik\Plugins\Diagnostics\Diagnostic\Diagnostic;
use Piwik\Plugins\Diagnostics\Diagnostic\DiagnosticResult;
use Piwik\SettingsServer;
use Piwik\Translation\Translator;

/**
 * Check Piwik JS is writable
 */
class PiwikJsCheck implements Diagnostic
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
        $label = $this->translator->translate('CustomPiwikJs_DiagnosticPiwikJsWritable');

        $file = new File(PIWIK_DOCUMENT_ROOT . '/piwik.js');

        if ($file->hasWriteAccess()) {
            return array(DiagnosticResult::singleResult($label, DiagnosticResult::STATUS_OK, ''));
        }

        $comment = $this->translator->translate('CustomPiwikJs_DiagnosticPiwikJsNotWritable');

        if(!SettingsServer::isWindows()) {
            $realpath = Filesystem::realpath(PIWIK_INCLUDE_PATH . '/piwik.js');
            $command = "<br/><code> chmod +w $realpath<br/> chown ". Filechecks::getUserAndGroup() ." " . $realpath . "</code><br />";
            $comment .= $this->translator->translate('CustomPiwikJs_DiagnosticPiwikJsMakeWritable', $command);
        }

        return array(DiagnosticResult::singleResult($label, DiagnosticResult::STATUS_WARNING, $comment));
    }

}
