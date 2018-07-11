<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Access;

use Piwik\Access\Capability\PublishLiveContainer;
use Piwik\Access\Capability\TagManagerWrite;
use Piwik\Access\Capability\UseCustomTemplates;
use Exception;
use Piwik\Piwik;

class CapabilitiesProvider
{
    /**
     * @return Capability[]
     */
    public function getAllCapabilities()
    {
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
         */
        Piwik::postEvent('Access.Capability.addCapabilities', array(&$capabilities));

        $capabilities[] = new TagManagerWrite();
        $capabilities[] = new PublishLiveContainer();
        $capabilities[] = new UseCustomTemplates();

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
         */
        Piwik::postEvent('Access.Capability.filterCapabilities', array(&$capabilities));

        $capabilities = array_values($capabilities);

        return $capabilities;
    }

    /**
     * @param $capabilityId
     * @return Capability|null
     */
    public function getCapability($capabilityId)
    {
        $ids = array();
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
}
