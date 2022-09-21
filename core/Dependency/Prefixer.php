<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Dependency;

use Piwik\CliMulti\CliPhp;
use Piwik\Filesystem;
use Psr\Log\LoggerInterface;

class Prefixer
{
    const SUPPORTED_CORE_DEPENDENCIES = [
        'twig/twig',
        'monolog/monolog',
        'symfony/monolog-bridge',
    ];

    /**
     * 'core' or a plugin name
     *
     * @var string
     */
    private $componentToPrefix;

    /**
     * @var string[]
     */
    private $dependenciesToPrefix;

    /**
     * @var string[]
     */
    private $namespacesToInclude;

    /**
     * @var string[]
     */
    private $coreNamespacesToPrefix;

    /**
     * @var string
     */
    private $vendorPath;

    /**
     * @var string
     */
    private $pathToPhpScoper;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function run()
    {
        $this->scopeDependencies();
        if ($this->componentToPrefix !== 'core') {
            $this->scopeCoreDependenciesForPlugin();
        }
    }

    public function setComponentToPrefix($componentToPrefix)
    {
        $this->componentToPrefix = $componentToPrefix;

        if ($this->componentToPrefix === 'core') {
            $this->dependenciesToPrefix = self::SUPPORTED_CORE_DEPENDENCIES;
            $this->vendorPath = PIWIK_VENDOR_PATH;
        } else {
            $pluginJson = PIWIK_INCLUDE_PATH . '/plugins/' . $componentToPrefix . '/plugin.json';
            if (!is_file($pluginJson)) {
                throw new \Exception("Cannot find the $pluginJson file, this is where the dependencies to prefix need to be declared (in the prefixedDependencies property).");
            }

            $contents = file_get_contents($pluginJson);
            $contents = json_decode($contents, true);
            if (!$contents
                || !is_array($contents['prefixedDependencies'])
            ) {
                throw new \Exception("Cannot read the prefixedDependencies key in $pluginJson. It should be an array of dependencies, eg, [\"twig/twig\", ...].");
            }

            $this->dependenciesToPrefix = $contents['prefixedDependencies'];

            $this->vendorPath = PIWIK_INCLUDE_PATH . '/plugins/vendor';

            $this->collectCoreNamespacesToPrefix();
        }

        $this->collectChildDependencies();
    }

    public function generatePhpScoperFileIfNotExists()
    {
        if ($this->componentToPrefix === 'core') {
            return false;
        }

        $pluginScoperIncFile = PIWIK_INCLUDE_PATH . '/plugins/' . $this->componentToPrefix . '/scoper.inc.php';
        if (is_file($pluginScoperIncFile)) {
            return false;
        }

        $scoperIncFileContents = <<<EOF
<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

use Isolated\Symfony\Component\Finder\Finder;

\$dependenciesToPrefix = json_decode(getenv('MATOMO_DEPENDENCIES_TO_PREFIX'), true);
\$namespacesToPrefix = json_decode(getenv('MATOMO_NAMESPACES_TO_PREFIX'), true);

return [
    'prefix' => 'Matomo\\Dependencies\\{$this->componentToPrefix}',
    'finders' => array_map(function (\$dependency) {
        return Finder::create()
            ->files()
            ->in(\$dependency);
    }, \$dependenciesToPrefix),
    'patchers' => [
        // define custom patchers here
    ],
    'include-namespaces' => \$namespacesToPrefix,
];
EOF;

        file_put_contents($pluginScoperIncFile, $scoperIncFileContents);

        return true;
    }

    public function setPathToPhpScoper($pathToPhpScoper)
    {
        $this->pathToPhpScoper = $pathToPhpScoper;
    }

    private function scopeDependencies()
    {
        $command = $this->getPhpScoperCommand();
        passthru($command, $returnCode);
        if ($returnCode) {
            throw new \Exception("Failed to run php-scoper! Command was: $command");
        }
    }

    private function scopeCoreDependenciesForPlugin()
    {
        $command = $this->getPhpScoperCommandForPrefixingCoreDepsInPlugin();
        passthru($command, $returnCode);
        if ($returnCode) {
            throw new \Exception("Failed to run php-scoper for prefixing core dependencies in plugin dependencies! Command was: $command");
        }

        // swap prefixed2 and prefixed
        $vendorPath = PIWIK_INCLUDE_PATH . '/plugins/' . $this->componentToPrefix . '/vendor';
        Filesystem::unlinkRecursive("$vendorPath/prefixed", true);
        rename("$vendorPath/prefixed2", "$vendorPath/prefixed");
    }

    private function getPhpScoperCommand()
    {
        $isCore = $this->componentToPrefix == 'core';
        $vendorPath = $isCore ? PIWIK_VENDOR_PATH : PIWIK_INCLUDE_PATH . '/plugins/' . $this->componentToPrefix . '/vendor';

        $cliPhp = new CliPhp();
        $phpBinary = $cliPhp->findPhpBinary();

        $env = 'MATOMO_DEPENDENCIES_TO_PREFIX="' . addslashes(json_encode($this->dependenciesToPrefix)) . '" '
            . 'MATOMO_NAMESPACES_TO_PREFIX="' . addslashes(json_encode($this->namespacesToInclude)) . '"';
        $command = 'cd ' . $vendorPath . ' && ' . $env . ' ' . $phpBinary . ' ' . $this->pathToPhpScoper
            . ' add --force --output-dir=./prefixed/ --config=../scoper.inc.php';

        $this->logger->debug('php-scoper command: {command}', ['command' => $command]);

        return $command;
    }

    private function getPhpScoperCommandForPrefixingCoreDepsInPlugin()
    {
        $pluginPrefixedPath = PIWIK_INCLUDE_PATH . '/plugins/' . $this->componentToPrefix . '/vendor/prefixed';

        $cliPhp = new CliPhp();
        $phpBinary = $cliPhp->findPhpBinary();

        $env = 'MATOMO_NAMESPACES_TO_PREFIX="' . addslashes(json_encode($this->coreNamespacesToPrefix)) . '"';
        $command = 'cd ' . $pluginPrefixedPath . ' && ' . $env . ' ' . $phpBinary . ' ' . $this->pathToPhpScoper
            . ' add --force --output-dir=../prefixed2 --config=' . PIWIK_INCLUDE_PATH . '/core-refs.scoper.inc.php';

        $this->logger->debug('php-scoper command for core dependency refs in plugins: {command}', ['command' => $command]);

        return $command;
    }

    private function collectChildDependencies()
    {
        $dependenciesToProcess = $this->dependenciesToPrefix;
        while (!empty($dependenciesToProcess)) {
            $dependency = array_shift($dependenciesToProcess);

            $dependencyComposerJson = $this->vendorPath . '/' . $dependency . '/composer.json';
            if (!is_file($dependencyComposerJson)) {
                continue;
            }

            $dependencyComposerJson = json_decode(file_get_contents($dependencyComposerJson), true);
            if (!empty($dependencyComposerJson['autoload']['psr-4'])) { // only handling psr-4 for now
                $this->namespacesToInclude = array_merge(
                    $this->namespacesToInclude,
                    array_keys($dependencyComposerJson['autoload']['psr-4']),
                );
            }

            if (empty($dependencyComposerJson['require'])) {
                continue;
            }

            foreach ($dependencyComposerJson['require'] as $name => $ignore) {
                if (!$this->isDependencyExists($name)
                    || in_array($name, $this->dependenciesToPrefix)
                ) {
                    continue;
                }

                $this->dependenciesToPrefix[] = $name;
                $dependenciesToProcess[] = $name;
            }
        }
    }

    private function collectCoreNamespacesToPrefix()
    {
        $corePrefixedPath = PIWIK_VENDOR_PATH . '/prefixed';
        foreach (scandir($corePrefixedPath) as $orgName) {
            if ($orgName === '.'
                || $orgName === '..'
                || $orgName === 'composer'
                || !is_dir("$corePrefixedPath/$orgName")
            ) {
                continue;
            }

            $orgPath = "$corePrefixedPath/$orgName";
            foreach (scandir($orgPath) as $depName) {
                if ($depName === '.'
                    || $depName === '..'
                    || !is_dir("$orgPath/$depName")
                ) {
                    continue;
                }

                $composerJsonPath = "$orgPath/composer.json";
                if (!is_file($composerJsonPath)) {
                    continue;
                }

                // if the dependency also exists in the plugin and we're prefixing it, don't use the prefixed
                // core dependency, use the plugin one.
                if (in_array("$orgName/$depName", $this->dependenciesToPrefix)) {
                    continue;
                }

                $composerJson = json_decode(file_get_contents($composerJsonPath), true);
                if (!empty($composerJson['autoload']['psr-4'])) { // only handling psr-4 for now
                    $this->coreNamespacesToPrefix = array_merge(
                        $this->coreNamespacesToPrefix,
                        array_keys($composerJson['autoload']['psr-4']),
                    );
                }
            }
        }
    }

    private function isDependencyExists($name)
    {
        return is_dir($this->vendorPath . '/' . $name);
    }

    public function getDependenciesToPrefix()
    {
        return $this->dependenciesToPrefix;
    }
}