<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\Diagnostics\Diagnostic;

use Piwik\Config;
use Piwik\Filesystem;
use Piwik\Translation\Translator;

/**
 * Checks if the filesystem Piwik stores sessions in is NFS or not.
 *
 * This check is done in order to avoid using file based sessions on NFS system,
 * since on such a filesystem file locking can make file based sessions incredibly slow.
 */
class NfsDiskCheck implements Diagnostic
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
        $label = $this->translator->translate('Installation_Filesystem');

        if (! Filesystem::checkIfFileSystemIsNFS()) {
            return array(DiagnosticResult::singleResult($label, DiagnosticResult::STATUS_OK));
        }

        $isPiwikInstalling = !Config::getInstance()->existsLocalConfig();
        if ($isPiwikInstalling) {
            $help = 'Installation_NfsFilesystemWarningSuffixInstall';
        } else {
            $help = 'Installation_NfsFilesystemWarningSuffixAdmin';
        }

        $comment = sprintf(
            '%s<br />%s',
            $this->translator->translate('Installation_NfsFilesystemWarning'),
            $this->translator->translate($help)
        );

        return array(DiagnosticResult::singleResult($label, DiagnosticResult::STATUS_WARNING, $comment));
    }
}
