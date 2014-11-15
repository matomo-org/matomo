<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\TestRunner\Runner;

use Piwik\Plugins\TestRunner\Aws\Instance;

class InstanceLauncher {

    /**
     * @var Instance
     */
    private $instance;

    public function __construct(Instance $instance)
    {
        $this->instance = $instance;
    }

    public function launchOrResumeInstance()
    {
        $host = $this->instance->findExisting();

        if (empty($host)) {
            $host = $this->launchInstance();
        }

        return $host;
    }

    private function launchInstance()
    {
        $instanceIds = $this->instance->launch();

        try {
            $host = $this->instance->setup($instanceIds);
            $this->instance->verifySetup($instanceIds);
        } catch (\Exception $e) {
            $this->instance->terminate($instanceIds);

            throw new \RuntimeException('We failed to launch a new instance so we terminated it directly. Try again! Error Message: ' . $e->getMessage());
        }

        return $host;
    }

}