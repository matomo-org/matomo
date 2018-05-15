<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\CustomPiwikJs\TrackingCode;

use Piwik\Piwik;
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

        foreach ($jsFiles as $plugin => $file) {
            if (!$this->shouldIncludeFile($plugin)) {
                unset($jsFiles[$plugin]);
            }
        }

        return $jsFiles;
    }

    protected function shouldIncludeFile($pluginName)
    {
        $shouldAddFile = true;

        /**
         * Detect if a custom tracker file should be added to the piwik.js tracker or not.
         *
         * This is useful for example if a plugin only wants to add its tracker file when the plugin is configured.
         *
         * @param bool &$shouldAddFile Decides whether the tracker file belonging to the given plugin should be added or not.
         * @param string $pluginName The name of the plugin this file belongs to
         */
        Piwik::postEvent('CustomPiwikJs.shouldAddTrackerFile', array(&$shouldAddFile, $pluginName));

        return $shouldAddFile;
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
