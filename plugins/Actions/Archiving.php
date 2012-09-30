<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id: Actions.php 6986 2012-09-15 03:42:26Z capedfuzz $
 *
 * @category Piwik_Plugins
 * @package Piwik_Actions
 */

/**
 * Class encapsulating logic to process Day/Period Archiving for the Actions reports
 *
 * @package Piwik_Actions
 */
class Piwik_Actions_Archiving
{
	protected $actionsTablesByType = null;

	public static $actionTypes = array(
		Piwik_Tracker_Action::TYPE_ACTION_URL,
		Piwik_Tracker_Action::TYPE_OUTLINK,
		Piwik_Tracker_Action::TYPE_DOWNLOAD,
		Piwik_Tracker_Action::TYPE_ACTION_NAME,
	);

	static protected $invalidSummedColumnNameToRenamedNameFromPeriodArchive = array(
		Piwik_Archive::INDEX_NB_UNIQ_VISITORS => Piwik_Archive::INDEX_SUM_DAILY_NB_UNIQ_VISITORS,
		Piwik_Archive::INDEX_PAGE_ENTRY_NB_UNIQ_VISITORS => Piwik_Archive::INDEX_PAGE_ENTRY_SUM_DAILY_NB_UNIQ_VISITORS,
		Piwik_Archive::INDEX_PAGE_EXIT_NB_UNIQ_VISITORS => Piwik_Archive::INDEX_PAGE_EXIT_SUM_DAILY_NB_UNIQ_VISITORS,
	);

	static protected $invalidSummedColumnNameToDeleteFromDayArchive = array(
		Piwik_Archive::INDEX_NB_UNIQ_VISITORS,
		Piwik_Archive::INDEX_PAGE_ENTRY_NB_UNIQ_VISITORS,
		Piwik_Archive::INDEX_PAGE_EXIT_NB_UNIQ_VISITORS,
	);

	/**
	 * Archives Actions reports for a Period
	 * @param Piwik_ArchiveProcessing $archiveProcessing
	 * @return bool
	 */
	public function archivePeriod(Piwik_ArchiveProcessing $archiveProcessing)
	{
		Piwik_Actions_ArchivingHelper::reloadConfig();
		$dataTableToSum = array(
			'Actions_actions',
			'Actions_downloads',
			'Actions_outlink',
			'Actions_actions_url',
		);
		$archiveProcessing->archiveDataTable($dataTableToSum,
					self::$invalidSummedColumnNameToRenamedNameFromPeriodArchive,
					Piwik_Actions_ArchivingHelper::$maximumRowsInDataTableLevelZero,
					Piwik_Actions_ArchivingHelper::$maximumRowsInSubDataTable,
					Piwik_Actions_ArchivingHelper::$columnToSortByBeforeTruncation);

		$archiveProcessing->archiveNumericValuesSum(array(
			'Actions_nb_pageviews',
			'Actions_nb_uniq_pageviews',
			'Actions_nb_downloads',
			'Actions_nb_uniq_downloads',
			'Actions_nb_outlinks',
			'Actions_nb_uniq_outlinks'
		));

		return true;
	}

	/**
	 * Archives Actions reports for a Day
	 *
	 * @param Piwik_ArchiveProcessing $archiveProcessing
	 * @return bool
	 */
	public function archiveDay(Piwik_ArchiveProcessing $archiveProcessing)
	{
		$rankingQueryLimit = self::getRankingQueryLimit();
		Piwik_Actions_ArchivingHelper::reloadConfig();

		$this->initActionsTables();
		$this->archiveDayActions($archiveProcessing, $rankingQueryLimit);
		$this->archiveDayEntryActions($archiveProcessing, $rankingQueryLimit);
		$this->archiveDayExitActions($archiveProcessing, $rankingQueryLimit);
		$this->archiveDayActionsTime($archiveProcessing, $rankingQueryLimit);

		// Record the final datasets
		$this->archiveDayRecordInDatabase($archiveProcessing);

		return true;
	}

	/*
	 * Page URLs and Page names, general stats
	 */
	protected function archiveDayActions($archiveProcessing, $rankingQueryLimit)
	{
		$select = "log_action.name,
				log_action.type,
				log_action.idaction,
				log_action.url_prefix,
				count(distinct log_link_visit_action.idvisit) as `" . Piwik_Archive::INDEX_NB_VISITS . "`,
				count(distinct log_link_visit_action.idvisitor) as `" . Piwik_Archive::INDEX_NB_UNIQ_VISITORS . "`,
				count(*) as `" . Piwik_Archive::INDEX_PAGE_NB_HITS . "`";

		$from = array(
			"log_link_visit_action",
			array(
				"table" => "log_action",
				"joinOn" => "log_link_visit_action.%s = log_action.idaction"
			)
		);

		$where = "log_link_visit_action.server_time >= ?
				AND log_link_visit_action.server_time <= ?
				AND log_link_visit_action.idsite = ?
				AND log_link_visit_action.%s IS NOT NULL";

		$groupBy = "log_action.idaction";
		$orderBy = "`" . Piwik_Archive::INDEX_PAGE_NB_HITS . "` DESC, name ASC";

		$rankingQuery = false;
		if ($rankingQueryLimit > 0) {
			$rankingQuery = new Piwik_RankingQuery($rankingQueryLimit);
			$rankingQuery->setOthersLabel(Piwik_DataTable::LABEL_SUMMARY_ROW);
			$rankingQuery->addLabelColumn(array('idaction', 'name'));
			$rankingQuery->addColumn(array('url_prefix', Piwik_Archive::INDEX_NB_UNIQ_VISITORS));
			$rankingQuery->addColumn(array(Piwik_Archive::INDEX_PAGE_NB_HITS, Piwik_Archive::INDEX_NB_VISITS), 'sum');
			$rankingQuery->partitionResultIntoMultipleGroups('type', array_keys($this->actionsTablesByType));
		}

		$this->archiveDayQueryProcess($select, $from, $where, $orderBy, $groupBy,
			"idaction_url", $archiveProcessing, $rankingQuery);

		$this->archiveDayQueryProcess($select, $from, $where, $orderBy, $groupBy,
			"idaction_name", $archiveProcessing, $rankingQuery);
	}


	/**
	 * Entry actions for Page URLs and Page names
	 */
	protected function archiveDayEntryActions($archiveProcessing, $rankingQueryLimit)
	{
		$rankingQuery = false;
		if ($rankingQueryLimit > 0) {
			$rankingQuery = new Piwik_RankingQuery($rankingQueryLimit);
			$rankingQuery->setOthersLabel(Piwik_DataTable::LABEL_SUMMARY_ROW);
			$rankingQuery->addLabelColumn('idaction');
			$rankingQuery->addColumn(Piwik_Archive::INDEX_PAGE_ENTRY_NB_UNIQ_VISITORS);
			$rankingQuery->addColumn(array(Piwik_Archive::INDEX_PAGE_ENTRY_NB_VISITS,
				Piwik_Archive::INDEX_PAGE_ENTRY_NB_ACTIONS,
				Piwik_Archive::INDEX_PAGE_ENTRY_SUM_VISIT_LENGTH,
				Piwik_Archive::INDEX_PAGE_ENTRY_BOUNCE_COUNT), 'sum');
			$rankingQuery->partitionResultIntoMultipleGroups('type', array_keys($this->actionsTablesByType));

			$extraSelects = 'log_action.type, log_action.name,';
			$from = array(
				"log_visit",
				array(
					"table" => "log_action",
					"joinOn" => "log_visit.%s = log_action.idaction"
				)
			);
			$orderBy = "`" . Piwik_Archive::INDEX_PAGE_ENTRY_NB_ACTIONS . "` DESC, log_action.name ASC";
		} else {
			$extraSelects = false;
			$from = "log_visit";
			$orderBy = false;
		}

		$select = "log_visit.%s as idaction, $extraSelects
				count(distinct log_visit.idvisitor) as `" . Piwik_Archive::INDEX_PAGE_ENTRY_NB_UNIQ_VISITORS . "`,
				count(*) as `" . Piwik_Archive::INDEX_PAGE_ENTRY_NB_VISITS . "`,
				sum(log_visit.visit_total_actions) as `" . Piwik_Archive::INDEX_PAGE_ENTRY_NB_ACTIONS . "`,
				sum(log_visit.visit_total_time) as `" . Piwik_Archive::INDEX_PAGE_ENTRY_SUM_VISIT_LENGTH . "`,
				sum(case log_visit.visit_total_actions when 1 then 1 when 0 then 1 else 0 end) as `" . Piwik_Archive::INDEX_PAGE_ENTRY_BOUNCE_COUNT . "`";

		$where = "log_visit.visit_last_action_time >= ?
				AND log_visit.visit_last_action_time <= ?
				AND log_visit.idsite = ?
		 		AND log_visit.%s > 0";

		$groupBy = "log_visit.%s, idaction";

		$this->archiveDayQueryProcess($select, $from, $where, $orderBy, $groupBy,
			"visit_entry_idaction_url", $archiveProcessing, $rankingQuery);

		$this->archiveDayQueryProcess($select, $from, $where, $orderBy, $groupBy,
			"visit_entry_idaction_name", $archiveProcessing, $rankingQuery);
	}


	/**
	 * Time per action
	 */
	protected function archiveDayActionsTime($archiveProcessing, $rankingQueryLimit)
	{
		$rankingQuery = false;
		if ($rankingQueryLimit > 0)
		{
			$rankingQuery = new Piwik_RankingQuery($rankingQueryLimit);
			$rankingQuery->setOthersLabel(Piwik_DataTable::LABEL_SUMMARY_ROW);
			$rankingQuery->addLabelColumn('idaction');
			$rankingQuery->addColumn(Piwik_Archive::INDEX_PAGE_SUM_TIME_SPENT, 'sum');
			$rankingQuery->partitionResultIntoMultipleGroups('type', array_keys($this->actionsTablesByType));

			$extraSelects = "log_action.type, log_action.name, count(*) as `" . Piwik_Archive::INDEX_PAGE_NB_HITS . "`,";
			$from = array(
				"log_link_visit_action",
				array(
					"table" => "log_action",
					"joinOn" => "log_link_visit_action.%s = log_action.idaction"
				)
			);
			$orderBy = "`" . Piwik_Archive::INDEX_PAGE_NB_HITS . "` DESC, log_action.name ASC";
		} else {
			$extraSelects = false;
			$from = "log_link_visit_action";
			$orderBy = false;
		}

		$select = "log_link_visit_action.%s as idaction, $extraSelects
				sum(log_link_visit_action.time_spent_ref_action) as `" . Piwik_Archive::INDEX_PAGE_SUM_TIME_SPENT . "`";

		$where = "log_link_visit_action.server_time >= ?
				AND log_link_visit_action.server_time <= ?
		 		AND log_link_visit_action.idsite = ?
		 		AND log_link_visit_action.time_spent_ref_action > 0
		 		AND log_link_visit_action.%s > 0";

		$groupBy = "log_link_visit_action.%s, idaction";

		$this->archiveDayQueryProcess($select, $from, $where, $orderBy, $groupBy,
			"idaction_url_ref", $archiveProcessing, $rankingQuery);

		$this->archiveDayQueryProcess($select, $from, $where, $orderBy, $groupBy,
			"idaction_name_ref", $archiveProcessing, $rankingQuery);
	}

	/**
	 * Exit actions
	 */
	public function archiveDayExitActions($archiveProcessing, $rankingQueryLimit)
	{
		$rankingQuery = false;
		if ($rankingQueryLimit > 0) {
			$rankingQuery = new Piwik_RankingQuery($rankingQueryLimit);
			$rankingQuery->setOthersLabel(Piwik_DataTable::LABEL_SUMMARY_ROW);
			$rankingQuery->addLabelColumn('idaction');
			$rankingQuery->addColumn(Piwik_Archive::INDEX_PAGE_EXIT_NB_UNIQ_VISITORS);
			$rankingQuery->addColumn(Piwik_Archive::INDEX_PAGE_EXIT_NB_VISITS, 'sum');
			$rankingQuery->partitionResultIntoMultipleGroups('type', array_keys($this->actionsTablesByType));

			$extraSelects = 'log_action.type, log_action.name,';
			$from = array(
				"log_visit",
				array(
					"table" => "log_action",
					"joinOn" => "log_visit.%s = log_action.idaction"
				)
			);
			$orderBy = "`" . Piwik_Archive::INDEX_PAGE_EXIT_NB_VISITS . "` DESC, log_action.name ASC";
		} else {
			$extraSelects = false;
			$from = "log_visit";
			$orderBy = false;
		}

		$select = "log_visit.%s as idaction, $extraSelects
				count(distinct log_visit.idvisitor) as `" . Piwik_Archive::INDEX_PAGE_EXIT_NB_UNIQ_VISITORS . "`,
				count(*) as `" . Piwik_Archive::INDEX_PAGE_EXIT_NB_VISITS . "`";

		$where = "log_visit.visit_last_action_time >= ?
				AND log_visit.visit_last_action_time <= ?
		 		AND log_visit.idsite = ?
		 		AND log_visit.%s > 0";

		$groupBy = "log_visit.%s, idaction";

		$this->archiveDayQueryProcess($select, $from, $where, $orderBy, $groupBy,
			"visit_exit_idaction_url", $archiveProcessing, $rankingQuery);

		$this->archiveDayQueryProcess($select, $from, $where, $orderBy, $groupBy,
			"visit_exit_idaction_name", $archiveProcessing, $rankingQuery);
		return array($rankingQuery, $extraSelects, $from, $orderBy, $select, $where, $groupBy);
	}


	/**
	 * Records in the DB the archived reports for Page views, Downloads, Outlinks, and Page titles
	 *
	 * @param $archiveProcessing
	 */
	protected function archiveDayRecordInDatabase($archiveProcessing)
	{
		Piwik_Actions_ArchivingHelper::clearActionsCache();

		$dataTable = $this->actionsTablesByType[Piwik_Tracker_Action::TYPE_ACTION_URL];
		self::deleteInvalidSummedColumnsFromDataTable($dataTable);
		$s = $dataTable->getSerialized( Piwik_Actions_ArchivingHelper::$maximumRowsInDataTableLevelZero, Piwik_Actions_ArchivingHelper::$maximumRowsInSubDataTable, Piwik_Actions_ArchivingHelper::$columnToSortByBeforeTruncation );
		$archiveProcessing->insertBlobRecord('Actions_actions_url', $s);
		$archiveProcessing->insertNumericRecord('Actions_nb_pageviews', array_sum($dataTable->getColumn(Piwik_Archive::INDEX_PAGE_NB_HITS)));
		$archiveProcessing->insertNumericRecord('Actions_nb_uniq_pageviews', array_sum($dataTable->getColumn(Piwik_Archive::INDEX_NB_VISITS)));
		destroy($dataTable);

		$dataTable = $this->actionsTablesByType[Piwik_Tracker_Action::TYPE_DOWNLOAD];
		self::deleteInvalidSummedColumnsFromDataTable($dataTable);
		$s = $dataTable->getSerialized(Piwik_Actions_ArchivingHelper::$maximumRowsInDataTableLevelZero, Piwik_Actions_ArchivingHelper::$maximumRowsInSubDataTable, Piwik_Actions_ArchivingHelper::$columnToSortByBeforeTruncation );
		$archiveProcessing->insertBlobRecord('Actions_downloads', $s);
		$archiveProcessing->insertNumericRecord('Actions_nb_downloads', array_sum($dataTable->getColumn(Piwik_Archive::INDEX_PAGE_NB_HITS)));
		$archiveProcessing->insertNumericRecord('Actions_nb_uniq_downloads', array_sum($dataTable->getColumn(Piwik_Archive::INDEX_NB_VISITS)));
		destroy($dataTable);

		$dataTable = $this->actionsTablesByType[Piwik_Tracker_Action::TYPE_OUTLINK];
		self::deleteInvalidSummedColumnsFromDataTable($dataTable);
		$s = $dataTable->getSerialized( Piwik_Actions_ArchivingHelper::$maximumRowsInDataTableLevelZero, Piwik_Actions_ArchivingHelper::$maximumRowsInSubDataTable, Piwik_Actions_ArchivingHelper::$columnToSortByBeforeTruncation );
		$archiveProcessing->insertBlobRecord('Actions_outlink', $s);
		$archiveProcessing->insertNumericRecord('Actions_nb_outlinks', array_sum($dataTable->getColumn(Piwik_Archive::INDEX_PAGE_NB_HITS)));
		$archiveProcessing->insertNumericRecord('Actions_nb_uniq_outlinks', array_sum($dataTable->getColumn(Piwik_Archive::INDEX_NB_VISITS)));
		destroy($dataTable);

		$dataTable = $this->actionsTablesByType[Piwik_Tracker_Action::TYPE_ACTION_NAME];
		self::deleteInvalidSummedColumnsFromDataTable($dataTable);
		$s = $dataTable->getSerialized( Piwik_Actions_ArchivingHelper::$maximumRowsInDataTableLevelZero, Piwik_Actions_ArchivingHelper::$maximumRowsInSubDataTable, Piwik_Actions_ArchivingHelper::$columnToSortByBeforeTruncation );
		$archiveProcessing->insertBlobRecord('Actions_actions', $s);
		destroy($dataTable);

		destroy($this->actionsTablesByType);
	}

	/**
	 * Returns the limit to use with RankingQuery for this plugin.
	 *
	 * @return int
	 */
	private static function getRankingQueryLimit()
	{
		$configGeneral = Piwik_Config::getInstance()->General;
		$configLimit = $configGeneral['archiving_ranking_query_row_limit'];
		return $configLimit == 0 ? 0 : max(
			$configLimit,
			$configGeneral['datatable_archiving_maximum_rows_actions'],
			$configGeneral['datatable_archiving_maximum_rows_subtable_actions']
		);
	}


	/**
	 * @param $select
	 * @param $from
	 * @param $where
	 * @param $orderBy
	 * @param $groupBy
	 * @param $sprintfField
	 * @param Piwik_ArchiveProcessing $archiveProcessing
	 * @param Piwik_RankingQuery|false $rankingQuery
	 * @return int
	 */
	protected function archiveDayQueryProcess($select, $from, $where, $orderBy, $groupBy,
	                                          $sprintfField, $archiveProcessing, $rankingQuery = false)
	{
		// idaction field needs to be set in select clause before calling getSelectQuery().
		// if a complex segmentation join is needed, the field needs to be propagated
		// to the outer select. therefore, $segment needs to know about it.
		$select = sprintf($select, $sprintfField);

		// get query with segmentation
		$bind = array();
		$query = $archiveProcessing->getSegment()->getSelectQuery(
			$select, $from, $where, $bind, $orderBy, $groupBy);

		// replace the rest of the %s
		$querySql = str_replace("%s", $sprintfField, $query['sql']);

		// apply ranking query
		if ($rankingQuery)
		{
			$querySql = $rankingQuery->generateQuery($querySql);
		}

		// extend bindings
		$bind = array_merge(array(  $archiveProcessing->getStartDatetimeUTC(),
									$archiveProcessing->getEndDatetimeUTC(),
									$archiveProcessing->idsite
							),
							$query['bind']
		);

		// get result
		$resultSet = $archiveProcessing->db->query($querySql, $bind);
		$modified = Piwik_Actions_ArchivingHelper::updateActionsTableWithRowQuery($resultSet, $sprintfField, $this->actionsTablesByType);
		return $modified;
	}


	/**
	 * For rows which have subtables (eg. directories with sub pages),
	 * deletes columns which don't make sense when all values of sub pages are summed.
	 *
	 * @param $dataTable Piwik_DataTable
	 */
	static public function deleteInvalidSummedColumnsFromDataTable($dataTable)
	{
		foreach($dataTable->getRows() as $id => $row)
		{
			if(($idSubtable = $row->getIdSubDataTable()) !== null
				|| $id === Piwik_DataTable::ID_SUMMARY_ROW)
			{
				if ($idSubtable !== null)
				{
					$subtable = Piwik_DataTable_Manager::getInstance()->getTable($idSubtable);
					self::deleteInvalidSummedColumnsFromDataTable($subtable);
				}
				
				if ($row instanceof Piwik_DataTable_Row_DataTableSummary)
				{
					$row->recalculate();
				}
				
				foreach(self::$invalidSummedColumnNameToDeleteFromDayArchive as $name)
				{
					$row->deleteColumn($name);
				}
			}
		}
	}

	/**
	 * Initializes the DataTables created by the archiveDay function.
	 */
	private function initActionsTables()
	{
		$this->actionsTablesByType = array();
		foreach (self::$actionTypes as $type)
		{
			$dataTable = new Piwik_DataTable();
			$dataTable->setMaximumAllowedRows(Piwik_Actions_ArchivingHelper::$maximumRowsInDataTableLevelZero);
			
			$this->actionsTablesByType[$type] = $dataTable;
		}
	}
}
