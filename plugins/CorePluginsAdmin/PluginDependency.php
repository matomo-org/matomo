<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package CorePluginsAdmin
 */
namespace Piwik\Plugins\CorePluginsAdmin;
use Piwik\Version;

/**
 *
 * @package CorePluginsAdmin
 */
class PluginDependency
{

    public function getMissingDependencies($requires)
    {
        $missingRequirements = array();

        if (empty($requires)) {
            return $missingRequirements;
        }

        foreach ($requires as $name => $requiredVersion) {
            $currentVersion = $this->getCurrentVersion($name);
            $comparison     = '>=';

            if (preg_match('{^(<>|!=|>=?|<=?|==?)\s*(.*)}', $requiredVersion, $matches)) {
                $requiredVersion = $matches[2];
                $comparison      = $matches[1];
            }

            if (false === version_compare($currentVersion, $requiredVersion, $comparison)) {
                $missingRequirements[] = array(
                    'requirement'     => $name,
                    'actualVersion'   => $currentVersion,
                    'requiredVersion' => $comparison . $requiredVersion
                );
            }
        }

        return $missingRequirements;
    }

    private function getCurrentVersion($name)
    {
        switch (strtolower($name)) {
            case 'piwik':
                return Version::VERSION;
            case 'php':
                return PHP_VERSION;
            default:
                try {
                    $plugin = \Piwik\Plugin\Manager::getInstance()->loadPlugin(ucfirst($name));

                    if (!empty($plugin)) {
                        return $plugin->getVersion();
                    }
                } catch (\Exception $e) {}
        }
    }
}
