<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Concurrency;

use Piwik\Option;

/**
 * TODO
 *
 * TODO: tests
 */
class DistributedList
{
    /**
     * TODO
     *
     * @var string
     */
    private $optionName;

    /**
     * TODO
     */
    public function __construct($optionName)
    {
        $this->optionName = $optionName;
    }

    /**
     * TODO
     *
     * @return array
     */
    public function getAll()
    {
        Option::clearCachedOption($this->optionName);
        $array = Option::get($this->optionName);

        if ($array
            && ($array = unserialize($array))
            && count($array)
        ) {
            return $array;
        }
        return array();
    }

    /**
     * TODO
     *
     * @param string[] $items
     */
    public function setAll($items)
    {
        foreach ($items as &$item) {
            $item = (string)$item;
        }

        Option::set($this->optionName, serialize($items));
    }

    /**
     * TODO
     *
     * @param string|array $item
     */
    public function add($item)
    {
        $allItems = $this->getAll();
        if (is_array($item)) {
            $allItems = array_merge($allItems, $item);
        } else {
            $allItems[] = $item;
        }

        $this->setAll($allItems);
    }

    /**
     * TODO
     * TODO: support removing multiple
     *
     * @param string|array $items
     */
    public function remove($items)
    {
        if (!is_array($items)) {
            $items = array($items);
        }

        $allItems = $this->getAll();

        foreach ($items as $item) {
            $existingIndex = array_search($item, $allItems);
            if ($existingIndex === false) {
                return;
            }

            unset($allItems[$existingIndex]);
        }

        $this->setAll($allItems);
    }
}