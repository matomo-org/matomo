<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Measurable\Type;

use Piwik\Container\StaticContainer;
use Piwik\Plugin\Manager as PluginManager;
use Piwik\Measurable\Type;

class TypeManager
{
    /**
     * @return Type[]
     */
    public function getAllTypes()
    {
        $components = PluginManager::getInstance()->findComponents('Type', '\\Piwik\\Measurable\\Type');

        $instances = array();
        foreach ($components as $component) {
            $instances[] = StaticContainer::get($component);
        }

        return $instances;
    }

    public function isExistingType($typeId)
    {
        foreach ($this->getAllTypes() as $type) {
            if ($type->getId() === $typeId) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $typeId
     * @return Type|null
     */
    public function getType($typeId)
    {
        foreach ($this->getAllTypes() as $type) {
            if ($type->getId() === $typeId) {
                return $type;
            }
        }

        return new Type();
    }
}

