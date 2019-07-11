<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Access;

use Exception;
use Piwik\CacheId;
use Piwik\Piwik;
use Piwik\Cache as PiwikCache;

class CapabilitiesProvider
{
    /**
     * @return Capability[]
     */
    public function getAllCapabilities()
    {
        $cacheId = CacheId::siteAware(CacheId::languageAware('Capabilities'));
        $cache   = PiwikCache::getTransientCache();

        if (!$cache->contains($cacheId)) {
            $capabilities = array();

            /**
             * Triggered to add new capabilities.
             *
             * **Example**
             *
             *     public function addCapabilities(&$capabilities)
             *     {
             *         $capabilities[] = new MyNewCapabilitiy();
             *     }
             *
             * @param Capability[] $reports An array of reports
             * @internal
             */
            Piwik::postEvent('Access.Capability.addCapabilities', array(&$capabilities));

            /**
             * Triggered to filter / restrict capabilities.
             *
             * **Example**
             *
             *     public function filterCapabilities(&$capabilities)
             *     {
             *         foreach ($capabilities as $index => $capability) {
             *              if ($capability->getId() === 'tagmanager_write') {}
             *                  unset($capabilities[$index]); // remove the given capability
             *              }
             *         }
             *     }
             *
             * @param Capability[] $reports An array of reports
             * @internal
             */
            Piwik::postEvent('Access.Capability.filterCapabilities', array(&$capabilities));

            $capabilities = array_values($capabilities);

            $this->checkCapabilityIds($capabilities);

            $cache->save($cacheId, $capabilities);
            return $capabilities;
        }

        return $cache->fetch($cacheId);
    }

    /**
     * @param $capabilityId
     * @return Capability|null
     */
    public function getCapability($capabilityId)
    {
        foreach ($this->getAllCapabilities() as $capability) {
            if ($capabilityId === $capability->getId()) {
                return $capability;
            }
        }
    }

    public function getAllCapabilityIds()
    {
        $ids = array();
        foreach ($this->getAllCapabilities() as $capability) {
            $ids[] = $capability->getId();
        }
        return $ids;
    }

    public function isValidCapability($capabilityId)
    {
        $capabilities = $this->getAllCapabilityIds();

        return in_array($capabilityId, $capabilities, true);
    }

    public function checkValidCapability($capabilityId)
    {
        if (!$this->isValidCapability($capabilityId)) {
            $capabilities = $this->getAllCapabilityIds();
            throw new Exception(Piwik::translate("UsersManager_ExceptionAccessValues", implode(", ", $capabilities)));
        }
    }

    /**
     * @param Capability[] $capabilities
     */
    private function checkCapabilityIds($capabilities)
    {
        foreach ($capabilities as $capability) {
            $id = $capability->getId();
            if (preg_match('/[^a-zA-Z0-9_-]/', $id)) {
                throw new \Exception("Capability with invalid ID found: '$id'. Valid characters are 'a-zA-Z0-9_-'.");
            }
        }
    }
}
