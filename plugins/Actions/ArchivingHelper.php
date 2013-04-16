<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_Actions
 */

/**
 * This static class provides:
 * - logic to parse/cleanup Action names,
 * - logic to efficiently process aggregate the array data during Archiving
 *
 * @package Piwik_Actions
 */

class Piwik_Actions_ArchivingHelper
{
    const OTHERS_ROW_KEY = '';

    /**
     * FIXME See FIXME related to this function at Piwik_Actions_Archiving::archiveDay.
     * 
     * @param Zend_Db_Statement|PDOStatement $query
     * @param string|bool $fieldQueried
     * @param array $actionsTablesByType
     * @return int
     */
    static public function updateActionsTableWithRowQuery($query, $fieldQueried, & $actionsTablesByType)
    {
        $rowsProcessed = 0;
        while ($row = $query->fetch()) {
            if (empty($row['idaction'])) {
                $row['type'] = ($fieldQueried == 'idaction_url' ? Piwik_Tracker_Action::TYPE_ACTION_URL : Piwik_Tracker_Action::TYPE_ACTION_NAME);
                // This will be replaced with 'X not defined' later
                $row['name'] = '';
                // Yes, this is kind of a hack, so we don't mix 'page url not defined' with 'page title not defined' etc.
                $row['idaction'] = -$row['type'];
            }

            if ($row['type'] != Piwik_Tracker_Action::TYPE_SITE_SEARCH) {
                unset($row[Piwik_Archive::INDEX_SITE_SEARCH_HAS_NO_RESULT]);
            }

            // This will appear as <url /> in the API, which is actually very important to keep
            // eg. When there's at least one row in a report that does not have a URL, not having this <url/> would break HTML/PDF reports.
            $url = '';
            if ($row['type'] == Piwik_Tracker_Action::TYPE_SITE_SEARCH
                || $row['type'] == Piwik_Tracker_Action::TYPE_ACTION_NAME
            ) {
                $url = null;
            } elseif (!empty($row['name'])
                && $row['name'] != Piwik_DataTable::LABEL_SUMMARY_ROW
            ) {
                $url = Piwik_Tracker_Action::reconstructNormalizedUrl((string)$row['name'], $row['url_prefix']);
            }

            if (isset($row['name'])
                && isset($row['type'])
            ) {
                $actionName = $row['name'];
                $actionType = $row['type'];
                $urlPrefix = $row['url_prefix'];
                $idaction = $row['idaction'];

                // in some unknown case, the type field is NULL, as reported in #1082 - we ignore this page view
                if (empty($actionType)) {
                    if ($idaction != Piwik_DataTable::LABEL_SUMMARY_ROW) {
                        self::setCachedActionRow($idaction, $actionType, false);
                    }
                    continue;
                }

                $actionRow = self::getActionRow($actionName, $actionType, $urlPrefix, $actionsTablesByType);

                self::setCachedActionRow($idaction, $actionType, $actionRow);
            } else {
                $actionRow = self::getCachedActionRow($row['idaction'], $row['type']);

                // Action processed as "to skip" for some reasons
                if ($actionRow === false) {
                    continue;
                }
            }


            if (is_null($actionRow)) {
                continue;
            }

            // Here we do ensure that, the Metadata URL set for a given row, is the one from the Pageview with the most hits.
            // This is to ensure that when, different URLs are loaded with the same page name.
            // For example http://piwik.org and http://id.piwik.org are reported in Piwik > Actions > Pages with /index
            // But, we must make sure http://piwik.org is used to link & for transitions
            // Note: this code is partly duplicated from Piwik_DataTable_Row->sumRowMetadata()
            if (!is_null($url)
                && !$actionRow->isSummaryRow()
            ) {
                if (($existingUrl = $actionRow->getMetadata('url')) !== false) {
                    if (!empty($row[Piwik_Archive::INDEX_PAGE_NB_HITS])
                        && $row[Piwik_Archive::INDEX_PAGE_NB_HITS] > $actionRow->maxVisitsSummed
                    ) {
                        $actionRow->setMetadata('url', $url);
                        $actionRow->maxVisitsSummed = $row[Piwik_Archive::INDEX_PAGE_NB_HITS];
                    }
                } else {
                    $actionRow->setMetadata('url', $url);
                    $actionRow->maxVisitsSummed = !empty($row[Piwik_Archive::INDEX_PAGE_NB_HITS]) ? $row[Piwik_Archive::INDEX_PAGE_NB_HITS] : 0;
                }
            }

            if ($row['type'] != Piwik_Tracker_Action::TYPE_ACTION_URL
                && $row['type'] != Piwik_Tracker_Action::TYPE_ACTION_NAME
            ) {
                // only keep performance metrics when they're used (i.e. for URLs and page titles)
                if (array_key_exists(Piwik_Archive::INDEX_PAGE_SUM_TIME_GENERATION, $row)) {
                    unset($row[Piwik_Archive::INDEX_PAGE_SUM_TIME_GENERATION]);
                }
                if (array_key_exists(Piwik_Archive::INDEX_PAGE_NB_HITS_WITH_TIME_GENERATION, $row)) {
                    unset($row[Piwik_Archive::INDEX_PAGE_NB_HITS_WITH_TIME_GENERATION]);
                }
                if (array_key_exists(Piwik_Archive::INDEX_PAGE_MIN_TIME_GENERATION, $row)) {
                    unset($row[Piwik_Archive::INDEX_PAGE_MIN_TIME_GENERATION]);
                }
                if (array_key_exists(Piwik_Archive::INDEX_PAGE_MAX_TIME_GENERATION, $row)) {
                    unset($row[Piwik_Archive::INDEX_PAGE_MAX_TIME_GENERATION]);
                }
            }

            unset($row['name']);
            unset($row['type']);
            unset($row['idaction']);
            unset($row['url_prefix']);

            foreach ($row as $name => $value) {
                // in some edge cases, we have twice the same action name with 2 different idaction
                // - this happens when 2 visitors visit the same new page at the same time, and 2 actions get recorded for the same name
                // - this could also happen when 2 URLs end up having the same label (eg. 2 subdomains get aggregated to the "/index" page name)
                if (($alreadyValue = $actionRow->getColumn($name)) !== false) {
                    $newValue = self::getColumnValuesMerged($name, $alreadyValue, $value);
                    $actionRow->setColumn($name, $newValue);
                } else {
                    $actionRow->addColumn($name, $value);
                }
            }

            // if the exit_action was not recorded properly in the log_link_visit_action
            // there would be an error message when getting the nb_hits column
            // we must fake the record and add the columns
            if ($actionRow->getColumn(Piwik_Archive::INDEX_PAGE_NB_HITS) === false) {
                // to test this code: delete the entries in log_link_action_visit for
                //  a given exit_idaction_url
                foreach (self::getDefaultRow()->getColumns() as $name => $value) {
                    $actionRow->addColumn($name, $value);
                }
            }
            $rowsProcessed++;
        }

        // just to make sure php copies the last $actionRow in the $parentTable array
        $actionRow =& $actionsTablesByType;
        return $rowsProcessed;
    }

    /**
     * @param $columnName
     * @param $alreadyValue
     * @param $value
     * @return mixed
     */
    private static function getColumnValuesMerged($columnName, $alreadyValue, $value)
    {
        if ($columnName == Piwik_Archive::INDEX_PAGE_MIN_TIME_GENERATION) {
            if (empty($alreadyValue)) {
                $newValue = $value;
            } else if (empty($value)) {
                $newValue = $alreadyValue;
            } else {
                $newValue = min($alreadyValue, $value);
            }
            return $newValue;
        }
        if ($columnName == Piwik_Archive::INDEX_PAGE_MAX_TIME_GENERATION) {
            $newValue = max($alreadyValue, $value);
            return $newValue;
        }

        $newValue = $alreadyValue + $value;
        return $newValue;
    }

    static public $maximumRowsInDataTableLevelZero;
    static public $maximumRowsInSubDataTable;
    static public $columnToSortByBeforeTruncation;

    static protected $actionUrlCategoryDelimiter = null;
    static protected $actionTitleCategoryDelimiter = null;
    static protected $defaultActionName = null;
    static protected $defaultActionNameWhenNotDefined = null;
    static protected $defaultActionUrlWhenNotDefined = null;

    static public function reloadConfig()
    {
        // for BC, we read the old style delimiter first (see #1067)Row
        $actionDelimiter = @Piwik_Config::getInstance()->General['action_category_delimiter'];
        if (empty($actionDelimiter)) {
            self::$actionUrlCategoryDelimiter = Piwik_Config::getInstance()->General['action_url_category_delimiter'];
            self::$actionTitleCategoryDelimiter = Piwik_Config::getInstance()->General['action_title_category_delimiter'];
        } else {
            self::$actionUrlCategoryDelimiter = self::$actionTitleCategoryDelimiter = $actionDelimiter;
        }

        self::$defaultActionName = Piwik_Config::getInstance()->General['action_default_name'];
        self::$columnToSortByBeforeTruncation = Piwik_Archive::INDEX_NB_VISITS;
        self::$maximumRowsInDataTableLevelZero = Piwik_Config::getInstance()->General['datatable_archiving_maximum_rows_actions'];
        self::$maximumRowsInSubDataTable = Piwik_Config::getInstance()->General['datatable_archiving_maximum_rows_subtable_actions'];

        Piwik_DataTable::setMaximumDepthLevelAllowedAtLeast(self::getSubCategoryLevelLimit() + 1);
    }


    /**
     * The default row is used when archiving, if data is inconsistent in the DB,
     * there could be pages that have exit/entry hits, but don't yet
     * have a record in the table (or the record was truncated).
     *
     * @return Piwik_DataTable_Row
     */
    static private function getDefaultRow()
    {
        static $row = false;
        if ($row === false) {
            // This row is used in the case where an action is know as an exit_action
            // but this action was not properly recorded when it was hit in the first place
            // so we add this fake row information to make sure there is a nb_hits, etc. column for every action
            $row = new Piwik_DataTable_Row(array(
                                                Piwik_DataTable_Row::COLUMNS => array(
                                                    Piwik_Archive::INDEX_NB_VISITS        => 1,
                                                    Piwik_Archive::INDEX_NB_UNIQ_VISITORS => 1,
                                                    Piwik_Archive::INDEX_PAGE_NB_HITS     => 1,
                                                )));
        }
        return $row;
    }

    /**
     * Given a page name and type, builds a recursive datatable where
     * each level of the tree is a category, based on the page name split by a delimiter (slash / by default)
     *
     * @param string $actionName
     * @param int $actionType
     * @param int $urlPrefix
     * @param array $actionsTablesByType
     * @return Piwik_DataTable
     */
    protected static function getActionRow($actionName, $actionType, $urlPrefix = null, &$actionsTablesByType)
    {
        // we work on the root table of the given TYPE (either ACTION_URL or DOWNLOAD or OUTLINK etc.)
        /* @var Piwik_DataTable $currentTable */
        $currentTable =& $actionsTablesByType[$actionType];

        // check for ranking query cut-off
        if ($actionName == Piwik_DataTable::LABEL_SUMMARY_ROW) {
            $summaryRow = $currentTable->getRowFromId(Piwik_DataTable::ID_SUMMARY_ROW);
            if ($summaryRow === false) {
                $summaryRow = $currentTable->addSummaryRow(self::createSummaryRow());
            }
            return $summaryRow;
        }

        // go to the level of the subcategory
        $actionExplodedNames = self::getActionExplodedNames($actionName, $actionType, $urlPrefix);
        list($row, $level) = $currentTable->walkPath(
            $actionExplodedNames, self::getDefaultRowColumns(), self::$maximumRowsInSubDataTable);

        return $row;
    }

    /**
     * Explodes action name into an array of elements.
     *
     * NOTE: before calling this function make sure Piwik_Actions_ArchivingHelper::reloadConfig(); is called
     *
     * for downloads:
     *  we explode link http://piwik.org/some/path/piwik.zip into an array( 'piwik.org', '/some/path/piwik.zip' );
     *
     * for outlinks:
     *  we explode link http://dev.piwik.org/some/path into an array( 'dev.piwik.org', '/some/path' );
     *
     * for action urls:
     *  we explode link http://piwik.org/some/path into an array( 'some', 'path' );
     *
     * for action names:
     *   we explode name 'Piwik / Category 1 / Category 2' into an array('Piwik', 'Category 1', 'Category 2');
     *
     * @param string action name
     * @param int action type
     * @param int url prefix (only used for TYPE_ACTION_URL)
     * @return array of exploded elements from $name
     */
    static public function getActionExplodedNames($name, $type, $urlPrefix = null)
    {
        // Site Search does not split Search keywords
        if ($type == Piwik_Tracker_Action::TYPE_SITE_SEARCH) {
            return array($name);
        }

        $matches = array();
        $isUrl = false;
        $name = str_replace("\n", "", $name);

        $urlRegexAfterDomain = '([^/]+)[/]?([^#]*)[#]?(.*)';
        if ($urlPrefix === null) {
            // match url with protocol (used for outlinks / downloads)
            $urlRegex = '@^http[s]?://' . $urlRegexAfterDomain . '$@i';
        } else {
            // the name is a url that does not contain protocol and www anymore
            // we know that normalization has been done on db level because $urlPrefix is set
            $urlRegex = '@^' . $urlRegexAfterDomain . '$@i';
        }

        preg_match($urlRegex, $name, $matches);
        if (count($matches)) {
            $isUrl = true;
            $urlHost = $matches[1];
            $urlPath = $matches[2];
            $urlFragment = $matches[3];
        }

        if ($type == Piwik_Tracker_Action::TYPE_DOWNLOAD
            || $type == Piwik_Tracker_Action::TYPE_OUTLINK
        ) {
            if ($isUrl) {
                return array(trim($urlHost), '/' . trim($urlPath));
            }
        }

        if ($isUrl) {
            $name = $urlPath;

            if ($name === '' || substr($name, -1) == '/') {
                $name .= self::$defaultActionName;
            }
        }

        if ($type == Piwik_Tracker_Action::TYPE_ACTION_NAME) {
            $categoryDelimiter = self::$actionTitleCategoryDelimiter;
        } else {
            $categoryDelimiter = self::$actionUrlCategoryDelimiter;
        }


        if ($isUrl) {
            $urlFragment = Piwik_Tracker_Action::processUrlFragment($urlFragment);
            if (!empty($urlFragment)) {
                $name .= '#' . $urlFragment;
            }
        }

        if (empty($categoryDelimiter)) {
            return array(trim($name));
        }

        $split = explode($categoryDelimiter, $name, self::getSubCategoryLevelLimit());

        // trim every category and remove empty categories
        $split = array_map('trim', $split);
        $split = array_filter($split, 'strlen');

        // forces array key to start at 0
        $split = array_values($split);

        if (empty($split)) {
            $defaultName = self::getUnknownActionName($type);
            return array(trim($defaultName));
        }

        $lastPageName = end($split);
        // we are careful to prefix the page URL / name with some value
        // so that if a page has the same name as a category
        // we don't merge both entries
        if ($type != Piwik_Tracker_Action::TYPE_ACTION_NAME) {
            $lastPageName = '/' . $lastPageName;
        } else {
            $lastPageName = ' ' . $lastPageName;
        }
        $split[count($split) - 1] = $lastPageName;
        return array_values($split);
    }

    /**
     * Gets the key for the cache of action rows from an action ID and type.
     *
     * @param int $idAction
     * @param int $actionType
     * @return string|int
     */
    private static function getCachedActionRowKey($idAction, $actionType)
    {
        return $idAction == Piwik_DataTable::LABEL_SUMMARY_ROW
            ? $actionType . '_others'
            : $idAction;
    }

    /**
     * Returns the configured sub-category level limit.
     *
     * @return int
     */
    public static function getSubCategoryLevelLimit()
    {
        return Piwik_Config::getInstance()->General['action_category_level_limit'];
    }

    /**
     * Returns default label for the action type
     *
     * @param $type
     * @return string
     */
    static public function getUnknownActionName($type)
    {
        if (empty(self::$defaultActionNameWhenNotDefined)) {
            self::$defaultActionNameWhenNotDefined = Piwik_Translate('General_NotDefined', Piwik_Translate('Actions_ColumnPageName'));
            self::$defaultActionUrlWhenNotDefined = Piwik_Translate('General_NotDefined', Piwik_Translate('Actions_ColumnPageURL'));
        }
        if ($type == Piwik_Tracker_Action::TYPE_ACTION_NAME) {
            return self::$defaultActionNameWhenNotDefined;
        }
        return self::$defaultActionUrlWhenNotDefined;
    }

    /**
     * Static cache to store Rows during processing
     */
    static protected $cacheParsedAction = array();

    public static function clearActionsCache()
    {
        self::$cacheParsedAction = array();
    }

    /**
     * Get cached action row by id & type. If $idAction is set to -1, the 'Others' row
     * for the specific action type will be returned.
     *
     * @param int $idAction
     * @param int $actionType
     * @return Piwik_DataTable_Row|false
     */
    private static function getCachedActionRow($idAction, $actionType)
    {
        $cacheLabel = self::getCachedActionRowKey($idAction, $actionType);

        if (!isset(self::$cacheParsedAction[$cacheLabel])) {
            // This can happen when
            // - We select an entry page ID that was only seen yesterday, so wasn't selected in the first query
            // - We count time spent on a page, when this page was only seen yesterday
            return false;
        }

        return self::$cacheParsedAction[$cacheLabel];
    }

    /**
     * Set cached action row for an id & type.
     *
     * @param int $idAction
     * @param int $actionType
     * @param Piwik_DataTable_Row
     */
    private static function setCachedActionRow($idAction, $actionType, $actionRow)
    {
        $cacheLabel = self::getCachedActionRowKey($idAction, $actionType);
        self::$cacheParsedAction[$cacheLabel] = $actionRow;
    }

    /**
     * Returns the default columns for a row in an Actions DataTable.
     *
     * @return array
     */
    private static function getDefaultRowColumns()
    {
        return array(Piwik_Archive::INDEX_NB_VISITS           => 0,
                     Piwik_Archive::INDEX_NB_UNIQ_VISITORS    => 0,
                     Piwik_Archive::INDEX_PAGE_NB_HITS        => 0,
                     Piwik_Archive::INDEX_PAGE_SUM_TIME_SPENT => 0);
    }

    /**
     * Creates a summary row for an Actions DataTable.
     *
     * @return Piwik_DataTable_Row
     */
    private static function createSummaryRow()
    {
        return new Piwik_DataTable_Row(array(
                                            Piwik_DataTable_Row::COLUMNS =>
                                            array('label' => Piwik_DataTable::LABEL_SUMMARY_ROW) + self::getDefaultRowColumns()
                                       ));
    }
}
