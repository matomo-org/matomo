<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CustomDimensions;

use Piwik\Common;
use Piwik\Archive;
use Piwik\DataTable;
use Piwik\Filesystem;
use Piwik\Piwik;
use Piwik\Plugins\CustomDimensions\Dao\Configuration;
use Piwik\Plugins\CustomDimensions\Dao\LogTable;
use Piwik\Plugins\CustomDimensions\Dimension\Active;
use Piwik\Plugins\CustomDimensions\Dimension\CaseSensitive;
use Piwik\Plugins\CustomDimensions\Dimension\Dimension;
use Piwik\Plugins\CustomDimensions\Dimension\Extraction;
use Piwik\Plugins\CustomDimensions\Dimension\Extractions;
use Piwik\Plugins\CustomDimensions\Dimension\Index;
use Piwik\Plugins\CustomDimensions\Dimension\Name;
use Piwik\Plugins\CustomDimensions\Dimension\Scope;
use Piwik\Tracker\Cache;

/**
 * The Custom Dimensions API lets you manage and access reports for your configured Custom Dimensions.
 *
 * @method static API getInstance()
 */
class API extends \Piwik\Plugin\API
{
    /**
     * Returns the report for a configured custom dimension. Only reports for active dimensions can be fetched.
     *
     * @param int $idDimension Custom dimension ID to load the report for.
     * @param int $idSite The numeric ID of the website to query.
     * @param 'day'|'week'|'month'|'year'|'range' $period The period to process, processes data for the period
     *                                                    containing the specified date.
     * @param string $date The date or date range to process.
     *                     'YYYY-MM-DD', magic keywords (today, yesterday, lastWeek, lastMonth, lastYear),
     *                     or date range (ie, 'YYYY-MM-DD,YYYY-MM-DD', lastX, previousX).
     * @param string|null|false $segment Custom segment to filter the report.
     *                                   Example: "referrerName==example.com"
     *                                   Supports AND (;) and OR (,) operators.
     * @param bool $expanded Whether subtables should be expanded in the response.
     * @param bool $flat Whether subtable rows should be flattened into a single table.
     * @param int|false $idSubtable Optional subtable ID to load.
     * @return DataTable|DataTable\Map
     */
    public function getCustomDimension(int $idDimension, int $idSite, string $period, string $date, $segment = false, bool $expanded = false, bool $flat = false, $idSubtable = false)
    {
        Piwik::checkUserHasViewAccess($idSite);

        $dimension = new Dimension($idDimension, $idSite);
        $dimension->checkActive();

        $record = Archiver::buildRecordNameForCustomDimensionId($idDimension);

        $dataTable = Archive::createDataTableFromArchive($record, $idSite, $period, $date, $segment ?: '', $expanded, $flat, $idSubtable);

        if (!empty($idSubtable) && $dataTable->getRowsCount()) {
            $parentTable = Archive::createDataTableFromArchive($record, $idSite, $period, $date, $segment ?: '');
            $row = $parentTable->getRowFromIdSubDataTable($idSubtable);
            if ($row) {
                $parentValue = $row->getColumn('label');
                $dataTable->filter('Piwik\Plugins\CustomDimensions\DataTable\Filter\AddSubtableSegmentMetadata', array($idDimension, $parentValue));
            }
        } else {
            $dataTable->filter('Piwik\Plugins\CustomDimensions\DataTable\Filter\AddSegmentMetadata', array($idDimension));
        }

        $dataTable->filter('Piwik\Plugins\CustomDimensions\DataTable\Filter\RemoveUserIfNeeded', array($idSite, $period, $date));

        return $dataTable;
    }

    /**
     * Configures a new custom dimension for a site. Note that custom dimensions cannot be deleted, so be careful
     * when creating one as you might run out of available custom dimension slots.
     *
     * A current list of available scopes can be fetched via `CustomDimensions.getAvailableScopes`. That method
     * also indicates whether custom dimension slots are still available or all in use.
     *
     * @param int $idSite The numeric ID of the website to configure the dimension for.
     * @param string $name The custom dimension name.
     * @param 'visit'|'action' $scope The dimension scope. Use `CustomDimensions.getAvailableScopes` for an
     *                                up-to-date list.
     * @param bool|int $active Whether the custom dimension should be active.
     * @param array<int, array{dimension:string, pattern:string}> $extractions Optional extraction rules, e.g.
     *                 `[{"dimension": "url", "pattern": "index_(.+).html"}, {"dimension": "urlparam", "pattern": "..."}]`.
     *                 Supported dimensions include `url`, `urlparam`, and `action_name`. Use
     *                 `CustomDimensions.getAvailableExtractionDimensions` for the full list.
     *                 Extractions are supported only for the `action` scope.
     * @param bool|int $caseSensitive Whether extraction matching should be case-sensitive.
     * @return int ID of the configured custom dimension. Note that the same ID may be used for different websites.
     */
    public function configureNewCustomDimension(int $idSite, string $name, string $scope, $active, $extractions = [], $caseSensitive = true)
    {
        Piwik::checkUserHasWriteAccess($idSite);

        $this->checkCustomDimensionConfig($name, $active, $extractions, $caseSensitive);

        $scopeCheck = new Scope($scope);
        $scopeCheck->check();

        $extractions = $this->unsanitizeExtractions($extractions);
        $this->checkExtractionsAreSupportedForScope($scope, $extractions);

        $index = new Index();
        $index = $index->getNextIndex($idSite, $scope);

        $configuration = $this->getConfiguration();
        $idDimension   = $configuration->configureNewDimension($idSite, $name, $scope, $index, $active, $extractions, $caseSensitive);

        Cache::deleteCacheWebsiteAttributes($idSite);
        Cache::clearCacheGeneral();
        Filesystem::deleteAllCacheOnUpdate();

        return $idDimension;
    }

    /**
     * @param array<int, array{dimension?:mixed, pattern?:mixed}> $extractions
     * @return array<int, array{dimension?:mixed, pattern?:mixed}>
     */
    private function unsanitizeExtractions($extractions)
    {
        if (!empty($extractions) && is_array($extractions)) {
            foreach ($extractions as $index => $extraction) {
                if (!empty($extraction['pattern']) && is_string($extraction['pattern'])) {
                    $extractions[$index]['pattern'] = Common::unsanitizeInputValue($extraction['pattern']);
                }
            }
        }

        return $extractions;
    }

    /**
     * Updates an existing custom dimension. This method updates all values, so you need to pass existing values
     * of the dimension if you do not want to reset any value.
     *
     * @param int $idDimension Custom dimension ID to update.
     * @param int $idSite The numeric ID of the website the dimension belongs to.
     * @param string $name The custom dimension name.
     * @param bool|int $active Whether the custom dimension should be active.
     * @param array<int, array{dimension:string, pattern:string}> $extractions Optional extraction rules, e.g.
     *                 `[{"dimension": "url", "pattern": "index_(.+).html"}, {"dimension": "urlparam", "pattern": "..."}]`.
     *                 Supported dimensions include `url`, `urlparam`, and `action_name`. Use
     *                 `CustomDimensions.getAvailableExtractionDimensions` for the full list.
     *                 Extractions are supported only for the `action` scope.
     * @param bool|int|null $caseSensitive Whether extraction matching should be case-sensitive.
     *                                     Use `null` to keep the current setting.
     */
    public function configureExistingCustomDimension(int $idDimension, int $idSite, string $name, $active, $extractions = [], $caseSensitive = null): void
    {
        Piwik::checkUserHasWriteAccess($idSite);

        $dimension = new Dimension($idDimension, $idSite);
        $dimension->checkExists();

        if (!isset($caseSensitive)) {
            $caseSensitive = $dimension->getCaseSensitive();
        }

        $extractions = $this->unsanitizeExtractions($extractions);
        $this->checkCustomDimensionConfig($name, $active, $extractions, $caseSensitive);
        $this->checkExtractionsAreSupportedForScope($dimension->getScope(), $extractions);

        $this->getConfiguration()->configureExistingDimension($idDimension, $idSite, $name, $active, $extractions, $caseSensitive);

        Cache::deleteCacheWebsiteAttributes($idSite);
        Cache::clearCacheGeneral();
    }

    /**
     * @param string $scope
     * @param array<int, array{dimension:string, pattern:string}> $extractions
     */
    private function checkExtractionsAreSupportedForScope($scope, $extractions): void
    {
        if (!CustomDimensions::doesScopeSupportExtractions($scope) && !empty($extractions)) {
            throw new \Exception("Extractions can be used only in scope 'action'");
        }
    }

    /**
     * Returns all configured custom dimensions for a site.
     *
     * @param int $idSite The numeric ID of the website to query.
     * @return array<int, array<string, mixed>>
     */
    public function getConfiguredCustomDimensions(int $idSite)
    {
        Piwik::checkUserHasViewAccess($idSite);

        return $this->getConfiguration()->getCustomDimensionsForSite($idSite);
    }

    /**
     * For convenience. Hidden to reduce API surface area.
     *
     * @param int $idSite The numeric ID of the website to query.
     * @param string $scope Scope to filter configured dimensions by.
     * @return array<int, array<string, mixed>>
     * @hide
     */
    public function getConfiguredCustomDimensionsHavingScope(int $idSite, $scope)
    {
        $result = $this->getConfiguredCustomDimensions($idSite);
        $result = array_filter($result, function ($row) use ($scope) {
            return $row['scope'] == $scope;
        });
        return array_values($result);
    }

    /**
     * @param bool|int $active
     * @param array<int, array{dimension:string, pattern:string}> $extractions
     * @param bool|int|null $caseSensitive
     */
    private function checkCustomDimensionConfig(string $name, $active, $extractions, $caseSensitive): void
    {
        // ideally we would work with these objects a bit more instead of arrays but we'd have a lot of
        // serialize/unserialize to do as we need to cache all configured custom dimensions for tracker cache and
        // we do not want to serialize all php instances there. Also we need to return an array for each
        // configured dimension in API methods anyway

        $name = new Name($name);
        $name->check();

        $active = new Active($active);
        $active->check();

        $extractions = new Extractions($extractions);
        $extractions->check();

        $caseSensitive = new CaseSensitive($caseSensitive);
        $caseSensitive->check();
    }

    /**
     * Returns the supported custom-dimension scopes for a site. The response also contains information about
     * how many custom dimension slots are available, used, and remaining, which can be used to check whether
     * more custom dimensions can be created via `CustomDimensions.configureNewCustomDimension`.
     *
     * @param int $idSite The numeric ID of the website to query.
     * @return array<int, array{value:string, name:string, numSlotsAvailable:int, numSlotsUsed:int, numSlotsLeft:int, supportsExtractions:bool}>
     */
    public function getAvailableScopes(int $idSite): array
    {
        Piwik::checkUserHasViewAccess($idSite);

        $scopes = [];
        foreach (CustomDimensions::getPublicScopes() as $scope) {
            $configs = $this->getConfiguredCustomDimensionsHavingScope($idSite, $scope);
            $indexes = $this->getTracking($scope)->getInstalledIndexes();

            $scopes[] = [
                'value' => $scope,
                'name' => Piwik::translate('General_TrackingScope' . ucfirst($scope)),
                'numSlotsAvailable' => count($indexes),
                'numSlotsUsed' => count($configs),
                'numSlotsLeft' => count($indexes) - count($configs),
                'supportsExtractions' => CustomDimensions::doesScopeSupportExtractions($scope),
            ];
        }

        return $scopes;
    }

    /**
     * Returns the dimensions that can be used in extraction rules.
     *
     * @return array<int, array{value:string, name:string}>
     */
    public function getAvailableExtractionDimensions(): array
    {
        Piwik::checkUserHasSomeWriteAccess();

        $supported = Extraction::getSupportedDimensions();

        $dimensions = [];
        foreach ($supported as $value => $dimension) {
            $dimensions[] = ['value' => $value, 'name' => $dimension];
        }

        return $dimensions;
    }

    private function getTracking(string $scope): LogTable
    {
        return new LogTable($scope);
    }

    private function getConfiguration(): Configuration
    {
        return new Configuration();
    }
}
