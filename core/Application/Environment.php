<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Application;

use DI\Container;
use Piwik\Application\Kernel\EnvironmentValidator;
use Piwik\Application\Kernel\GlobalSettingsProvider;
use Piwik\Application\Kernel\PluginList;
use Piwik\Container\ContainerFactory;
use Piwik\Container\StaticContainer;
use Piwik\Piwik;

/**
 * Encapsulates Piwik environment setup and access.
 *
 * The Piwik environment consists of two main parts: the kernel and the DI container.
 *
 * The 'kernel' is the core part of Piwik that cannot be modified / extended through the DI container.
 * It includes components that are required to create the DI container.
 *
 * Currently the only objects in the 'kernel' are a GlobalSettingsProvider object and a
 * PluginList object. The GlobalSettingsProvider object is required for the current PluginList
 * implementation and for checking whether Development mode is enabled. The PluginList is
 * needed in order to determine what plugins are activated, since plugins can provide their
 * own DI configuration.
 *
 * The DI container contains every other Piwik object, including the Plugin\Manager,
 * plugin API instances, dependent services, etc. Plugins and users can override/extend
 * the objects in this container.
 *
 * NOTE: DI support in Piwik is currently a work in process; not everything is currently
 * stored in the DI container, but we are working towards this.
 */
class Environment
{
    /**
     * @internal
     * @var EnvironmentManipulator[]
     */
    private static $globalEnvironmentManipulators = array();

    /**
     * @var string
     */
    private $environment;

    /**
     * @var array
     */
    private $definitions;

    /**
     * @var Container
     */
    private $container;

    /**
     * @var GlobalSettingsProvider
     */
    private $globalSettingsProvider;

    /**
     * @var PluginList
     */
    private $pluginList;

    /**
     * @param string $environment
     * @param array $definitions
     */
    public function __construct($environment, array $definitions = array())
    {
        $this->environment = $environment;
        $this->definitions = $definitions;
    }

    /**
     * Initializes the kernel globals and DI container.
     */
    public function init()
    {
        $this->invokeBeforeContainerCreatedHook();

        $this->container = $this->createContainer();

        StaticContainer::set($this->container);

        $this->validateEnvironment();

        $this->invokeEnvironmentBootstrappedHook();

        Piwik::postEvent('Environment.bootstrapped'); // this event should be removed eventually
    }

    /**
     * Returns the DI container. All Piwik objects for a specific Piwik instance should be stored
     * in this container.
     *
     * @return Container
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @link http://php-di.org/doc/container-configuration.html
     */
    private function createContainer()
    {
        $pluginList = $this->getPluginListCached();
        $settings = $this->getGlobalSettingsCached();

        $extraDefinitions = $this->getExtraDefinitionsFromManipulators();
        $definitions = array_merge(StaticContainer::getDefinitions(), $extraDefinitions, array($this->definitions));

        $containerFactory = new ContainerFactory($pluginList, $settings, $this->environment, $definitions);
        return $containerFactory->create();
    }

    protected function getGlobalSettingsCached()
    {
        if ($this->globalSettingsProvider === null) {
            $globalSettingsProvider = $this->getGlobalSettingsProviderOverride();
            $this->globalSettingsProvider = $globalSettingsProvider ?: $this->getGlobalSettings();
        }
        return $this->globalSettingsProvider;
    }

    protected function getPluginListCached()
    {
        if ($this->pluginList === null) {
            $this->pluginList = $this->getPluginList();
        }
        return $this->pluginList;
    }

    /**
     * Returns the kernel global GlobalSettingsProvider object. Derived classes can override this method
     * to provide a different implementation.
     *
     * @return null|GlobalSettingsProvider
     */
    protected function getGlobalSettings()
    {
        return new GlobalSettingsProvider();
    }

    /**
     * Returns the kernel global PluginList object. Derived classes can override this method to
     * provide a different implementation.
     *
     * @return PluginList
     */
    protected function getPluginList()
    {
        // TODO: in tracker should only load tracker plugins. can't do properly until tracker entrypoint is encapsulated.
        return new PluginList($this->getGlobalSettingsCached());
    }

    private function validateEnvironment()
    {
        /** @var EnvironmentValidator $validator */
        $validator = $this->container->get('Piwik\Application\Kernel\EnvironmentValidator');
        $validator->validate();
    }

    /**
     * @param EnvironmentManipulator $manipulator
     * @internal
     */
    public static function addEnvironmentManipulator(EnvironmentManipulator $manipulator)
    {
        self::$globalEnvironmentManipulators[] = $manipulator;
    }

    private function getGlobalSettingsProviderOverride()
    {
        foreach (self::$globalEnvironmentManipulators as $manipulator) {
            $result = $manipulator->makeGlobalSettingsProvider();
            if (!empty($result)) {
                return $result;
            }
        }

        return null;
    }

    private function invokeBeforeContainerCreatedHook()
    {
        foreach (self::$globalEnvironmentManipulators as $manipulator) {
            $manipulator->beforeContainerCreated();
        }
    }

    private function getExtraDefinitionsFromManipulators()
    {
        $result = array();
        foreach (self::$globalEnvironmentManipulators as $manipulator) {
            $result = array_merge($result, $manipulator->getExtraDefinitions());
        }
        return $result;
    }

    private function invokeEnvironmentBootstrappedHook()
    {
        foreach (self::$globalEnvironmentManipulators as $manipulator) {
            $manipulator->onEnvironmentBootstrapped();
        }
    }
}
