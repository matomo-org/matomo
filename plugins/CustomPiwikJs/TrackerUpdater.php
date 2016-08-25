<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CustomPiwikJs;

use Piwik\Plugins\CustomPiwikJs\TrackingCode\PiwikJsManipulator;
use Piwik\Plugins\CustomPiwikJs\TrackingCode\PluginTrackerFiles;

/**
 * Updates the Javascript file containing the Tracker.
 */
class TrackerUpdater
{
    const DEVELOPMENT_PIWIK_JS = '/js/piwik.js';
    const ORIGINAL_PIWIK_JS = '/js/piwik.min.js';
    const TARGET_PIWIK_JS = '/piwik.js';

    /**
     * @var File
     */
    private $fromFile;

    /**
     * @var File
     */
    private $toFile;

    private $trackerFiles = array();

    /**
     * @param string|null $fromFile If null then the minified JS tracker file in /js fill be used
     * @param string|null $toFile If null then the minified JS tracker will be updated.
     */
    public function __construct($fromFile = null, $toFile = null)
    {
        if (!isset($fromFile)) {
            $fromFile = PIWIK_DOCUMENT_ROOT . self::ORIGINAL_PIWIK_JS;
        }

        if (!isset($toFile)) {
            $toFile = PIWIK_DOCUMENT_ROOT . self::TARGET_PIWIK_JS;
        }

        $this->fromFile = new File($fromFile);
        $this->toFile = new File($toFile);
        $this->trackerFiles = new PluginTrackerFiles();
    }

    public function setTrackerFiles(PluginTrackerFiles $trackerFiles)
    {
        $this->trackerFiles = $trackerFiles;
    }

    public function checkWillSucceed()
    {
        $this->fromFile->checkReadable();
        $this->toFile->checkWritable();
    }

    public function update()
    {
        if (!$this->toFile->hasWriteAccess() || !$this->fromFile->hasReadAccess()) {
            return;
        }

        $trackingCode = new PiwikJsManipulator($this->fromFile->getContent(), $this->trackerFiles);
        $newContent = $trackingCode->manipulateContent();

        if ($newContent !== $this->toFile->getContent()) {
            $this->toFile->save($newContent);
        }
    }
}
