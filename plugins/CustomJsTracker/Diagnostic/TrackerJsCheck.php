<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\CustomJsTracker\Diagnostic;

use Piwik\Filechecks;
use Piwik\Filesystem;
use Piwik\Plugins\CustomJsTracker\File;
use Piwik\Plugins\Diagnostics\Diagnostic\Diagnostic;
use Piwik\Plugins\Diagnostics\Diagnostic\DiagnosticResult;
use Piwik\SettingsPiwik;
use Piwik\SettingsServer;
use Piwik\Tracker\TrackerCodeGenerator;
use Piwik\Translation\Translator;

/**
 * Check Piwik JS is writable
 */
class TrackerJsCheck implements Diagnostic
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
        // for users that installed matomo 3.7+ we only check for matomo.js being writable... for all other users we
        // check both piwik.js and matomo.js as they can use both
        $filesToCheck = array('matomo.js');

        $jsCodeGenerator = new TrackerCodeGenerator();
        if (SettingsPiwik::isMatomoInstalled() && $jsCodeGenerator->shouldPreferPiwikEndpoint()) {
            // if matomo is not installed yet, we definitely prefer matomo.js... check for isMatomoInstalled is needed
            // cause otherwise it would perform a db query before matomo DB is configured
            $filesToCheck[] = 'piwik.js';
        }

        $notWritableFiles = array();
        foreach ($filesToCheck as $fileToCheck) {
            $file = new File(PIWIK_DOCUMENT_ROOT . '/' . $fileToCheck);

            if (!$file->hasWriteAccess()) {
                $notWritableFiles[] = $fileToCheck;
            }
        }

        $label = $this->translator->translate('CustomJsTracker_DiagnosticPiwikJsWritable', $this->makeFilesTitles($filesToCheck));

        if (empty($notWritableFiles)) {
            return array(DiagnosticResult::singleResult($label, DiagnosticResult::STATUS_OK, ''));
        }

        $comment = $this->translator->translate('CustomJsTracker_DiagnosticPiwikJsNotWritable', $this->makeFilesTitles($notWritableFiles));

        if (!SettingsServer::isWindows()) {
            $command = '';
            foreach ($notWritableFiles as $notWritableFile) {
                $realpath = Filesystem::realpath(PIWIK_INCLUDE_PATH . '/' . $notWritableFile);
                $command .= "<br/><code> chmod +w $realpath<br/> chown ". Filechecks::getUserAndGroup() ." " . $realpath . "</code><br />";
            }
            $comment .= $this->translator->translate('CustomJsTracker_DiagnosticPiwikJsMakeWritable', array($this->makeFilesTitles($notWritableFiles), $command));
        }

        return array(DiagnosticResult::singleResult($label, DiagnosticResult::STATUS_WARNING, $comment));
    }

    private function makeFilesTitles($files)
    {
        return '"/'. implode('" & "/', $files) .'"';
    }

}
