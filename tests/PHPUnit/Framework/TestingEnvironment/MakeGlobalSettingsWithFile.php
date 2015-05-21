<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Tests\Framework\TestingEnvironment;

use Piwik\Application\EnvironmentManipulator;
use Piwik\Application\Kernel\GlobalSettingsProvider;
use Piwik\Tests\Framework\TestingEnvironment;

class MakeGlobalSettingsWithFile implements EnvironmentManipulator
{
    private $configFileGlobal;
    private $configFileLocal;
    private $configFileCommon;

    public function __construct(\Piwik_TestingEnvironment $testingEnvironment)
    {
        $this->configFileGlobal = $testingEnvironment->configFileGlobal;
        $this->configFileLocal = $testingEnvironment->configFileLocal;
        $this->configFileCommon = $testingEnvironment->configFileCommon;
    }

    public function makeGlobalSettingsProvider()
    {
        return new GlobalSettingsProvider($this->configFileGlobal, $this->configFileLocal, $this->configFileCommon);
    }
}