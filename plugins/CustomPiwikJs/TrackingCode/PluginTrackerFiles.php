<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\CustomPiwikJs\TrackingCode;

use Piwik\Filesystem;
use Piwik\Plugin;
use Piwik\Plugins\CustomPiwikJs\File;

class PluginTrackerFiles
{
    const TRACKER_FILE = 'tracker.js';
    const MIN_TRACKER_FILE = 'tracker.min.js';

    /**
     * @var string
     */
    protected $dir;

    /**
     * @var Plugin\Manager
     */
    private $pluginManager;

    /**
     * @var bool
     */
    protected $ignoreMinified = false;

    public function __construct()
    {
        $this->dir = PIWIK_DOCUMENT_ROOT . '/plugins/';
        $this->pluginManager = Plugin\Manager::getInstance();
    }

    public function ignoreMinified()
    {
        $this->ignoreMinified = true;
    }

    /**
     * @return File[]
     */
    public function find()
    {
        $jsFiles = array();

        if (!$this->ignoreMinified) {
            $trackerFiles = \_glob($this->dir . '*/' . self::MIN_TRACKER_FILE);

            foreach ($trackerFiles as $trackerFile) {
                $plugin = $this->getPluginNameFromFile($trackerFile);
                if ($this->isPluginActivated($plugin)) {
                    $jsFiles[$plugin] = new File($trackerFile);
                }
            }
        }

        $trackerFiles = \_glob($this->dir . '*/' . self::TRACKER_FILE);

        foreach ($trackerFiles as $trackerFile) {
            $plugin = $this->getPluginNameFromFile($trackerFile);
            if (!isset($jsFiles[$plugin])) {
                if ($this->isPluginActivated($plugin)) {
                    $jsFiles[$plugin] = new File($trackerFile);
                }
            }
        }

        return $jsFiles;
    }

    protected function isPluginActivated($pluginName)
    {
        return $this->pluginManager->isPluginActivated($pluginName);
    }

    protected function getPluginNameFromFile($file)
    {
        $file = str_replace(array($this->dir, self::TRACKER_FILE, self::MIN_TRACKER_FILE), '', $file);
        return trim($file, '/');
    }
}
