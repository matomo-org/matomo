<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Application;

use DI\Container;
use Doctrine\Common\Cache\Cache;
use Piwik\Application\Kernel\EnvironmentValidator;
use Piwik\Application\Kernel\GlobalSettingsProvider;
use Piwik\Application\Kernel\PluginList;
use Piwik\Application\Kernel\StaticCacheFactory;
use Piwik\Cache\Backend\ArrayCache;
use Piwik\Cache\Backend\Chained;
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
     * @var EnvironmentManipulator
     */
    private static $globalEnvironmentManipulator = null;

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
     * @var Cache
     */
    private $definitionCache;

    /**
     * @var StaticCacheFactory
     */
    private $staticCacheFactory;

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

        StaticContainer::push($this->container);

        $this->validateEnvironment();

        $this->invokeEnvironmentBootstrappedHook();

        Piwik::postEvent('Environment.bootstrapped'); // this event should be removed eventually
    }

    /**
     * Destroys an environment. MUST be called when embedding environments.
     */
    public function destroy()
    {
        StaticContainer::pop();
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
        $this->staticCacheFactory = new StaticCacheFactory($this->getGlobalSettingsCached());

        $pluginList = $this->getPluginListCached();
        $settings = $this->getGlobalSettingsCached();

        $extraDefinitions = $this->getExtraDefinitionsFromManipulators();
        $definitions = array_merge(StaticContainer::getDefinitions(), $extraDefinitions, array($this->definitions));

        $definitionCache = $this->getDefinitionCacheCached();

        $environments = array($this->environment);
        $environments = array_merge($environments, $this->getExtraEnvironmentsFromManipulators());

        $containerFactory = new ContainerFactory($this, $pluginList, $settings, $definitionCache, $environments, $definitions);
        $container = $containerFactory->create();

        $container->set('Piwik\Application\Kernel\StaticCacheFactory', $this->staticCacheFactory);

        return $container;
    }

    protected function getGlobalSettingsCached()
    {
        if ($this->globalSettingsProvider === null) {
            $original = $this->getGlobalSettings();
            $globalSettingsProvider = $this->getGlobalSettingsProviderOverride($original);

            $this->globalSettingsProvider = $globalSettingsProvider ?: $original;
        }
        return $this->globalSettingsProvider;
    }

    protected function getPluginListCached()
    {
        if ($this->pluginList === null) {
            $pluginList = $this->getPluginListOverride();
            $this->pluginList = $pluginList ?: $this->getPluginList();
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
    public static function setGlobalEnvironmentManipulator(EnvironmentManipulator $manipulator)
    {
        self::$globalEnvironmentManipulator = $manipulator;
    }

    private function getGlobalSettingsProviderOverride(GlobalSettingsProvider $original)
    {
        if (self::$globalEnvironmentManipulator) {
            return self::$globalEnvironmentManipulator->makeGlobalSettingsProvider($original);
        } else {
            return null;
        }
    }

    private function invokeBeforeContainerCreatedHook()
    {
        if (self::$globalEnvironmentManipulator) {
            return self::$globalEnvironmentManipulator->beforeContainerCreated();
        }
    }

    private function getExtraDefinitionsFromManipulators()
    {
        if (self::$globalEnvironmentManipulator) {
            return self::$globalEnvironmentManipulator->getExtraDefinitions();
        } else {
            return array();
        }
    }

    private function invokeEnvironmentBootstrappedHook()
    {
        if (self::$globalEnvironmentManipulator) {
            self::$globalEnvironmentManipulator->onEnvironmentBootstrapped();
        }
    }

    private function getExtraEnvironmentsFromManipulators()
    {
        if (self::$globalEnvironmentManipulator) {
            return self::$globalEnvironmentManipulator->getExtraEnvironments();
        } else {
            return array();
        }
    }

    private function getPluginListOverride()
    {
        if (self::$globalEnvironmentManipulator) {
            return self::$globalEnvironmentManipulator->makePluginList($this->getGlobalSettingsCached());
        } else {
            return null;
        }
    }

    private function getDefinitionCacheCached()
    {
        if ($this->definitionCache === null) {
            $definitionCache = $this->getDefinitionCacheOverride();
            $this->definitionCache = $definitionCache ?: $this->getDefinitionCache();
        }
        return $this->definitionCache;
    }

    private function getDefinitionCacheOverride()
    {
        if (self::$globalEnvironmentManipulator) {
            return self::$globalEnvironmentManipulator->makeDefinitionCache();
        } else {
            return null;
        }
    }

    private function getDefinitionCache()
    {
        $cache = $this->getStaticCacheBackend();

        if ($cache instanceof ArrayCache) {
            return $cache;
        } else {
            return new Chained(array(new ArrayCache(), $cache));
        }
    }

    private function getStaticCacheBackendName()
    {
        $settingsProvider = $this->getGlobalSettingsCached();
        $cacheConfigSection = $settingsProvider->getSection('Cache');
        return @$cacheConfigSection['static_cache_backend'];
    }

    private function getStaticCacheBackend()
    {
        $staticCacheBackend = $this->getStaticCacheBackendName();
        if ($staticCacheBackend == 'null'
            || $staticCacheBackend == 'file'
            || $staticCacheBackend == 'chained'
        ) {
            throw new \RuntimeException("Invalid static cache type '$staticCacheBackend'. See global.ini.php for allowed values.");
        }

        if ($staticCacheBackend == 'array') {
            return new ArrayCache();
        }

        return $this->staticCacheFactory->make($staticCacheBackend);
    }
}
