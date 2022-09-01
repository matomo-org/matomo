<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugin;

use Piwik\Cache;
use Piwik\CacheId;
use Piwik\Category\CategoryList;
use Piwik\Common;
use Piwik\Piwik;
use Piwik\Plugin;
use Piwik\Cache as PiwikCache;
use Piwik\Site;

/**
 * Get reports that are defined by plugins.
 */
class ReportsProvider
{
    private $categoryList;

    /**
     * Get an instance of a specific report belonging to the given module and having the given action.
     * @param  string $module
     * @param  string $action
     * @return null|\Piwik\Plugin\Report
     * @api
     */
    public static function factory($module, $action)
    {
        $listApiToReport = self::getMapOfModuleActionsToReport();
        $api = $module . '.' . ucfirst($action);

        if (!array_key_exists($api, $listApiToReport)) {
            return null;
        }

        $klassName = $listApiToReport[$api];

        return new $klassName;
    }

    private static function getMapOfModuleActionsToReport()
    {
        $cacheKey = 'ReportFactoryMap';
        $idSite = Common::getRequestVar('idSite', 0, 'int');

        if (!empty($idSite)) {
            // some reports may be per site!
            $cacheKey .= '_' . (int) $idSite;
        }

        // fallback eg fror API.getReportMetadata and API.getSegmentsMetadata
        $idSites = Common::getRequestVar('idSites', '', $type = null);
        if (!empty($idSites)) {

            $transientCache = Cache::getTransientCache();
            $transientCacheKey = 'ReportIdSitesParam';
            if ($transientCache->contains($transientCacheKey)) {
                $idSites = $transientCache->fetch($transientCacheKey);
            } else {
                // this may be called 100 times during one page request and may go to DB, therefore have to cache
                $idSites = Site::getIdSitesFromIdSitesString($idSites);
                sort($idSites);// we sort to reuse the cache key as often as possible
                $transientCache->save($transientCacheKey, $idSites);
            }

            // it is important to not use either idsite, or idsites in the cache key but to include both for security reasons
            // otherwise someone may specify idSite=5&idSites=7 and if then a plugin is eg only looking at idSites param
            // we could return a wrong result (eg API.getSegmentsMetadata)
            if (count($idSites) <= 5) {
                $cacheKey .= '_' . implode('_', $idSites); // we keep the cache key readable when possible
            } else {
                $cacheKey .= '_' . md5(implode('_', $idSites)); // we need to shorten it
            }
        }

        $lazyCacheId = CacheId::pluginAware($cacheKey);

        $cache = PiwikCache::getLazyCache();
        $mapApiToReport = $cache->fetch($lazyCacheId);

        if (empty($mapApiToReport)) {
            $reports = new static();
            $reports = $reports->getAllReports();

            $mapApiToReport = array();
            foreach ($reports as $report) {
                $key = $report->getModule() . '.' . ucfirst($report->getAction());

                if (isset($mapApiToReport[$key]) && $report->getParameters()) {
                    // sometimes there are multiple reports with same module/action but different parameters.
                    // we might pick the "wrong" one. At some point we should compare all parameters and if there is
                    // a report which parameters mach $_REQUEST then we should prefer that report
                    continue;
                }
                $mapApiToReport[$key] = get_class($report);
            }

            $cache->save($lazyCacheId, $mapApiToReport, $lifeTime = 3600);
        }

        return $mapApiToReport;
    }

    /**
     * Returns a list of all available reports. Even not enabled reports will be returned. They will be already sorted
     * depending on the order and category of the report.
     * @return \Piwik\Plugin\Report[]
     * @api
     */
    public function getAllReports()
    {
        $reports = $this->getAllReportClasses();
        $cacheId = CacheId::siteAware(CacheId::languageAware('Reports' . md5(implode('', $reports))));
        $cache   = PiwikCache::getTransientCache();

        if (!$cache->contains($cacheId)) {
            $instances = array();

            /**
             * Triggered to add new reports that cannot be picked up automatically by the platform.
             * This is useful if the plugin allows a user to create reports / dimensions dynamically. For example
             * CustomDimensions or CustomVariables. There are a variable number of dimensions in this case and it
             * wouldn't be really possible to create a report file for one of these dimensions as it is not known
             * how many Custom Dimensions will exist.
             *
             * **Example**
             *
             *     public function addReport(&$reports)
             *     {
             *         $reports[] = new MyCustomReport();
             *     }
             *
             * @param Report[] $reports An array of reports
             */
            Piwik::postEvent('Report.addReports', array(&$instances));

            foreach ($reports as $report) {
                $instances[] = new $report();
            }

            /**
             * Triggered to filter / restrict reports.
             *
             * **Example**
             *
             *     public function filterReports(&$reports)
             *     {
             *         foreach ($reports as $index => $report) {
             *              if ($report->getCategoryId() === 'General_Actions') {
             *                  unset($reports[$index]); // remove all reports having this action
             *              }
             *         }
             *     }
             *
             * @param Report[] $reports An array of reports
             */
            Piwik::postEvent('Report.filterReports', array(&$instances));

            @usort($instances, array($this, 'sort'));

            $cache->save($cacheId, $instances);
        }

        return $cache->fetch($cacheId);
    }

    /**
     * API metadata are sorted by category/name,
     * with a little tweak to replicate the standard Piwik category ordering
     *
     * @param Report $a
     * @param Report $b
     * @return int
     */
    private function sort($a, $b)
    {
        $result = $this->compareCategories($a->getCategoryId(), $a->getSubcategoryId(), $a->getOrder(), $b->getCategoryId(), $b->getSubcategoryId(), $b->getOrder());

        // if categories are equal, sort by ID
        if (!$result) {
            $aId = $a->getId();
            $bId = $b->getId();

            if ($aId == $bId) {
                return 0;
            }

            return $aId < $bId ? -1 : 1;
        }

        return $result;
    }

    public function compareCategories($catIdA, $subcatIdA, $orderA, $catIdB, $subcatIdB, $orderB)
    {
        if (!isset($this->categoryList)) {
            $this->categoryList = CategoryList::get();
        }

        $catA = $this->categoryList->getCategory($catIdA);
        $catB = $this->categoryList->getCategory($catIdB);

        // in case there is a category class for both reports
        if (isset($catA) && isset($catB)) {

            if ($catA->getOrder() == $catB->getOrder()) {
                // same category order, compare subcategory order
                $subcatA = $catA->getSubcategory($subcatIdA);
                $subcatB = $catB->getSubcategory($subcatIdB);

                // both reports have a subcategory with custom subcategory class
                if ($subcatA && $subcatB) {
                    if ($subcatA->getOrder() == $subcatB->getOrder()) {
                        // same subcategory order, compare order of report

                        if ($orderA == $orderB) {
                            return 0;
                        }

                        return $orderA < $orderB ? -1 : 1;
                    }

                    return $subcatA->getOrder() < $subcatB->getOrder() ? -1 : 1;

                } elseif ($subcatA) {
                    return 1;
                } elseif ($subcatB) {
                    return -1;
                }

                if ($orderA == $orderB) {
                    return 0;
                }

                return $orderA < $orderB ? -1 : 1;
            }

            return $catA->getOrder() < $catB->getOrder() ? -1 : 1;

        } elseif (isset($catA)) {
            return -1;
        } elseif (isset($catB)) {
            return 1;
        }

        if ($catIdA === $catIdB) {
            // both have same category, compare order
            if ($orderA == $orderB) {
                return 0;
            }

            return $orderA < $orderB ? -1 : 1;
        }

        return strnatcasecmp($catIdA, $catIdB);
    }

    /**
     * Returns class names of all Report metadata classes.
     *
     * @return string[]
     * @api
     */
    public function getAllReportClasses()
    {
        return Plugin\Manager::getInstance()->findMultipleComponents('Reports', '\\Piwik\\Plugin\\Report');
    }

    //Added this to trigger reset of category list as the list never gets rest after setting up due to isset check and affects testcases
    public function unsetCategoryList()
    {
        unset($this->categoryList);
    }
}
