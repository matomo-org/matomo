<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Measurable\Type;

use Piwik\Plugin\Manager as PluginManager;
use Piwik\Measurable\Type;

class TypeManager
{
    /**
     * @return Type[]
     */
    public function getAllTypes()
    {
        return PluginManager::getInstance()->findComponents('Type', '\\Piwik\\Measurable\\Type');
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

