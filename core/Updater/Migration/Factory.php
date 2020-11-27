<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Updater\Migration;

use Piwik\Updater\Migration\Db\Factory as DbFactory;
use Piwik\Updater\Migration\Plugin\Factory as PluginFactory;
use Piwik\Updater\Migration\Config\Factory as ConfigFactory;

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
     * @var ConfigFactory
     */
    public $config;

    /**
     * @ignore
     */
    public function __construct(DbFactory $dbFactory, PluginFactory $pluginFactory, ConfigFactory $configFactory)
    {
        $this->db = $dbFactory;
        $this->plugin = $pluginFactory;
        $this->config = $configFactory;
    }
}
