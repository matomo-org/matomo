<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Framework\Mock;

use Piwik\Config;

class TestConfig extends Config
{
    private $allowSave = false;
    private $isSettingTestEnv = false;
    private $doSetTestEnvironment = false;

    public function __construct($pathGlobal = null, $pathLocal = null, $pathCommon = null, $allowSave = false, $doSetTestEnvironment = true)
    {
        \Piwik\Application\Kernel\GlobalSettingsProvider::unsetSingletonInstance();

        parent::__construct($pathGlobal, $pathLocal, $pathCommon);

        $this->allowSave = $allowSave;
        $this->doSetTestEnvironment = $doSetTestEnvironment;

        $this->reload($pathGlobal, $pathLocal, $pathCommon);

        $testingEnvironment = new \Piwik_TestingEnvironment();
        $this->setFromTestEnvironment($testingEnvironment);
    }

    public function reload($pathLocal = null, $pathGlobal = null, $pathCommon = null)
    {
        if ($this->isSettingTestEnv) {
            parent::reload($pathGlobal, $pathLocal, $pathCommon);
        } else {
            $this->isSettingTestEnv = true;
            $this->setTestEnvironment($pathLocal, $pathGlobal, $pathCommon, $this->allowSave);
            $this->isSettingTestEnv = false;
        }
    }

    public function setTestEnvironment($pathLocal = null, $pathGlobal = null, $pathCommon = null, $allowSaving = false)
    {
        if ($this->doSetTestEnvironment) {
            parent::setTestEnvironment($pathLocal, $pathGlobal, $pathCommon, $allowSaving);
        } else {
            $this->doNotWriteConfigInTests = !$allowSaving;

            $this->pathLocal = $pathLocal ?: Config::getLocalConfigPath();
            $this->pathGlobal = $pathGlobal ?: Config::getGlobalConfigPath();
            $this->pathCommon = $pathCommon ?: Config::getCommonConfigPath();

            $this->reload();
        }
    }

    public function forceSave()
    {
        if ($this->allowSave) {
            parent::forceSave();
        }
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