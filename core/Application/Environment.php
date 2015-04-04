<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Application;

use DI\Container;
use Piwik\Container\ContainerFactory;
use Piwik\Container\StaticContainer;

/**
 * TODO
 */
class Environment
{
    private $environment;

    /**
     * @var Container
     */
    private $container;

    public function __construct($environment)
    {
        $this->environment = $environment;
    }

    public function init()
    {
        $this->container = $this->createContainer();

        StaticContainer::set($this->container);
    }

    public function getContainer()
    {
        return $this->container;
    }

    public function get($id)
    {
        return $this->container->get($id);
    }

    /**
     * @link http://php-di.org/doc/container-configuration.html
     */
    private function createContainer()
    {
        $containerFactory = new ContainerFactory($this->environment, StaticContainer::getDefinitons());
        return $containerFactory->create();
    }
}