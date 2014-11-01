<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Concurrency;
use Piwik\Common;
use Piwik\Db;
use Piwik\Option;

/**
 * Limited atomic list structure implemented in MySQL.
 *
 * This class uses an Option entry to manage a list of PHP objects in MySQL. The option data
 * is a list of serialized PHP objects separated by a delimiter (see {@link ITEM_DELIMITER}).
 * The delimiter is stored in between serialized objects, and once more after the list for a
 * simpler implementation. (eg `"myserializeddata\0\0myotherserializedata\0\0"`).
 *
 * No locks are used in maintaining atomicity.
 */
class AtomicList
{
    const OPTION_PREFIX = 'AtomicList.';
    const ITEM_DELIMITER = "\0\0";

    /**
     * The name of the distributed list. Allows different processes to access same list
     * by using same name.
     *
     * @var string
     */
    private $name;

    /**
     * Constructor.
     *
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * Adds one or more items to the list in MySQL. This operation is atomic.
     *
     * Objects are serialized before being added to MySQL.
     *
     * @param array $items
     */
    public function push($items)
    {
        $itemsString = $this->getItemsStringFor($items);
        $this->appendToOption($itemsString);
    }

    /**
     * Inserts one or more items before the beginning of the list in MySQL. This operation is atomic.
     *
     * Objects are serialized before being added to MySQL.
     *
     * @param array $items
     */
    public function unshift($items)
    {
        $itemsString = $this->getItemsStringFor($items);
        $this->prependToOption($itemsString);
    }

    /**
     * Removes `$n` items from the end of the list in MySQL and returns them deserialized. This
     * operation is atomic.
     *
     * @param int $n The number of elements to pop.
     * @return array
     */
    public function pop($n)
    {
        if ($n <= 0) {
            return array();
        }

        $itemsString = $this->removeAndGetFromOptionEnd($n);
        return $this->getItemsArrayFromString($itemsString);
    }

    /**
     * Removes `$n` items from the beginning of the list in MySQL and returns them deserialized. This
     * operation is atomic.
     *
     * @param int $n The number of elements to pull.
     * @return array
     */
    public function pull($n)
    {
        if ($n <= 0) {
            return array();
        }

        $itemsString = $this->removeAndGetFromOptionStart($n);
        return $this->getItemsArrayFromString($itemsString);
    }

    /**
     * Returns the items currently in the array in MySQL. This operation is atomic, however, the list
     * can be changed between the time the option is read from the DB and the data is unserialized and
     * returned.
     *
     * Items are deserialized before being returned.
     *
     * @return array
     */
    public function getAll()
    {
        $itemsString = Option::get($this->getOptionName(), $useCache = false);
        return $this->getItemsArrayFromString($itemsString);
    }

    /**
     * Deletes the option used to store the list.
     */
    public function clear()
    {
        Option::delete($this->getOptionName());
    }

    private function getItemsStringFor($items)
    {
        $itemsString = "";
        foreach ($items as $item) {
            $itemsString .= serialize($item) . self::ITEM_DELIMITER;
        }
        return $itemsString;
    }

    private function getItemsArrayFromString($itemsString)
    {
        $itemsString = trim($itemsString);
        if (empty($itemsString)) {
            return array();
        }

        $items = explode(self::ITEM_DELIMITER, $itemsString);

        $result = array();
        foreach ($items as $idx => $item) {
            if (empty($item)) {
                $result[] = null;
            } else {
                $result[] = unserialize($item);
            }
        }
        return $result;
    }

    private function appendToOption($itemsString)
    {
        $optionTable = $this->getTableName();
        $optionName = $this->getOptionName();

        $sql = "INSERT INTO `$optionTable` (option_name, option_value) VALUES (?, ?)
           ON DUPLICATE KEY
                     UPDATE option_value = CONCAT(option_value, ?)";
        Db::query($sql, array($optionName, $itemsString, $itemsString));
    }

    private function prependToOption($itemsString)
    {
        $optionTable = $this->getTableName();
        $optionName = $this->getOptionName();

        $sql = "INSERT INTO `$optionTable` (option_name, option_value) VALUES (?, ?)
           ON DUPLICATE KEY
                     UPDATE option_value = CONCAT(?, option_value)";
        Db::query($sql, array($optionName, $itemsString, $itemsString));
    }

    private function removeAndGetFromOptionEnd($n)
    {
        $optionTable = $this->getTableName();
        $optionName = $this->getOptionName();

        list($getItemCountSql, $getItemCountBind) = $this->getItemCountSql();
        list($getLimitedItemIndexSql, $getLimitedItemIndexBind) = $this->getLimitedItemIndexSql($n);

        $setRemainingOptionSql = "@remainingOption := SUBSTRING_INDEX((@wholeOption := option_value), ?, $getItemCountSql - $getLimitedItemIndexSql)";

        $sql = "UPDATE `$optionTable`
                   SET option_value = IF((@remainingLength := LENGTH($setRemainingOptionSql)) <> 0, CONCAT(@remainingOption, ?),\"\")
                 WHERE option_name = ?";
        $bind = array_merge(array(self::ITEM_DELIMITER), $getItemCountBind, $getLimitedItemIndexBind, array(self::ITEM_DELIMITER, $optionName));

        Db::query($sql, $bind);

        $vars = Db::fetchRow("SELECT @wholeOption, @remainingLength");
        return substr($vars['@wholeOption'], $vars['@remainingLength']);
    }

    private function removeAndGetFromOptionStart($n)
    {
        $optionTable = $this->getTableName();
        $optionName = $this->getOptionName();

        list($getItemCountSql, $getItemCountBind) = $this->getItemCountSql();
        list($getLimitedItemIndexSql, $getLimitedItemIndexBind) = $this->getLimitedItemIndexSql($n);

        $setRemainingOptionSql = "@remainingOption := SUBSTRING_INDEX((@wholeOption := option_value), ?, -($getItemCountSql - $getLimitedItemIndexSql) - "
                               . strlen(self::ITEM_DELIMITER) . ")";

        $sql = "UPDATE `$optionTable`
                   SET option_value = IF((@remainingLength := LENGTH($setRemainingOptionSql)) = 0, CONCAT(@remainingOption, ?), @remainingOption)
                 WHERE option_name = ?";
        $bind = array_merge(array(self::ITEM_DELIMITER), $getItemCountBind, $getLimitedItemIndexBind, array(self::ITEM_DELIMITER, $optionName));

        Db::query($sql, $bind);

        $vars = Db::fetchRow("SELECT @wholeOption, @remainingLength");
        return substr($vars['@wholeOption'], 0, strlen($vars['@wholeOption']) - $vars['@remainingLength']);
    }

    private function getItemCountSql()
    {
        $sql = "(@itemCount := (LENGTH(@wholeOption) - LENGTH(REPLACE(@wholeOption, ?, \"\"))) / "
             . strlen(self::ITEM_DELIMITER) . ")";
        return array($sql, array(self::ITEM_DELIMITER));
    }

    private function getLimitedItemIndexSql($n)
    {
        return array("IF(? > @itemCount, @itemCount, ?)", array($n, $n));
    }

    private function getOptionName()
    {
        return self::OPTION_PREFIX . $this->name;
    }

    private function getTableName()
    {
        return Common::prefixTable('option');
    }
}