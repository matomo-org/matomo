<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugin;

use Piwik\Plugin\Manager as PluginManager;
use Piwik\Version;

/**
 *
 */
class Dependency
{
    private $piwikVersion;

    public function __construct()
    {
        $this->setPiwikVersion(Version::VERSION);
    }

    public function getMissingDependencies($requires)
    {
        $missingRequirements = array();

        if (empty($requires)) {
            return $missingRequirements;
        }

        foreach ($requires as $name => $requiredVersion) {
            $currentVersion  = $this->getCurrentVersion($name);
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
        $currentVersion   = trim($currentVersion);
        $requiredVersions = explode(',' , (string) $requiredVersion);

        $missingVersions = array();

        foreach ($requiredVersions as $required) {

            $comparison = '>=';
            $required   = trim($required);

            if (preg_match('{^(<>|!=|>=?|<=?|==?)\s*(.*)}', $required, $matches)) {
                $required   = $matches[2];
                $comparison = trim($matches[1]);
            }

            if (false === version_compare($currentVersion, $required, $comparison)) {
                $missingVersions[] = $comparison . $required;
            }
        }

        return $missingVersions;
    }

    public function setPiwikVersion($piwikVersion)
    {
        $this->piwikVersion = $piwikVersion;
    }

    private function getCurrentVersion($name)
    {
        switch (strtolower($name)) {
            case 'piwik':
                return $this->piwikVersion;
            case 'php':
                return PHP_VERSION;
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
                } catch (\Exception $e) {}
        }

        return '';
    }
}
