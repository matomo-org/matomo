<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Tracker;

use Exception;
use Piwik\Common;
use Piwik\Config;
use Piwik\Piwik;
use Piwik\Plugins\CustomVariables\CustomVariables;
use Piwik\Tracker;

/**
 */
class GoalManager
{
    // log_visit.visit_goal_buyer
    const TYPE_BUYER_NONE = 0;
    const TYPE_BUYER_ORDERED = 1;
    const TYPE_BUYER_OPEN_CART = 2;
    const TYPE_BUYER_ORDERED_AND_OPEN_CART = 3;

    // log_conversion.idorder is NULLable, but not log_conversion_item which defaults to zero for carts
    const ITEM_IDORDER_ABANDONED_CART = 0;

    // log_conversion.idgoal special values
    const IDGOAL_CART = -1;
    const IDGOAL_ORDER = 0;

    const REVENUE_PRECISION = 2;

    const MAXIMUM_PRODUCT_CATEGORIES = 5;
    public $idGoal;
    public $requestIsEcommerce;
    public $isGoalAnOrder;

    /**
     * @var Action
     */
    protected $action = null;
    protected $convertedGoals = array();
    protected $isThereExistingCartInVisit = false;
    /**
     * @var Request
     */
    protected $request;
    protected $orderId;

    /**
     * Constructor
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->init();
    }

    function init()
    {
        $this->orderId = $this->request->getParam('ec_id');
        $this->isGoalAnOrder = !empty($this->orderId);
        $this->idGoal = $this->request->getParam('idgoal');
        $this->requestIsEcommerce = ($this->idGoal == 0);
    }

    function getBuyerType($existingType = GoalManager::TYPE_BUYER_NONE)
    {
        // Was there a Cart for this visit prior to the order?
        $this->isThereExistingCartInVisit = in_array($existingType,
            array(GoalManager::TYPE_BUYER_OPEN_CART,
                  GoalManager::TYPE_BUYER_ORDERED_AND_OPEN_CART));

        if (!$this->requestIsEcommerce) {
            return $existingType;
        }
        if ($this->isGoalAnOrder) {
            return self::TYPE_BUYER_ORDERED;
        }
        // request is Add to Cart
        if ($existingType == self::TYPE_BUYER_ORDERED
            || $existingType == self::TYPE_BUYER_ORDERED_AND_OPEN_CART
        ) {
            return self::TYPE_BUYER_ORDERED_AND_OPEN_CART;
        }
        return self::TYPE_BUYER_OPEN_CART;
    }

    static public function getGoalDefinitions($idSite)
    {
        $websiteAttributes = Cache::getCacheWebsiteAttributes($idSite);
        if (isset($websiteAttributes['goals'])) {
            return $websiteAttributes['goals'];
        }
        return array();
    }

    static public function getGoalDefinition($idSite, $idGoal)
    {
        $goals = self::getGoalDefinitions($idSite);
        foreach ($goals as $goal) {
            if ($goal['idgoal'] == $idGoal) {
                return $goal;
            }
        }
        throw new Exception('Goal not found');
    }

    static public function getGoalIds($idSite)
    {
        $goals = self::getGoalDefinitions($idSite);
        $goalIds = array();
        foreach ($goals as $goal) {
            $goalIds[] = $goal['idgoal'];
        }
        return $goalIds;
    }

    /**
     * Look at the URL or Page Title and sees if it matches any existing Goal definition
     *
     * @param int $idSite
     * @param Action $action
     * @throws Exception
     * @return int Number of goals matched
     */
    function detectGoalsMatchingUrl($idSite, $action)
    {
        if (!Common::isGoalPluginEnabled()) {
            return false;
        }

        $decodedActionUrl = $action->getActionUrl();
        $actionType = $action->getActionType();
        $goals = $this->getGoalDefinitions($idSite);
        foreach ($goals as $goal) {
            $attribute = $goal['match_attribute'];
            // if the attribute to match is not the type of the current action
            if (   (($attribute == 'url' || $attribute == 'title') && $actionType != Action::TYPE_PAGE_URL)
                || ($attribute == 'file' && $actionType != Action::TYPE_DOWNLOAD)
                || ($attribute == 'external_website' && $actionType != Action::TYPE_OUTLINK)
                || ($attribute == 'manually')
            ) {
                continue;
            }

            $url = $decodedActionUrl;
            // Matching on Page Title
            if ($attribute == 'title') {
                $url = $action->getActionName();
            }
            $pattern_type = $goal['pattern_type'];

            $match = $this->isUrlMatchingGoal($goal, $pattern_type, $url);
            if ($match) {
                $goal['url'] = $decodedActionUrl;
                $this->convertedGoals[] = $goal;
            }
        }
        return count($this->convertedGoals) > 0;
    }

    function detectGoalId($idSite)
    {
        if (!Common::isGoalPluginEnabled()) {
            return false;
        }
        $goals = $this->getGoalDefinitions($idSite);
        if (!isset($goals[$this->idGoal])) {
            return false;
        }
        $goal = $goals[$this->idGoal];

        $url = $this->request->getParam('url');
        $goal['url'] = PageUrl::excludeQueryParametersFromUrl($url, $idSite);
        $goal['revenue'] = $this->getRevenue($this->request->getGoalRevenue($goal['revenue']));
        $this->convertedGoals[] = $goal;
        return true;
    }

    /**
     * Records one or several goals matched in this request.
     *
     * @param int $idSite
     * @param array $visitorInformation
     * @param array $visitCustomVariables
     * @param Action $action
     */
    public function recordGoals($idSite, $visitorInformation, $visitCustomVariables, $action)
    {
        $referrerTimestamp = $this->request->getParam('_refts');
        $referrerUrl = $this->request->getParam('_ref');
        $referrerCampaignName = trim(urldecode($this->request->getParam('_rcn')));
        $referrerCampaignKeyword = trim(urldecode($this->request->getParam('_rck')));
        $browserLanguage = $this->request->getBrowserLanguage();

        $location_country = isset($visitorInformation['location_country'])
            ? $visitorInformation['location_country']
            : Common::getCountry(
                $browserLanguage,
                $enableLanguageToCountryGuess = Config::getInstance()->Tracker['enable_language_to_country_guess'],
                $visitorInformation['location_ip']
            );

        $goal = array(
            'idvisit'                  => $visitorInformation['idvisit'],
            'idsite'                   => $idSite,
            'idvisitor'                => $visitorInformation['idvisitor'],
            'server_time'              => Tracker::getDatetimeFromTimestamp($visitorInformation['visit_last_action_time']),
            'location_country'         => $location_country,
            'visitor_returning'        => $visitorInformation['visitor_returning'],
            'visitor_days_since_first' => $visitorInformation['visitor_days_since_first'],
            'visitor_days_since_order' => $visitorInformation['visitor_days_since_order'],
            'visitor_count_visits'     => $visitorInformation['visitor_count_visits'],
        );

        $extraLocationCols = array('location_region', 'location_city', 'location_latitude', 'location_longitude');
        foreach ($extraLocationCols as $col) {
            if (isset($visitorInformation[$col])) {
                $goal[$col] = $visitorInformation[$col];
            }
        }

        // Copy Custom Variables from Visit row to the Goal conversion
        // Otherwise, set the Custom Variables found in the cookie sent with this request
        $goal += $visitCustomVariables;
        $maxCustomVariables = CustomVariables::getMaxCustomVariables();

        for ($i = 1; $i <= $maxCustomVariables; $i++) {
            if (isset($visitorInformation['custom_var_k' . $i])
                && strlen($visitorInformation['custom_var_k' . $i])
            ) {
                $goal['custom_var_k' . $i] = $visitorInformation['custom_var_k' . $i];
            }
            if (isset($visitorInformation['custom_var_v' . $i])
                && strlen($visitorInformation['custom_var_v' . $i])
            ) {
                $goal['custom_var_v' . $i] = $visitorInformation['custom_var_v' . $i];
            }
        }

        // Attributing the correct Referrer to this conversion.
        // Priority order is as follows:
        // 0) In some cases, the campaign is not passed from the JS so we look it up from the current visit
        // 1) Campaign name/kwd parsed in the JS
        // 2) Referrer URL stored in the _ref cookie
        // 3) If no info from the cookie, attribute to the current visit referrer

        // 3) Default values: current referrer
        $type = $visitorInformation['referer_type'];
        $name = $visitorInformation['referer_name'];
        $keyword = $visitorInformation['referer_keyword'];
        $time = $visitorInformation['visit_first_action_time'];

        // 0) In some (unknown!?) cases the campaign is not found in the attribution cookie, but the URL ref was found.
        //    In this case we look up if the current visit is credited to a campaign and will credit this campaign rather than the URL ref (since campaigns have higher priority)
        if (empty($referrerCampaignName)
            && $type == Common::REFERRER_TYPE_CAMPAIGN
            && !empty($name)
        ) {
            // Use default values per above
        } // 1) Campaigns from 1st party cookie
        elseif (!empty($referrerCampaignName)) {
            $type = Common::REFERRER_TYPE_CAMPAIGN;
            $name = $referrerCampaignName;
            $keyword = $referrerCampaignKeyword;
            $time = $referrerTimestamp;
        } // 2) Referrer URL parsing
        elseif (!empty($referrerUrl)) {
            $referrer = new Referrer();
            $referrer = $referrer->getReferrerInformation($referrerUrl, $currentUrl = '', $idSite);

            // if the parsed referrer is interesting enough, ie. website or search engine
            if (in_array($referrer['referer_type'], array(Common::REFERRER_TYPE_SEARCH_ENGINE, Common::REFERRER_TYPE_WEBSITE))) {
                $type = $referrer['referer_type'];
                $name = $referrer['referer_name'];
                $keyword = $referrer['referer_keyword'];
                $time = $referrerTimestamp;
            }
        }
        $this->setCampaignValuesToLowercase($type, $name, $keyword);

        $goal += array(
            'referer_type'              => $type,
            'referer_name'              => $name,
            'referer_keyword'           => $keyword,
            // this field is currently unused
            'referer_visit_server_date' => date("Y-m-d", $time),
        );

        // some goals are converted, so must be ecommerce Order or Cart Update
        if ($this->requestIsEcommerce) {
            $this->recordEcommerceGoal($goal, $visitorInformation);
        } else {
            $this->recordStandardGoals($goal, $action, $visitorInformation);
        }
    }

    /**
     * Returns rounded decimal revenue, or if revenue is integer, then returns as is.
     *
     * @param int|float $revenue
     * @return int|float
     */
    protected function getRevenue($revenue)
    {
        if (round($revenue) == $revenue) {
            return $revenue;
        }
        return round($revenue, self::REVENUE_PRECISION);
    }

    /**
     * Records an Ecommerce conversion in the DB. Deals with Items found in the request.
     * Will deal with 2 types of conversions: Ecommerce Order and Ecommerce Cart update (Add to cart, Update Cart etc).
     *
     * @param array $conversion
     * @param array $visitInformation
     */
    protected function recordEcommerceGoal($conversion, $visitInformation)
    {
        if ($this->isThereExistingCartInVisit) {
            Common::printDebug("There is an existing cart for this visit");
        }
        if ($this->isGoalAnOrder) {
            $conversion['idgoal'] = self::IDGOAL_ORDER;
            $conversion['idorder'] = $this->orderId;
            $conversion['buster'] = Common::hashStringToInt($this->orderId);
            $conversion['revenue_subtotal'] = $this->getRevenue($this->request->getParam('ec_st'));
            $conversion['revenue_tax'] = $this->getRevenue($this->request->getParam('ec_tx'));
            $conversion['revenue_shipping'] = $this->getRevenue($this->request->getParam('ec_sh'));
            $conversion['revenue_discount'] = $this->getRevenue($this->request->getParam('ec_dt'));

            $debugMessage = 'The conversion is an Ecommerce order';
        } // If Cart update, select current items in the previous Cart
        else {
            $conversion['buster'] = 0;
            $conversion['idgoal'] = self::IDGOAL_CART;
            $debugMessage = 'The conversion is an Ecommerce Cart Update';
        }
        $conversion['revenue'] = $this->getRevenue($this->request->getGoalRevenue($defaultRevenue = 0));

        Common::printDebug($debugMessage . ':' . var_export($conversion, true));

        // INSERT or Sync items in the Cart / Order for this visit & order
        $items = $this->getEcommerceItemsFromRequest();
        if ($items === false) {
            return;
        }

        $itemsCount = 0;
        foreach ($items as $item) {
            $itemsCount += $item[self::INTERNAL_ITEM_QUANTITY];
        }
        $conversion['items'] = $itemsCount;

        if($this->isThereExistingCartInVisit) {
            $updateWhere = array(
                'idvisit' => $visitInformation['idvisit'],
                'idgoal'  => self::IDGOAL_CART,
                'buster'  => 0,
            );
            $recorded = $this->updateExistingConversion($conversion, $updateWhere);
        } else {
            $recorded = $this->insertNewConversion($conversion, $visitInformation);
        }

        if ($recorded) {
            $this->recordEcommerceItems($conversion, $items, $visitInformation);
        }

        /**
         * Triggered after successfully persisting an ecommerce conversion.
         * 
         * _Note: Subscribers should be wary of doing any expensive computation here as it may slow
         * the tracker down._
         * 
         * @param array $conversion The conversion entity that was just persisted. See what information
         *                          it contains [here](/guides/persistence-and-the-mysql-backend#conversions).
         * @param array $visitInformation The visit entity that we are tracking a conversion for. See what
         *                                information it contains [here](/guides/persistence-and-the-mysql-backend#visits).
         */
        Piwik::postEvent('Tracker.recordEcommerceGoal', array($conversion, $visitInformation));
    }

    /**
     * Returns Items read from the request string
     * @return array|bool
     */
    protected function getEcommerceItemsFromRequest()
    {
        $items = Common::unsanitizeInputValue($this->request->getParam('ec_items'));
        if (empty($items)) {
            Common::printDebug("There are no Ecommerce items in the request");
            // we still record an Ecommerce order without any item in it
            return array();
        }
        $items = Common::json_decode($items, $assoc = true);
        if (!is_array($items)) {
            Common::printDebug("Error while json_decode the Ecommerce items = " . var_export($items, true));
            return false;
        }

        $cleanedItems = $this->getCleanedEcommerceItems($items);
        return $cleanedItems;
    }

    /**
     * Loads the Ecommerce items from the request and records them in the DB
     *
     * @param array $goal
     * @param array $items
     * @throws Exception
     * @return int Number of items in the cart
     */
    protected function recordEcommerceItems($goal, $items)
    {
        $itemInCartBySku = array();
        foreach ($items as $item) {
            $itemInCartBySku[$item[0]] = $item;
        }

        // Select all items currently in the Cart if any
        $sql = "SELECT idaction_sku, idaction_name, idaction_category, idaction_category2, idaction_category3, idaction_category4, idaction_category5, price, quantity, deleted, idorder as idorder_original_value
				FROM " . Common::prefixTable('log_conversion_item') . "
				WHERE idvisit = ?
					AND (idorder = ? OR idorder = ?)";

        $bind = array($goal['idvisit'],
                      isset($goal['idorder']) ? $goal['idorder'] : self::ITEM_IDORDER_ABANDONED_CART,
                      self::ITEM_IDORDER_ABANDONED_CART
        );

        $itemsInDb = Tracker::getDatabase()->fetchAll($sql, $bind);

        Common::printDebug("Items found in current cart, for conversion_item (visit,idorder)=" . var_export($bind, true));
        Common::printDebug($itemsInDb);
        // Look at which items need to be deleted, which need to be added or updated, based on the SKU
        $skuFoundInDb = $itemsToUpdate = array();
        foreach ($itemsInDb as $itemInDb) {
            $skuFoundInDb[] = $itemInDb['idaction_sku'];

            // Ensure price comparisons will have the same assumption
            $itemInDb['price'] = $this->getRevenue($itemInDb['price']);
            $itemInDbOriginal = $itemInDb;
            $itemInDb = array_values($itemInDb);

            // Cast all as string, because what comes out of the fetchAll() are strings
            $itemInDb = $this->getItemRowCast($itemInDb);

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
            $newItem = $this->getItemRowCast($newItem);

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

    // In the GET items parameter, each item has the following array of information
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

    /**
     * Reads items from the request, then looks up the names from the lookup table
     * and returns a clean array of items ready for the database.
     *
     * @param array $items
     * @return array $cleanedItems
     */
    protected function getCleanedEcommerceItems($items)
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
                $price = $this->getRevenue($item[self::INDEX_ITEM_PRICE]);
            }
            if (!empty($item[self::INDEX_ITEM_QUANTITY])
                && is_numeric($item[self::INDEX_ITEM_QUANTITY])
            ) {
                $quantity = (int)$item[self::INDEX_ITEM_QUANTITY];
            }

            // self::INDEX_ITEM_* are in order
            $cleanedItems[] = array(
                self::INTERNAL_ITEM_SKU       => $sku,
                self::INTERNAL_ITEM_NAME      => $name,
                self::INTERNAL_ITEM_CATEGORY  => $category,
                self::INTERNAL_ITEM_CATEGORY2 => $category2,
                self::INTERNAL_ITEM_CATEGORY3 => $category3,
                self::INTERNAL_ITEM_CATEGORY4 => $category4,
                self::INTERNAL_ITEM_CATEGORY5 => $category5,
                self::INTERNAL_ITEM_PRICE     => $price,
                self::INTERNAL_ITEM_QUANTITY  => $quantity
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
            $newRow = $this->getItemRowEnriched($goal, $item);
            Common::printDebug($newRow);
            $updateParts = $sqlBind = array();
            foreach ($newRow AS $name => $value) {
                $updateParts[] = $name . " = ?";
                $sqlBind[] = $value;
            }
            $sql = 'UPDATE ' . Common::prefixTable('log_conversion_item') . "
					SET " . implode($updateParts, ', ') . "
						WHERE idvisit = ?
							AND idorder = ?
							AND idaction_sku = ?";
            $sqlBind[] = $newRow['idvisit'];
            $sqlBind[] = $item['idorder_original_value'];
            $sqlBind[] = $newRow['idaction_sku'];
            Tracker::getDatabase()->query($sql, $sqlBind);
        }
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

        $sql = "INSERT INTO " . Common::prefixTable('log_conversion_item') . "
					(idaction_sku, idaction_name, idaction_category, idaction_category2, idaction_category3, idaction_category4, idaction_category5, price, quantity, deleted,
					idorder, idsite, idvisitor, server_time, idvisit)
					VALUES ";
        $i = 0;
        $bind = array();
        foreach ($itemsToInsert as $item) {
            if ($i > 0) {
                $sql .= ',';
            }
            $newRow = array_values($this->getItemRowEnriched($goal, $item));
            $sql .= " ( " . Common::getSqlStringFieldsArray($newRow) . " ) ";
            $i++;
            $bind = array_merge($bind, $newRow);
        }
        Tracker::getDatabase()->query($sql, $bind);
        Common::printDebug($sql);
        Common::printDebug($bind);
    }

    protected function getItemRowEnriched($goal, $item)
    {
        $newRow = array(
            'idaction_sku'       => (int)$item[self::INTERNAL_ITEM_SKU],
            'idaction_name'      => (int)$item[self::INTERNAL_ITEM_NAME],
            'idaction_category'  => (int)$item[self::INTERNAL_ITEM_CATEGORY],
            'idaction_category2' => (int)$item[self::INTERNAL_ITEM_CATEGORY2],
            'idaction_category3' => (int)$item[self::INTERNAL_ITEM_CATEGORY3],
            'idaction_category4' => (int)$item[self::INTERNAL_ITEM_CATEGORY4],
            'idaction_category5' => (int)$item[self::INTERNAL_ITEM_CATEGORY5],
            'price'              => $item[self::INTERNAL_ITEM_PRICE],
            'quantity'           => $item[self::INTERNAL_ITEM_QUANTITY],
            'deleted'            => isset($item['deleted']) ? $item['deleted'] : 0, //deleted
            'idorder'            => isset($goal['idorder']) ? $goal['idorder'] : self::ITEM_IDORDER_ABANDONED_CART, //idorder = 0 in log_conversion_item for carts
            'idsite'             => $goal['idsite'],
            'idvisitor'          => $goal['idvisitor'],
            'server_time'        => $goal['server_time'],
            'idvisit'            => $goal['idvisit']
        );
        return $newRow;
    }

    /**
     * Records a standard non-Ecommerce goal in the DB (URL/Title matching),
     * linking the conversion to the action that triggered it
     * @param $goal
     * @param Action $action
     * @param $visitorInformation
     */
    protected function recordStandardGoals($goal, $action, $visitorInformation)
    {
        foreach ($this->convertedGoals as $convertedGoal) {
            Common::printDebug("- Goal " . $convertedGoal['idgoal'] . " matched. Recording...");
            $conversion = $goal;
            $conversion['idgoal'] = $convertedGoal['idgoal'];
            $conversion['url'] = $convertedGoal['url'];
            $conversion['revenue'] = $this->getRevenue($convertedGoal['revenue']);

            if (!is_null($action)) {
                $conversion['idaction_url'] = $action->getIdActionUrl();
                $conversion['idlink_va'] = $action->getIdLinkVisitAction();
            }

            // If multiple Goal conversions per visit, set a cache buster
            $conversion['buster'] = $convertedGoal['allow_multiple'] == 0
                ? '0'
                : $visitorInformation['visit_last_action_time'];

            $this->insertNewConversion($conversion, $visitorInformation);

            /**
             * Triggered after successfully recording a non-ecommerce conversion.
             * 
             * _Note: Subscribers should be wary of doing any expensive computation here as it may slow
             * the tracker down._
             * 
             * @param array $conversion The conversion entity that was just persisted. See what information
             *                          it contains [here](/guides/persistence-and-the-mysql-backend#conversions).
             */
            Piwik::postEvent('Tracker.recordStandardGoals', array($conversion));
        }
    }

    /**
     * Helper function used by other record* methods which will INSERT or UPDATE the conversion in the DB
     *
     * @param array $conversion
     * @param array $visitInformation
     * @return bool
     */
    protected function insertNewConversion($conversion, $visitInformation)
    {
        /**
         * Triggered before persisting a new [conversion entity](/guides/persistence-and-the-mysql-backend#conversions).
         * 
         * This event can be used to modify conversion information or to add new information to be persisted.
         * 
         * @param array $conversion The conversion entity. Read [this](/guides/persistence-and-the-mysql-backend#conversions)
         *                          to see what it contains.
         * @param array $visitInformation The visit entity that we are tracking a conversion for. See what
         *                                information it contains [here](/guides/persistence-and-the-mysql-backend#visits).
         * @param \Piwik\Tracker\Request $request An object describing the tracking request being processed.
         */
        Piwik::postEvent('Tracker.newConversionInformation', array(&$conversion, $visitInformation, $this->request));

        $newGoalDebug = $conversion;
        $newGoalDebug['idvisitor'] = bin2hex($newGoalDebug['idvisitor']);
        Common::printDebug($newGoalDebug);

        $fields = implode(", ", array_keys($conversion));
        $bindFields = Common::getSqlStringFieldsArray($conversion);
        $sql = 'INSERT IGNORE INTO ' . Common::prefixTable('log_conversion') . "
                ($fields) VALUES ($bindFields) ";
        $bind = array_values($conversion);
        $result = Tracker::getDatabase()->query($sql, $bind);

        // If a record was inserted, we return true
        return Tracker::getDatabase()->rowCount($result) > 0;
    }

    /**
     * Casts the item array so that array comparisons work nicely
     * @param array $row
     * @return array
     */
    protected function getItemRowCast($row)
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

    protected function updateExistingConversion($newGoal, $updateWhere)
    {
        $updateParts = $sqlBind = $updateWhereParts = array();
        foreach ($newGoal AS $name => $value) {
            $updateParts[] = $name . " = ?";
            $sqlBind[] = $value;
        }
        foreach ($updateWhere as $name => $value) {
            $updateWhereParts[] = $name . " = ?";
            $sqlBind[] = $value;
        }
        $sql = 'UPDATE  ' . Common::prefixTable('log_conversion') . "
					SET " . implode($updateParts, ', ') . "
						WHERE " . implode($updateWhereParts, ' AND ');

        try {
            Tracker::getDatabase()->query($sql, $sqlBind);
        } catch(Exception $e){
            Common::printDebug("There was an error while updating the Conversion: " . $e->getMessage());
            return false;
        }
        return true;
    }

    /**
     * @param $type
     * @param $name
     * @param $keyword
     */
    protected function setCampaignValuesToLowercase($type, &$name, &$keyword)
    {
        if ($type === Common::REFERRER_TYPE_CAMPAIGN) {
            if (!empty($name)) {
                $name = Common::mb_strtolower($name);
            }
            if (!empty($keyword)) {
                $keyword = Common::mb_strtolower($keyword);
            }
        }
    }

    /**
     * @param $goal
     * @param $pattern_type
     * @param $url
     * @return bool
     * @throws \Exception
     */
    protected function isUrlMatchingGoal($goal, $pattern_type, $url)
    {
        switch ($pattern_type) {
            case 'regex':
                $pattern = $goal['pattern'];
                if (strpos($pattern, '/') !== false
                    && strpos($pattern, '\\/') === false
                ) {
                    $pattern = str_replace('/', '\\/', $pattern);
                }
                $pattern = '/' . $pattern . '/';
                if (!$goal['case_sensitive']) {
                    $pattern .= 'i';
                }
                $match = (@preg_match($pattern, $url) == 1);
                break;
            case 'contains':
                if ($goal['case_sensitive']) {
                    $matched = strpos($url, $goal['pattern']);
                } else {
                    $matched = stripos($url, $goal['pattern']);
                }
                $match = ($matched !== false);
                break;
            case 'exact':
                if ($goal['case_sensitive']) {
                    $matched = strcmp($goal['pattern'], $url);
                } else {
                    $matched = strcasecmp($goal['pattern'], $url);
                }
                $match = ($matched == 0);
                break;
            default:
                throw new Exception(Piwik::translate('General_ExceptionInvalidGoalPattern', array($pattern_type)));
                break;
        }
        return $match;
    }
}
