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

    public function __construct($pathGlobal = null, $pathLocal = null, $pathCommon = null, $allowSave = false)
    {
        parent::__construct($pathGlobal, $pathLocal, $pathCommon);

        $this->allowSave = $allowSave;
    }

    public function reload()
    {
        if ($this->isSettingTestEnv) {
            parent::reload();
        } else {
            $this->isSettingTestEnv = true;
            $this->setTestEnvironment($this->getLocalPath(), $this->getGlobalPath(), $this->getCommonPath(), $this->allowSave);
            $this->isSettingTestEnv = false;
        }
    }
}