<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugin;

use Composer\Semver\VersionParser;
use Piwik\Plugin\Manager as PluginManager;
use Piwik\Plugins\Marketplace\Environment;
use Piwik\Version;

/**
 *
 */
class Dependency
{
    private $piwikVersion;
    private $phpVersion;

    public function __construct()
    {
        $this->setPiwikVersion(Version::VERSION);
        $this->setPhpVersion(PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION . '.' . PHP_RELEASE_VERSION);
    }

    public function setEnvironment(Environment $environment)
    {
        $this->setPiwikVersion($environment->getPiwikVersion());
        $this->setPhpVersion($environment->getPhpVersion());
    }

    public function getMissingDependencies($requires)
    {
        $missingRequirements = array();

        if (empty($requires)) {
            return $missingRequirements;
        }

        foreach ($requires as $name => $requiredVersion) {
            $currentVersion  = $this->getCurrentVersion($name);

            if (in_array(strtolower($name), ['piwik', 'matomo'])) {
                $requiredVersion = $this->markPluginsWithoutUpperBoundMatomoRequirementAsIncompatible($requiredVersion);
            }

            $missingVersions = $this->getMissingVersions($currentVersion, $requiredVersion);

            if (!empty($missingVersions)) {
                $missingRequirements[] = array(
                    'requirement'     => $name,
                    'actualVersion'   => $currentVersion,
                    'requiredVersion' => $requiredVersion,
                    'causedBy'        => implode(', ', $missingVersions)
                );
            }
        }

        return $missingRequirements;
    }

    public function getMissingVersions($currentVersion, $requiredVersion)
    {
        $currentVersion = trim($currentVersion ?? '');

        $missingVersions = array();

        if (empty($currentVersion)) {
            if (!empty($requiredVersion)) {
                $missingVersions[] = (string) $requiredVersion;
            }

            return $missingVersions;
        }

        $requiredVersion = $this->makeVersionBackwardsCompatibleIfNoComparisonDefined($requiredVersion);

        $version = new VersionParser();
        $constraintsExisting = $version->parseConstraints($currentVersion);

        $requiredVersions = explode(',', (string) $requiredVersion);

        foreach ($requiredVersions as $required) {
            $required = trim($required);

            if (empty($required)) {
                continue;
            }

            $required = $this->makeVersionBackwardsCompatibleIfNoComparisonDefined($required);
            $constraintRequired = $version->parseConstraints($required);

            if (!$constraintRequired->matches($constraintsExisting)) {
                $missingVersions[] = $required;
            }
        }

        return $missingVersions;
    }

    /**
     * Upon Matomo 4 we require a lower and upper version bound for Matomo to be set in plugin.json
     * If that is not the case we assume the plugin not to be compatible with Matomo 4
     *
     * @param string $requiredVersion
     * @return string
     */
    private function markPluginsWithoutUpperBoundMatomoRequirementAsIncompatible($requiredVersion)
    {
        if (strpos($requiredVersion, ',') !== false) {
            return $requiredVersion;
        }

        $minVersion = str_replace(array('>', '=', '<', '!', '~', '^'), '', $requiredVersion);
        if (preg_match("/^\<=?\d/", $requiredVersion)) {
            $upperLimit = '>=' . $minVersion[0] . '.0.0-b1,' . $requiredVersion;
        } else if (!empty($minVersion) && is_numeric($minVersion[0])) {
            $upperLimit = $requiredVersion . ',<' . ($minVersion[0] + 1) . '.0.0-b1';
        } else {
            $upperLimit = '>=4.0.0-b1,<5.0.0-b1';
        }

        return $upperLimit;
    }

    private function makeVersionBackwardsCompatibleIfNoComparisonDefined($version)
    {
        if (!empty($version) && preg_match('/^(\d+)\.(\d+)/', $version)) {
            // TODO: we should remove this from piwik 3. To stay BC we add >= if no >= is defined yet
            $version = '>=' . $version;
        }

        return $version;
    }

    public function setPiwikVersion($piwikVersion)
    {
        $this->piwikVersion = $piwikVersion;
    }

    public function setPhpVersion($phpVersion)
    {
        $this->phpVersion = $phpVersion;
    }

    public function hasDependencyToDisabledPlugin($requires)
    {
        if (empty($requires)) {
            return false;
        }

        foreach ($requires as $name => $requiredVersion) {
            $nameLower = strtolower($name);
            $isPluginRequire = !in_array($nameLower, array('piwik', 'php', 'matomo'));
            if ($isPluginRequire) {
                // we do not check version, only whether it's activated. Everything that is not piwik or php is assumed
                // a plugin so far.
                if (!PluginManager::getInstance()->isPluginActivated($name)) {
                    return true;
                }
            }
        }

        return false;
    }

    private function getCurrentVersion($name)
    {
        switch (strtolower($name)) {
            case 'matomo':
            case 'piwik':
                return $this->piwikVersion;
            case 'php':
                return $this->phpVersion;
            default:
                try {
                    $pluginNames = PluginManager::getAllPluginsNames();

                    if (!in_array($name, $pluginNames) || !PluginManager::getInstance()->isPluginLoaded($name)) {
                        return '';
                    }

                    $plugin = PluginManager::getInstance()->loadPlugin(ucfirst($name));

                    if (!empty($plugin)) {
                        return $plugin->getVersion();
                    }
                } catch (\Exception $e) {
                }
        }

        return '';
    }


}
