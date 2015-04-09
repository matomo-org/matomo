<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Framework\Mock;

use Piwik\Config;
use Piwik\Piwik;

class TestConfig extends Config
{
    private $allowSave = false;
    private $isSettingTestEnv = false;
    private $isConfigTestEventPosted = false;
    private $doSetTestEnvironment = false;

    public function __construct($pathGlobal = null, $pathLocal = null, $pathCommon = null, $allowSave = false, $doSetTestEnvironment = true)
    {
        parent::__construct($pathGlobal, $pathLocal, $pathCommon);

        $this->allowSave = $allowSave;
        $this->doSetTestEnvironment = $doSetTestEnvironment;

        $this->reload($pathGlobal, $pathLocal, $pathCommon);
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
}