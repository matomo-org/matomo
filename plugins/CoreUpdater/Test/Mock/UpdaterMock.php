<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreUpdater\Test\Mock;

use Piwik\Plugins\CoreUpdater\ArchiveDownloadException;
use Piwik\Plugins\CoreUpdater\Updater;
use Piwik\Translation\Translator;

class UpdaterMock extends Updater
{
    /**
     * @var Translator
     */
    private $translator;

    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    public function getLatestVersion()
    {
        return '4.0.0';
    }

    public function isNewVersionAvailable()
    {
        return true;
    }

    public function getArchiveUrl($version, $https = true)
    {
        return 'http://builds.piwik.org/piwik.zip';
    }

    public function updatePiwik($https = true)
    {
        // Simulate that the update over HTTPS fails
        if ($https) {
            // The actual error message depends on the OS, the HTTP method etc.
            // This is what I get on my machine, but it doesn't really matter
            throw new ArchiveDownloadException(new \Exception('curl_exec: SSL certificate problem: Invalid certificate chain. Hostname requested was: piwik.org'), array());
        }

        // Simulate that the update over HTTP succeeds
        return array(
            $this->translator->translate('CoreUpdater_DownloadingUpdateFromX', ''),
            $this->translator->translate('CoreUpdater_UnpackingTheUpdate'),
            $this->translator->translate('CoreUpdater_VerifyingUnpackedFiles'),
            $this->translator->translate('CoreUpdater_InstallingTheLatestVersion'),
        );
    }
}
