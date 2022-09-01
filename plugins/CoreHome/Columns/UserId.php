<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CoreHome\Columns;

use Piwik\Cache;
use Piwik\DataTable;
use Piwik\DataTable\Map;
use Piwik\Metrics;
use Piwik\Plugin;
use Piwik\Plugin\Dimension\VisitDimension;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visitor;
use Piwik\Tracker\Action;

/**
 * UserId dimension.
 */
class UserId extends VisitDimension
{
    const MAXLENGTH = 200;

    /**
     * @var string
     */
    protected $columnName = 'user_id';
    protected $type = self::TYPE_TEXT;
    protected $allowAnonymous = false;
    protected $segmentName = 'userId';
    protected $nameSingular = 'General_UserId';
    protected $namePlural = 'General_UserIds';
    protected $acceptValues = 'any non empty unique string identifying the user (such as an email address or a username).';

    public function __construct()
    {
        $this->columnType = 'VARCHAR(' . self::MAXLENGTH . ') NULL';

        if (Plugin\Manager::getInstance()->isPluginActivated('UserId')) {
            $this->suggestedValuesApi = 'UserId.getUsers';
        }
    }

    /**
     * @param Request $request
     * @param Visitor $visitor
     * @param Action|null $action
     * @return mixed|false
     */
    public function onNewVisit(Request $request, Visitor $visitor, $action)
    {
        $value = $request->getForcedUserId();
        if (!empty($value)) {
            return mb_substr($value, 0, self::MAXLENGTH);
        }
        return $value;
    }

    /**
     * @param Request $request
     * @param Visitor $visitor
     * @param Action|null $action
     *
     * @return mixed|false
     */
    public function onExistingVisit(Request $request, Visitor $visitor, $action)
    {
        return $this->onNewVisit($request, $visitor, $action);
    }

    public function isUsedInAtLeastOneSite($idSites, $period, $date)
    {
        if ($period === 'day' || $period === 'week') {
            $period = 'month';
        }

        if ($period === 'range') {
            $period = 'day';
        }

        if (!empty($idSites)) {
            foreach ($idSites as $idSite) {
                if ($this->isUsedInSiteCached($idSite, $period, $date)) {
                    return true;
                }
            }
        }

        return false;
    }

    private function isUsedInSiteCached($idSite, $period, $date)
    {
        $cache = Cache::getTransientCache();
        $key   = sprintf('%d.%s.%s', $idSite, $period, $date);

        if (!$cache->contains($key)) {
            $result = $this->isUsedInSite($idSite, $period, $date);
            $cache->save($key, $result);
        }

        return $cache->fetch($key);
    }

    private function isUsedInSite($idSite, $period, $date)
    {
        $result = \Piwik\API\Request::processRequest('VisitsSummary.get', [
            'columns' => 'nb_users',
            'idSite' => $idSite,
            'period' => $period,
            'date' => $date,
            'segment' => false,
        ], $default = []);

        return $this->hasDataTableUsers($result);
    }

    public function hasDataTableUsers(DataTable\DataTableInterface $result)
    {
        if ($result instanceof Map) {
            foreach ($result->getDataTables() as $table) {
                if ($this->hasDataTableUsers($table)) {
                    return true;
                }
            }
        }

        if (!$result->getRowsCount()) {
            return false;
        }

        $firstRow = $result->getFirstRow();
        if ($firstRow instanceof DataTable\Row && $firstRow->hasColumn(Metrics::INDEX_NB_USERS)) {
            $metric = Metrics::INDEX_NB_USERS;
        } else {
            $metric = 'nb_users';
        }

        $numUsers = $result->getColumn($metric);
        $numUsers = array_sum($numUsers);

        return !empty($numUsers);
    }

}