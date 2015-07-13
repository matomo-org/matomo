<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Container;

use DI\Definition\ArrayDefinition;
use DI\Definition\EntryReference;
use DI\Definition\Exception\DefinitionException;
use DI\Definition\Source\DefinitionSource;
use Piwik\Application\Environment;
use Piwik\Filesystem;

/**
 * This DefinitionSource matches definition names like `'components.<BaseClass>'` and loads
 * Components for every loaded and activated plugin that derives from BaseClass.
 *
 * For example, the name `'components.Piwik\Plugin\Dimension\VisitDimension'` will load every
 * VisitDimension.
 *
 * The Components are loaded through DI and so, can be injected.
 *
 * This source will also handle keys like `'components.<PluginName>.<BaseClass>'` to get
 * the components for a single plugin.
 *
 * Component base classes must have a const named COMPONENT_SUBNAMESPACE which names the
 * plugin subdirectory the components must be stored under. VisitDimension's, for example,
 * is **Columns**.
 */
class ComponentDefinitionSource implements DefinitionSource
{
    /**
     * @var Environment
     */
    private $environment;

    /**
     * @var string
     */
    private $prefix;

    public function __construct(Environment $environment, $prefix = 'components.')
    {
        $this->environment = $environment;
        $this->prefix = $prefix;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefinition($name)
    {
        if (strpos($name, $this->prefix) !== 0) {
            return null;
        }

        list($specificPlugin, $baseClass) = $this->getBaseClassFromName($name);

        if (!class_exists($baseClass)) {
            throw new DefinitionException("Trying to load components for base class that doesn't exist '$baseClass'.");
        }

        /** @var \Piwik\Plugin\Manager $pluginManager */
        $pluginManager = $this->environment->getContainer()->get('Piwik\Plugin\Manager');

        if ($specificPlugin) {
            if (!$pluginManager->isPluginLoaded($specificPlugin)) {
                return null;
            }

            $components = $this->findComponents($specificPlugin, $baseClass);
        } else {
            $loadedAndActivated = array_keys($pluginManager->getPluginsLoadedAndActivated());

            $components = array();
            foreach ($loadedAndActivated as $pluginName) {
                $components = array_merge($components, $this->findComponents($pluginName, $baseClass));
            }
        }

        $components = array_map(function ($klassName) {
            return new EntryReference($klassName);
        }, $components);

        return new ArrayDefinition($name, $components);
    }

    private function getBaseClassFromName($name)
    {
        $parts = explode('.', $name, 2);
        $pluginAndBaseClass = $parts[1];

        if (strpos($pluginAndBaseClass, '.') !== false) {
            list($specificPlugin, $baseClass) = explode('.', $pluginAndBaseClass, 2);
        } else {
            $specificPlugin = null;
            $baseClass = $pluginAndBaseClass;
        }

        return array($specificPlugin, $baseClass);
    }

    private function findComponents($pluginName, $baseClass)
    {
        $directoryWithinPlugin = $baseClass::COMPONENT_SUBNAMESPACE;

        $components = array();

        $baseDir = PIWIK_INCLUDE_PATH . '/plugins/' . $pluginName . '/' . $directoryWithinPlugin;
        $files   = Filesystem::globr($baseDir, '*.php');

        foreach ($files as $file) {
            $fileName  = str_replace(array($baseDir . '/', '.php'), '', $file);
            $klassName = sprintf('Piwik\\Plugins\\%s\\%s\\%s', $pluginName, $directoryWithinPlugin, str_replace('/', '\\', $fileName));

            if (!class_exists($klassName)) {
                continue;
            }

            if (!is_subclass_of($klassName, $baseClass)) {
                continue;
            }

            $klass = new \ReflectionClass($klassName);

            if ($klass->isAbstract()) {
                continue;
            }

            $components[] = $klassName;
        }
        return $components;
    }
}
