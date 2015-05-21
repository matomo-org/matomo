<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Framework\Mock;

use Piwik\Application\Kernel\GlobalSettingsProvider;
use Piwik\Config;

class TestConfig extends Config
{
    private $allowSave = false;
    private $doSetTestEnvironment = false;

    public function __construct(GlobalSettingsProvider $provider, $allowSave = false, $doSetTestEnvironment = true)
    {
        parent::__construct($provider);

        $this->allowSave = $allowSave;
        $this->doSetTestEnvironment = $doSetTestEnvironment;

        $this->reload();

        $testingEnvironment = new \Piwik_TestingEnvironment();
        $this->setFromTestEnvironment($testingEnvironment);
    }

    public function reload($pathLocal = null, $pathGlobal = null, $pathCommon = null)
    {
        parent::reload($pathGlobal, $pathLocal, $pathCommon);

        $this->setTestEnvironment();
    }

    public function forceSave()
    {
        if ($this->allowSave) {
            parent::forceSave();
        }
    }

    public function setTestEnvironment()
    {
        if (!$this->allowSave) {
            $this->doNotWriteConfigInTests = true;
        }

        $chain = $this->settings->getIniFileChain();

        $databaseTestsSettings = $chain->get('database_tests'); // has to be __get otherwise when called from TestConfig, PHP will issue a NOTICE
        if (!empty($databaseTestsSettings)) {
            $chain->set('database', $databaseTestsSettings);
        }

        // Ensure local mods do not affect tests
        if (empty($pathGlobal)) {
            $chain->set('Debug', $chain->getFrom($this->getGlobalPath(), 'Debug'));
            $chain->set('mail', $chain->getFrom($this->getGlobalPath(), 'mail'));
            $chain->set('General', $chain->getFrom($this->getGlobalPath(), 'General'));
            $chain->set('Segments', $chain->getFrom($this->getGlobalPath(), 'Segments'));
            $chain->set('Tracker', $chain->getFrom($this->getGlobalPath(), 'Tracker'));
            $chain->set('Deletelogs', $chain->getFrom($this->getGlobalPath(), 'Deletelogs'));
            $chain->set('Deletereports', $chain->getFrom($this->getGlobalPath(), 'Deletereports'));
            $chain->set('Development', $chain->getFrom($this->getGlobalPath(), 'Development'));
        }

        // for unit tests, we set that no plugin is installed. This will force
        // the test initialization to create the plugins tables, execute ALTER queries, etc.
        $chain->set('PluginsInstalled', array('PluginsInstalled' => array()));
    }

    private function setFromTestEnvironment(\Piwik_TestingEnvironment $testingEnvironment)
    {
        $pluginsToLoad = $testingEnvironment->getCoreAndSupportedPlugins();
        if (!empty($testingEnvironment->pluginsToLoad)) {
            $pluginsToLoad = array_unique(array_merge($pluginsToLoad, $testingEnvironment->pluginsToLoad));
        }

        sort($pluginsToLoad);

        $chain = $this->settings->getIniFileChain();

        $general =& $chain->get('General');
        $plugins =& $chain->get('Plugins');
        $log =& $chain->get('log');
        $database =& $chain->get('database');

        if ($testingEnvironment->configFileLocal) {
            $general['session_save_handler'] = 'dbtable';
        }

        $plugins['Plugins'] = $pluginsToLoad;

        $log['log_writers'] = array('file');

        // TODO: replace this and below w/ configOverride use
        if ($testingEnvironment->tablesPrefix) {
            $database['tables_prefix'] = $testingEnvironment->tablesPrefix;
        }

        if ($testingEnvironment->dbName) {
            $database['dbname'] = $testingEnvironment->dbName;
        }

        if ($testingEnvironment->configOverride) {
            $cache =& $chain->getAll();
            $cache = $testingEnvironment->arrayMergeRecursiveDistinct($cache, $testingEnvironment->configOverride);
        }
    }
}