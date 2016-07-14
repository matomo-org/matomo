<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Tracker;

use Piwik\Common;
use Exception;

class EcommerceItems
{

    // In the GET items parameter, each item has the following array of information
    const MAXIMUM_PRODUCT_CATEGORIES = 5;
    const INDEX_ITEM_SKU = 0;
    const INDEX_ITEM_NAME = 1;
    const INDEX_ITEM_CATEGORY = 2;
    const INDEX_ITEM_PRICE = 3;
    const INDEX_ITEM_QUANTITY = 4;

    // Used in the array of items, internally to this class
    const INTERNAL_ITEM_SKU = 0;
    const INTERNAL_ITEM_NAME = 1;
    const INTERNAL_ITEM_CATEGORY = 2;
    const INTERNAL_ITEM_CATEGORY2 = 3;
    const INTERNAL_ITEM_CATEGORY3 = 4;
    const INTERNAL_ITEM_CATEGORY4 = 5;
    const INTERNAL_ITEM_CATEGORY5 = 6;
    const INTERNAL_ITEM_PRICE = 7;
    const INTERNAL_ITEM_QUANTITY = 8;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function isItemsInRequestInvalid()
    {
        return $this->getEcommerceItemsFromRequest() === false;
    }

    public function getItemsCount()
    {
        $items = $this->getEcommerceItemsFromRequest();

        if(!is_array($items)) {
            throw new Exception("getItemsCount expected a valid array of ecommerce items.");
        }

        $itemsCount = 0;
        foreach ($items as $item) {
            $itemsCount += $item[self::INTERNAL_ITEM_QUANTITY];
        }
        return $itemsCount;
    }

    /**
     * Loads the Ecommerce items from the request and records them in the DB
     *
     * @param array $goal
     * @param array $items
     * @throws Exception
     * @return int Number of items in the cart
     */
    public function recordEcommerceItems($goal)
    {
        $items = $this->getEcommerceItemsFromRequest();

        $itemInCartBySku = array();
        foreach ($items as $item) {
            $itemInCartBySku[$item[0]] = $item;
        }

        $itemsInDb = $this->getModel()->getAllItemsCurrentlyInTheCart($goal, GoalManager::ITEM_IDORDER_ABANDONED_CART);

        // Look at which items need to be deleted, which need to be added or updated, based on the SKU
        $skuFoundInDb = $itemsToUpdate = array();

        foreach ($itemsInDb as $itemInDb) {
            $skuFoundInDb[] = $itemInDb['idaction_sku'];

            // Ensure price comparisons will have the same assumption
            $itemInDb['price'] = self::getRevenue($itemInDb['price']);
            $itemInDbOriginal = $itemInDb;
            $itemInDb = array_values($itemInDb);

            // Cast all as string, because what comes out of the fetchAll() are strings
            $itemInDb = self::getItemRowCast($itemInDb);

            //Item in the cart in the DB, but not anymore in the cart
            if (!isset($itemInCartBySku[$itemInDb[0]])) {
                $itemToUpdate = array_merge($itemInDb,
                    array('deleted'                => 1,
                        'idorder_original_value' => $itemInDbOriginal['idorder_original_value']
                    )
                );

                $itemsToUpdate[] = $itemToUpdate;
                Common::printDebug("Item found in the previous Cart, but no in the current cart/order");
                Common::printDebug($itemToUpdate);
                continue;
            }

            $newItem = $itemInCartBySku[$itemInDb[0]];
            $newItem = self::getItemRowCast($newItem);

            if (count($itemInDb) != count($newItem)) {
                Common::printDebug("ERROR: Different format in items from cart and DB");
                throw new Exception(" Item in DB and Item in cart have a different format, this is not expected... " . var_export($itemInDb, true) . var_export($newItem, true));
            }
            Common::printDebug("Item has changed since the last cart. Previous item stored in cart in database:");
            Common::printDebug($itemInDb);
            Common::printDebug("New item to UPDATE the previous row:");
            $newItem['idorder_original_value'] = $itemInDbOriginal['idorder_original_value'];
            Common::printDebug($newItem);
            $itemsToUpdate[] = $newItem;
        }

        // Items to UPDATE
        $this->updateEcommerceItems($goal, $itemsToUpdate);

        // Items to INSERT
        $itemsToInsert = array();
        foreach ($items as $item) {
            if (!in_array($item[0], $skuFoundInDb)) {
                $itemsToInsert[] = $item;
            }
        }

        $this->insertEcommerceItems($goal, $itemsToInsert);
    }



    /**
     * Inserts in the cart in the DB the new items
     * that were not previously in the cart
     *
     * @param array $goal
     * @param array $itemsToInsert
     *
     * @return void
     */
    protected function insertEcommerceItems($goal, $itemsToInsert)
    {
        if (empty($itemsToInsert)) {
            return;
        }

        Common::printDebug("Ecommerce items that are added to the cart/order");
        Common::printDebug($itemsToInsert);

        $items = array();

        foreach ($itemsToInsert as $item) {
            $items[] = self::getItemRowEnriched($goal, $item);
        }

        $this->getModel()->createEcommerceItems($items);
    }


    /**
     * Updates the cart items in the DB
     * that have been modified since the last cart update
     *
     * @param array $goal
     * @param array $itemsToUpdate
     *
     * @return void
     */
    protected function updateEcommerceItems($goal, $itemsToUpdate)
    {
        if (empty($itemsToUpdate)) {
            return;
        }

        Common::printDebug("Goal data used to update ecommerce items:");
        Common::printDebug($goal);

        foreach ($itemsToUpdate as $item) {
            $newRow = self::getItemRowEnriched($goal, $item);
            Common::printDebug($newRow);

            $this->getModel()->updateEcommerceItem($item['idorder_original_value'], $newRow);
        }
    }

    /**
     * Returns Items read from the request string
     *
     * @return array|bool
     */
    protected function getEcommerceItemsFromRequest()
    {
        $items = $this->request->getParam('ec_items');

        if (empty($items)) {
            Common::printDebug("There are no Ecommerce items in the request");
            // we still record an Ecommerce order without any item in it
            return array();
        }

        if (!is_array($items)) {
            Common::printDebug("Error while json_decode the Ecommerce items = " . var_export($items, true));
            return false;
        }

        $items = Common::unsanitizeInputValues($items);

        return self::getCleanedEcommerceItems($items);
    }


    /**
     * Returns rounded decimal revenue, or if revenue is integer, then returns as is.
     *
     * @param int|float $revenue
     * @return int|float
     */
    protected static function getRevenue($revenue)
    {
        if (round($revenue) != $revenue) {
            $revenue = round($revenue, GoalManager::REVENUE_PRECISION);
        }

        $revenue = Common::forceDotAsSeparatorForDecimalPoint($revenue);

        return $revenue;
    }

    /**
     * Reads items from the request, then looks up the names from the lookup table
     * and returns a clean array of items ready for the database.
     *
     * @param array $items
     * @return array $cleanedItems
     */
    protected static function getCleanedEcommerceItems($items)
    {
        // Clean up the items array
        $cleanedItems = array();
        foreach ($items as $item) {
            $name = $category = $category2 = $category3 = $category4 = $category5 = false;
            $price = 0;
            $quantity = 1;

            // items are passed in the request as an array: ( $sku, $name, $category, $price, $quantity )
            if (empty($item[self::INDEX_ITEM_SKU])) {
                continue;
            }

            $sku = $item[self::INDEX_ITEM_SKU];
            if (!empty($item[self::INDEX_ITEM_NAME])) {
                $name = $item[self::INDEX_ITEM_NAME];
            }

            if (!empty($item[self::INDEX_ITEM_CATEGORY])) {
                $category = $item[self::INDEX_ITEM_CATEGORY];
            }

            if (isset($item[self::INDEX_ITEM_PRICE])
                && is_numeric($item[self::INDEX_ITEM_PRICE])
            ) {
                $price = self::getRevenue($item[self::INDEX_ITEM_PRICE]);
            }
            if (!empty($item[self::INDEX_ITEM_QUANTITY])
                && is_numeric($item[self::INDEX_ITEM_QUANTITY])
            ) {
                $quantity = (int)$item[self::INDEX_ITEM_QUANTITY];
            }

            // self::INDEX_ITEM_* are in order
            $cleanedItems[] = array(
                self::INTERNAL_ITEM_SKU => $sku,
                self::INTERNAL_ITEM_NAME => $name,
                self::INTERNAL_ITEM_CATEGORY => $category,
                self::INTERNAL_ITEM_CATEGORY2 => $category2,
                self::INTERNAL_ITEM_CATEGORY3 => $category3,
                self::INTERNAL_ITEM_CATEGORY4 => $category4,
                self::INTERNAL_ITEM_CATEGORY5 => $category5,
                self::INTERNAL_ITEM_PRICE => $price,
                self::INTERNAL_ITEM_QUANTITY => $quantity
            );
        }

        // Lookup Item SKUs, Names & Categories Ids
        $actionsToLookupAllItems = array();

        // Each item has 7 potential "ids" to lookup in the lookup table
        $columnsInEachRow = 1 + 1 + self::MAXIMUM_PRODUCT_CATEGORIES;

        foreach ($cleanedItems as $item) {
            $actionsToLookup = array();
            list($sku, $name, $category, $price, $quantity) = $item;
            $actionsToLookup[] = array(trim($sku), Action::TYPE_ECOMMERCE_ITEM_SKU);
            $actionsToLookup[] = array(trim($name), Action::TYPE_ECOMMERCE_ITEM_NAME);

            // Only one category
            if (!is_array($category)) {
                $actionsToLookup[] = array(trim($category), Action::TYPE_ECOMMERCE_ITEM_CATEGORY);
            } // Multiple categories
            else {
                $countCategories = 0;
                foreach ($category as $productCategory) {
                    $productCategory = trim($productCategory);
                    if (empty($productCategory)) {
                        continue;
                    }
                    $countCategories++;
                    if ($countCategories > self::MAXIMUM_PRODUCT_CATEGORIES) {
                        break;
                    }
                    $actionsToLookup[] = array($productCategory, Action::TYPE_ECOMMERCE_ITEM_CATEGORY);
                }
            }
            // Ensure that each row has the same number of columns, fill in the blanks
            for ($i = count($actionsToLookup); $i < $columnsInEachRow; $i++) {
                $actionsToLookup[] = array(false, Action::TYPE_ECOMMERCE_ITEM_CATEGORY);
            }
            $actionsToLookupAllItems = array_merge($actionsToLookupAllItems, $actionsToLookup);
        }

        $actionsLookedUp = TableLogAction::loadIdsAction($actionsToLookupAllItems);

        // Replace SKU, name & category by their ID action
        foreach ($cleanedItems as $index => &$item) {
            // SKU
            $item[0] = $actionsLookedUp[$index * $columnsInEachRow + 0];
            // Name
            $item[1] = $actionsLookedUp[$index * $columnsInEachRow + 1];
            // Categories
            $item[2] = $actionsLookedUp[$index * $columnsInEachRow + 2];
            $item[3] = $actionsLookedUp[$index * $columnsInEachRow + 3];
            $item[4] = $actionsLookedUp[$index * $columnsInEachRow + 4];
            $item[5] = $actionsLookedUp[$index * $columnsInEachRow + 5];
            $item[6] = $actionsLookedUp[$index * $columnsInEachRow + 6];
        }

        return $cleanedItems;
    }

    protected static function getItemRowEnriched($goal, $item)
    {
        $newRow = array(
            'idaction_sku' => (int)$item[self::INTERNAL_ITEM_SKU],
            'idaction_name' => (int)$item[self::INTERNAL_ITEM_NAME],
            'idaction_category' => (int)$item[self::INTERNAL_ITEM_CATEGORY],
            'idaction_category2' => (int)$item[self::INTERNAL_ITEM_CATEGORY2],
            'idaction_category3' => (int)$item[self::INTERNAL_ITEM_CATEGORY3],
            'idaction_category4' => (int)$item[self::INTERNAL_ITEM_CATEGORY4],
            'idaction_category5' => (int)$item[self::INTERNAL_ITEM_CATEGORY5],
            'price' => Common::forceDotAsSeparatorForDecimalPoint($item[self::INTERNAL_ITEM_PRICE]),
            'quantity' => $item[self::INTERNAL_ITEM_QUANTITY],
            'deleted' => isset($item['deleted']) ? $item['deleted'] : 0,
            //deleted
            'idorder' => isset($goal['idorder']) ? $goal['idorder'] : GoalManager::ITEM_IDORDER_ABANDONED_CART,
            //idorder = 0 in log_conversion_item for carts
            'idsite' => $goal['idsite'],
            'idvisitor' => $goal['idvisitor'],
            'server_time' => $goal['server_time'],
            'idvisit' => $goal['idvisit']
        );
        return $newRow;
    }

    /**
     * Casts the item array so that array comparisons work nicely
     * @param array $row
     * @return array
     */
    public static function getItemRowCast($row)
    {
        return array(
            (string)(int)$row[self::INTERNAL_ITEM_SKU],
            (string)(int)$row[self::INTERNAL_ITEM_NAME],
            (string)(int)$row[self::INTERNAL_ITEM_CATEGORY],
            (string)(int)$row[self::INTERNAL_ITEM_CATEGORY2],
            (string)(int)$row[self::INTERNAL_ITEM_CATEGORY3],
            (string)(int)$row[self::INTERNAL_ITEM_CATEGORY4],
            (string)(int)$row[self::INTERNAL_ITEM_CATEGORY5],
            (string)$row[self::INTERNAL_ITEM_PRICE],
            (string)$row[self::INTERNAL_ITEM_QUANTITY],
        );
    }


    private function getModel()
    {
        return new Model();
    }
}
