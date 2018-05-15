<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Updater\Migration;

use Interop\Container\ContainerInterface;
use Piwik\Common;
use Piwik\Updater\Migration\Db\Factory as DbFactory;
use Piwik\Updater\Migration\Plugin\Factory as PluginFactory;

/**
 * Migration factory to create various migrations that implement the Migration interface.
 *
 * @api
 */
class Factory
{
    /**
     * @var DbFactory
     */
    public $db;

    /**
     * @var PluginFactory
     */
    public $plugin;

    /**
     * @ignore
     */
    public function __construct(DbFactory $dbFactory, PluginFactory $pluginFactory)
    {
        $this->db = $dbFactory;
        $this->plugin = $pluginFactory;
    }
}
