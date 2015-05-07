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
        $this->container = $this->createContainer();

        StaticContainer::set($this->container);

        $this->validateEnvironment();

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
        $definitions = array_merge(StaticContainer::getDefinitions(), $this->definitions);

        $containerFactory = new ContainerFactory($pluginList, $settings, $this->environment, $definitions);
        return $containerFactory->create();
    }

    protected function getGlobalSettingsCached()
    {
        if ($this->globalSettingsProvider === null) {
            $this->globalSettingsProvider = $this->getGlobalSettings();
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
        // TODO: need to be able to set path global/local/etc. which is in DI... for now works because TestingEnvironment creates
        //       singleton instance before this method.
        return GlobalSettingsProvider::getSingletonInstance();
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
}