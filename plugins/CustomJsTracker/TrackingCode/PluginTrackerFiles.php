<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\CustomJsTracker\TrackingCode;

use Piwik\Piwik;
use Piwik\Plugin;
use Piwik\Plugins\CustomJsTracker\File;

class PluginTrackerFiles
{
    const TRACKER_FILE = 'tracker.js';
    const MIN_TRACKER_FILE = 'tracker.min.js';

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
        $this->pluginManager = Plugin\Manager::getInstance();
    }

    public function ignoreMinified()
    {
        $this->ignoreMinified = true;
    }

    protected function getDirectoriesToLook()
    {
        $dirs = array();
        $manager = Plugin\Manager::getInstance();
        foreach ($manager->getPluginsLoadedAndActivated() as $pluginName => $plugin) {
            $dirs[$pluginName] = rtrim(Plugin\Manager::getPluginDirectory($pluginName), '/') . '/';
        }
        return $dirs;
    }

    /**
     * @return File[]
     */
    public function find()
    {
        $jsFiles = array();

        foreach ($this->getDirectoriesToLook() as $pluginName => $pluginDir) {
            if (!$this->ignoreMinified && file_exists($pluginDir . self::MIN_TRACKER_FILE)) {
                $jsFiles[$pluginName] = new File($pluginDir . self::MIN_TRACKER_FILE);
            } elseif (file_exists($pluginDir . self::TRACKER_FILE)) {
                $jsFiles[$pluginName] = new File($pluginDir . self::TRACKER_FILE);
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
        Piwik::postEvent('CustomJsTracker.shouldAddTrackerFile', array(&$shouldAddFile, $pluginName));

        return $shouldAddFile;
    }

    protected function isPluginActivated($pluginName)
    {
        return $this->pluginManager->isPluginActivated($pluginName);
    }
}
