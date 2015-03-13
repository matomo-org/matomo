<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreUpdater\tests\Mock;

use Piwik\Plugins\CoreUpdater\Updater;
use Piwik\Plugins\CoreUpdater\UpdaterException;
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

    public function updatePiwik($https = true)
    {
        // Simulate that the update over HTTPS fails
        if ($https) {
            throw new UpdaterException(new \Exception('Error while downloading Piwik'), array());
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
