<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Updater\Migration\Plugin;

use Piwik\Plugin;
use Piwik\Updater\Migration;

/**
 * Deactivates the given plugin during the update
 */
class Deactivate extends Migration
{
    /**
     * @var string
     */
    private $pluginName;

    /**
     * @var Plugin\Manager
     */
    private $pluginManager;

    public function __construct(Plugin\Manager $pluginManager, $pluginName)
    {
        $this->pluginManager = $pluginManager;
        $this->pluginName = $pluginName;
    }

    public function __toString()
    {
        return sprintf('Deactivating plugin "%s"', $this->pluginName);
    }

    public function shouldIgnoreError($exception)
    {
        return true;
    }

    public function exec()
    {
        if ($this->pluginManager->isPluginActivated($this->pluginName)) {
            $this->pluginManager->deactivatePlugin($this->pluginName);
        }
    }

}
