<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
 * 
 * @category Piwik_Plugins
 * @package Piwik_Goals
 */

/**
 * Goals API lets you Manage existing goals, via "updateGoal" and "deleteGoal", create new Goals via "addGoal", 
 * or list existing Goals for one or several websites via "getGoals" 
 * 
 * If you are <a href='http://piwik.org/docs/ecommerce-analytics/' target='_blank'>tracking Ecommerce orders and products</a> on your site, the functions "getItemsSku", "getItemsName" and "getItemsCategory"
 * will return the list of products purchased on your site, either grouped by Product SKU, Product Name or Product Category. For each name, SKU or category, the following
 * metrics are returned: Total revenue, Total quantity, average price, average quantity, number of orders (or abandoned carts) containing this product, number of visits on the Product page,
 * Conversion rate.
 * 
 * By default, these functions return the 'Products purchased'. These functions also accept an optional parameter &abandonedCarts=1.
 * If the parameter is set, it will instead return the metrics for products that were left in an abandoned cart therefore not purchased. 
 * 
 * The API also lets you request overall Goal metrics via the method "get": Conversions, Visits with at least one conversion, Conversion rate and Revenue.
 * If you wish to request specific metrics about Ecommerce goals, you can set the parameter &idGoal=ecommerceAbandonedCart to get metrics about abandoned carts (including Lost revenue, and number of items left in the cart) 
 * or &idGoal=ecommerceOrder to get metrics about Ecommerce orders (number of orders, visits with an order, subtotal, tax, shipping, discount, revenue, items ordered)
 * 
 * See also the documentation about <a href='http://piwik.org/docs/tracking-goals-web-analytics/' target='_blank'>Tracking Goals</a> in Piwik.
 * 
 * @package Piwik_Goals
 */
class Piwik_Goals_API 
{
	static private $instance = null;
	/**
	 * @return Piwik_Goals_API
	 */
	static public function getInstance()
	{
		if (self::$instance == null)
		{
			self::$instance = new self;
		}
		return self::$instance;
	}
	
	/**
	 * Returns all Goals for a given website, or list of websites
	 * 
	 * @param string|array $idSite Array or Comma separated list of website IDs to request the goals for
	 * @return array Array of Goal attributes
	 */
	public function getGoals( $idSite )
	{
		if(!is_array($idSite))
		{
			$idSite = Piwik_Site::getIdSitesFromIdSitesString($idSite);
		}
		Piwik::checkUserHasViewAccess($idSite);
		$goals = Piwik_FetchAll("SELECT * 
								FROM ".Piwik_Common::prefixTable('goal')." 
								WHERE idsite IN (".implode(", ", $idSite).")
									AND deleted = 0");
		$cleanedGoals = array();
		foreach($goals as &$goal)
		{
			if($goal['match_attribute'] == 'manually') {
			    unset($goal['pattern']);
			    unset($goal['pattern_type']);
			    unset($goal['case_sensitive']);
			}
			$cleanedGoals[$goal['idgoal']] = $goal;
		}
		return $cleanedGoals;
	}

	/**
	 * Creates a Goal for a given website.
	 * 
	 * @param int $idSite
	 * @param string $name
	 * @param string $matchAttribute 'url', 'title', 'file', 'external_website' or 'manually'
	 * @param string $pattern eg. purchase-confirmation.htm
	 * @param string $patternType 'regex', 'contains', 'exact' 
	 * @param bool $caseSensitive
	 * @param float|string $revenue If set, default revenue to assign to conversions
	 * @param bool $allowMultipleConversionsPerVisit By default, multiple conversions in the same visit will only record the first conversion.
	 * 						If set to true, multiple conversions will all be recorded within a visit (useful for Ecommerce goals)
	 * @return int ID of the new goal
	 */
	public function addGoal( $idSite, $name, $matchAttribute, $pattern, $patternType, $caseSensitive = false, $revenue = false, $allowMultipleConversionsPerVisit = false)
	{
		Piwik::checkUserHasAdminAccess($idSite);
		$this->checkPatternIsValid($patternType, $pattern);
		$name = $this->checkName($name);
		$pattern = $this->checkPattern($pattern);

		// save in db
		$db = Zend_Registry::get('db');
		$idGoal = $db->fetchOne("SELECT max(idgoal) + 1 
								FROM ".Piwik_Common::prefixTable('goal')." 
								WHERE idsite = ?", $idSite);
		if($idGoal == false)
		{
			$idGoal = 1;
		}
		$db->insert(Piwik_Common::prefixTable('goal'),
					array( 
						'idsite' => $idSite,
						'idgoal' => $idGoal,
						'name' => $name,
						'match_attribute' => $matchAttribute,
						'pattern' => $pattern,
						'pattern_type' => $patternType,
						'case_sensitive' => (int)$caseSensitive,
						'allow_multiple' => (int)$allowMultipleConversionsPerVisit,
						'revenue' => (float)$revenue,
						'deleted' => 0,
					));
		Piwik_Common::regenerateCacheWebsiteAttributes($idSite);
		return $idGoal;
	}
	
	/**
	 * Updates a Goal description.
	 * Will not update or re-process the conversions already recorded  
	 * 
	 * @see addGoal() for parameters description
	 * @return void
	 */
	public function updateGoal( $idSite, $idGoal, $name, $matchAttribute, $pattern, $patternType, $caseSensitive = false, $revenue = false, $allowMultipleConversionsPerVisit = false)
	{
		Piwik::checkUserHasAdminAccess($idSite);
		$name = $this->checkName($name);
		$pattern = $this->checkPattern($pattern);
		$this->checkPatternIsValid($patternType, $pattern);
		Zend_Registry::get('db')->update( Piwik_Common::prefixTable('goal'), 
					array(
						'name' => $name,
						'match_attribute' => $matchAttribute,
						'pattern' => $pattern,
						'pattern_type' => $patternType,
						'case_sensitive' => (int)$caseSensitive,
						'allow_multiple' => (int)$allowMultipleConversionsPerVisit,
						'revenue' => (float)$revenue,
						),
					"idsite = '$idSite' AND idgoal = '$idGoal'"
			);	
		Piwik_Common::regenerateCacheWebsiteAttributes($idSite);
	}

	private function checkPatternIsValid($patternType, $pattern)
	{
		if($patternType == 'exact' 
			&& substr($pattern, 0, 4) != 'http')
		{
			throw new Exception(Piwik_TranslateException('Goals_ExceptionInvalidMatchingString', array("http:// or https://", "http://www.yourwebsite.com/newsletter/subscribed.html")));
		}
	}
	
	private function checkName($name)
	{
		return urldecode($name);
	}
	
	private function checkPattern($pattern)
	{
		return urldecode($pattern);
	}
	
	/**
	 * Soft deletes a given Goal.
	 * Stats data in the archives will still be recorded, but not displayed.
	 * 
	 * @param int $idSite
	 * @param int $idGoal
	 * @return void
	 */
	public function deleteGoal( $idSite, $idGoal )
	{
		Piwik::checkUserHasAdminAccess($idSite);
		Piwik_Query("UPDATE ".Piwik_Common::prefixTable('goal')."
										SET deleted = 1
										WHERE idsite = ? 
											AND idgoal = ?",
									array($idSite, $idGoal));
		Piwik_Query("DELETE FROM ".Piwik_Common::prefixTable("log_conversion")." WHERE idgoal = ?", $idGoal);
		Piwik_Common::regenerateCacheWebsiteAttributes($idSite);
	}
	
	/**
	 * Returns a datatable of Items SKU/name or categories and their metrics
	 * If $abandonedCarts set to 1, will return items abandoned in carts. If set to 0, will return items ordered
	 */
	protected function getItems($recordName, $idSite, $period, $date, $abandonedCarts )
	{
		Piwik::checkUserHasViewAccess( $idSite );
		$recordNameFinal = $recordName;
		if($abandonedCarts)
		{
			$recordNameFinal = Piwik_Goals::getItemRecordNameAbandonedCart($recordName);
		}
		$archive = Piwik_Archive::build($idSite, $period, $date );
		$dataTable = $archive->getDataTable($recordNameFinal);
		$dataTable->filter('Sort', array(Piwik_Archive::INDEX_ECOMMERCE_ITEM_REVENUE));
		$dataTable->queueFilter('ReplaceColumnNames');
		
		$ordersColumn = 'orders';
		if($abandonedCarts)
		{
			$ordersColumn = 'abandoned_carts';
			$dataTable->renameColumn(Piwik_Archive::INDEX_ECOMMERCE_ORDERS, $ordersColumn);
		}
		// Average price = sum product revenue / quantity
		$dataTable->queueFilter('ColumnCallbackAddColumnQuotient', array('avg_price', 'price', $ordersColumn, Piwik_Tracker_GoalManager::REVENUE_PRECISION));

		// Average quantity = sum product quantity / abandoned carts 
		$dataTable->queueFilter('ColumnCallbackAddColumnQuotient', array('avg_quantity', 'quantity', $ordersColumn, $precision = 1));
		$dataTable->queueFilter('ColumnDelete', array('price'));
		
		// Enrich the datatable with Product/Categories views, and conversion rates
		$mapping = array(
			'Goals_ItemsSku' => '_pks',
			'Goals_ItemsName' => '_pkn',
			'Goals_ItemsCategory' => '_pkc',
		);
		$customVariables = Piwik_CustomVariables_API::getInstance()->getCustomVariables($idSite, $period, $date, $segment = false, $expanded = false, $_leavePiwikCoreVariables = true);
		if($customVariables instanceof Piwik_DataTable
			&& $row = $customVariables->getRowFromLabel($mapping[$recordName]))
		{
			// Request views for all products/categories
			$idSubtable = $row->getIdSubDataTable();
			$ecommerceViews = Piwik_CustomVariables_API::getInstance()->getCustomVariablesValuesFromNameId($idSite, $period, $date, $idSubtable);
			
			$dataTable->addDataTable($ecommerceViews);
			// Product conversion rate = orders / visits 
			$dataTable->queueFilter('ColumnCallbackAddColumnPercentage', array('conversion_rate', $ordersColumn, 'nb_visits', Piwik_Tracker_GoalManager::REVENUE_PRECISION));
		}
		
		return $dataTable;
	}
	
	public function getItemsSku($idSite, $period, $date, $abandonedCarts = false )
	{
		return $this->getItems('Goals_ItemsSku', $idSite, $period, $date, $abandonedCarts);
	}
	
	public function getItemsName($idSite, $period, $date, $abandonedCarts = false )
	{
		return $this->getItems('Goals_ItemsName', $idSite, $period, $date, $abandonedCarts);
	}
	
	public function getItemsCategory($idSite, $period, $date, $abandonedCarts = false )
	{
		return $this->getItems('Goals_ItemsCategory', $idSite, $period, $date, $abandonedCarts);
	}
	
	/**
	 * Returns Goals data
	 * 
	 * @param int $idSite
	 * @param string $period
	 * @param string $date
	 * @param int $idGoal
	 * @param array $columns Array of metrics to fetch: nb_conversions, conversion_rate, revenue
	 * @return Piwik_DataTable
	 */
	public function get( $idSite, $period, $date, $segment = false, $idGoal = false, $columns = array() )
	{
		Piwik::checkUserHasViewAccess( $idSite );
		$archive = Piwik_Archive::build($idSite, $period, $date, $segment );
		$columns = Piwik::getArrayFromApiParameter($columns);
		
		// Mapping string idGoal to internal ID
		$idGoal = ($idGoal == Piwik_Archive::LABEL_ECOMMERCE_ORDER) 
						? Piwik_Tracker_GoalManager::IDGOAL_ORDER
						: ($idGoal == Piwik_Archive::LABEL_ECOMMERCE_CART
							? Piwik_Tracker_GoalManager::IDGOAL_CART
							: $idGoal);
							
		if(empty($columns))
		{
			$columns = Piwik_Goals::getGoalColumns($idGoal);
			if($idGoal == 'ecommerceOrder')
			{
				$columns[] = 'avg_order_revenue';
			}
		}
		if(in_array('avg_order_revenue', $columns)
			&& $idGoal == 'ecommerceOrder')
		{
			$columns[] = 'nb_conversions';
			$columns[] = 'revenue';
			$columns = array_unique($columns);
		}
		$columnsToSelect = array();
		foreach($columns as &$columnName)
		{
			$columnsToSelect[] = Piwik_Goals::getRecordName($columnName, $idGoal);
		}
		$dataTable = $archive->getDataTableFromNumeric($columnsToSelect);
		
		// Rewrite column names as we expect them
		foreach($columnsToSelect as $id => $oldName)
		{
			$dataTable->renameColumn($oldName, $columns[$id]);
		}
		if($idGoal == 'ecommerceOrder')
		{
			if($dataTable instanceof Piwik_DataTable_Array)
			{
				foreach($dataTable->getArray() as $row)
				{
					$this->enrichTable($row);
				}
			}
			else
			{
				$this->enrichTable($dataTable);
			}
		}
		return $dataTable;
	}
	
	protected function enrichTable($table)
	{
		$row = $table->getFirstRow();
		if(!$row)
		{
			return;
		}
		// AVG order per visit
		if(false !== $table->getColumn('avg_order_revenue'))
		{
			$conversions = $row->getColumn('nb_conversions');
			if($conversions)
			{
				$row->setColumn('avg_order_revenue', round($row->getColumn('revenue') / $conversions, 2));
			}
		}
	}
	
	protected function getNumeric( $idSite, $period, $date, $segment, $toFetch )
	{
		Piwik::checkUserHasViewAccess( $idSite );
		$archive = Piwik_Archive::build($idSite, $period, $date, $segment );
		$dataTable = $archive->getNumeric($toFetch);
		return $dataTable;		
	}

	/**
	 * @ignore 
	 */
	public function getConversions( $idSite, $period, $date, $segment = false, $idGoal = false )
	{
		return $this->getNumeric( $idSite, $period, $date, $segment, Piwik_Goals::getRecordName('nb_conversions', $idGoal));
	}
	
	/**
	 * @ignore 
	 */
	public function getNbVisitsConverted( $idSite, $period, $date, $segment = false, $idGoal = false )
	{
		return $this->getNumeric( $idSite, $period, $date, $segment, Piwik_Goals::getRecordName('nb_visits_converted', $idGoal));
	}
	
	/**
	 * @ignore 
	 */
	public function getConversionRate( $idSite, $period, $date, $segment = false, $idGoal = false )
	{
		return $this->getNumeric( $idSite, $period, $date, $segment, Piwik_Goals::getRecordName('conversion_rate', $idGoal));
	}
	
	/**
	 * @ignore 
	 */
	public function getRevenue( $idSite, $period, $date, $segment = false, $idGoal = false )
	{
		return $this->getNumeric( $idSite, $period, $date, $segment, Piwik_Goals::getRecordName('revenue', $idGoal));
	}
}
