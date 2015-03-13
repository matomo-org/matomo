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
 * Manages a simple distributed list stored in an Option. No locking occurs, so the list
 * is not thread safe, and should only be used for use cases where atomicity is not
 * important.
 *
 * The list of items is serialized and stored in an Option. Items are converted to string
 * before being persisted, so it is not expected to unserialize objects.
 */
class DistributedList
{
    /**
     * The name of the option to store the list in.
     *
     * @var string
     */
    private $optionName;

    /**
     * Constructor.
     *
     * @param string $optionName
     */
    public function __construct($optionName)
    {
        $this->optionName = $optionName;
    }

    /**
     * Queries the option table and returns all items in this list.
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
     * Sets the contents of the list in the option table.
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
     * Adds one or more items to the list in the option table.
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
     * Removes one or more items by value from the list in the option table.
     *
     * Does not preserve array keys.
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

        $this->setAll(array_values($allItems));
    }

    /**
     * Removes one or more items by index from the list in the option table.
     *
     * Does not preserve array keys.
     *
     * @param int[]|int $indices
     */
    public function removeByIndex($indices)
    {
        if (!is_array($indices)) {
            $indices = array($indices);
        }

        $indices = array_unique($indices);

        $allItems = $this->getAll();
        foreach ($indices as $index) {
            unset($allItems[$index]);
        }

        $this->setAll(array_values($allItems));
    }
}