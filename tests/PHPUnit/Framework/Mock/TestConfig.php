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
use Piwik\Piwik;
use Piwik\Tests\Framework\TestingEnvironment;

class TestConfig extends Config
{
    private $allowSave = false;
    private $isSettingTestEnv = false;
    private $isConfigTestEventPosted = false;
    private $doSetTestEnvironment = false;

    public function __construct(GlobalSettingsProvider $settings, $allowSave = false, $doSetTestEnvironment = true,
                                TestingEnvironment $testingEnvironment = null)
    {
        parent::__construct($settings);

        $this->allowSave = $allowSave;
        $this->doSetTestEnvironment = $doSetTestEnvironment;

        $this->reload();

        if ($testingEnvironment) {
            $this->setupFromTestEnvironment($testingEnvironment);
        }
    }

    public function reload($pathLocal = null, $pathGlobal = null, $pathCommon = null)
    {
        if ($this->isSettingTestEnv) {
            parent::reload();
        } else {
            $this->isSettingTestEnv = true;
            $this->setTestEnvironment($this->allowSave);
            $this->isSettingTestEnv = false;
        }
    }

    protected function postConfigTestEvent()
    {
        if ($this->isConfigTestEventPosted) { // avoid infinite recursion in case setTestEnvironment is called from within Config.setSingletonInstance test event
            return;
        } else {
            $this->isConfigTestEventPosted = true;
            parent::postConfigTestEvent();
            $this->isConfigTestEventPosted = false;
        }
    }

    public function setTestEnvironment($allowSaving = false)
    {
        if ($this->doSetTestEnvironment) {
            parent::setTestEnvironment($allowSaving);
        } else {
            $this->doNotWriteConfigInTests = !$allowSaving;

            $this->reload();
        }
    }

    public function forceSave()
    {
        if ($this->allowSave) {
            parent::forceSave();
        }
    }

    private function setupFromTestEnvironment(TestingEnvironment $testingEnvironment)
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