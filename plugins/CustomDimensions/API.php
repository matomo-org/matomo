<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
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
     * Fetch a report for the given idDimension. Only reports for active dimensions can be fetched. Requires at least
     * view access.
     *
     * @param int $idDimension
     * @param int $idSite
     * @param string $period
     * @param string $date
     * @param bool|false $segment
     * @param bool|false $expanded
     * @param bool|false $flat
     * @param int|false $idSubtable
     * @return DataTable|DataTable\Map
     * @throws \Exception
     */
    public function getCustomDimension($idDimension, $idSite, $period, $date, $segment = false, $expanded = false, $flat = false, $idSubtable = false)
    {
        Piwik::checkUserHasViewAccess($idSite);

        $dimension = new Dimension($idDimension, $idSite);
        $dimension->checkActive();

        $record = Archiver::buildRecordNameForCustomDimensionId($idDimension);

        $dataTable = Archive::createDataTableFromArchive($record, $idSite, $period, $date, $segment, $expanded, $flat, $idSubtable);

        if (!empty($idSubtable) && $dataTable->getRowsCount()) {
            $parentTable = Archive::createDataTableFromArchive($record, $idSite, $period, $date, $segment);
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
     * Configures a new Custom Dimension. Note that Custom Dimensions cannot be deleted, be careful when creating one
     * as you might run quickly out of available Custom Dimension slots. Requires at least Admin access for the
     * specified website. A current list of available `$scopes` can be fetched via the API method
     * `CustomDimensions.getAvailableScopes()`. This method will also contain information whether actually Custom
     * Dimension slots are available or whether they are all already in use.
     *
     * @param int $idSite    The idSite the dimension shall belong to
     * @param string $name   The name of the dimension
     * @param string $scope  Either 'visit' or 'action'. To get an up to date list of availabe scopes fetch the
     *                       API method `CustomDimensions.getAvailableScopes`
     * @param int $active  '0' if dimension should be inactive, '1' if dimension should be active
     * @param array $extractions    Either an empty array or if extractions shall be used one or multiple extractions
     *                              the format array(array('dimension' => 'url', 'pattern' => 'index_(.+).html'), array('dimension' => 'urlparam', 'pattern' => '...'))
     *                              Supported dimensions are  eg 'url', 'urlparam' and 'action_name'. To get an up to date list of
     *                              supported dimensions request the API method `CustomDimensions.getAvailableExtractionDimensions`.
     *                              Note: Extractions can be only set for dimensions in scope 'action'.
     * @param int|bool $caseSensitive  '0' if extractions should be applied case insensitive, '1' if extractions should be applied case sensitive
     * @return int Returns the ID of the configured dimension. Note that the same idDimension will be used for different websites.
     * @throws \Exception
     */
    public function configureNewCustomDimension($idSite, $name, $scope, $active, $extractions = array(), $caseSensitive = true)
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
     * Updates an existing Custom Dimension. This method updates all values, you need to pass existing values of the
     * dimension if you do not want to reset any value. Requires at least Admin access for the specified website.
     *
     * @param int $idDimension  The id of a Custom Dimension.
     * @param int $idSite       The idSite the dimension belongs to
     * @param string $name      The name of the dimension
     * @param int $active       '0' if dimension should be inactive, '1' if dimension should be active
     * @param array $extractions    Either an empty array or if extractions shall be used one or multiple extractions
     *                              the format array(array('dimension' => 'url', 'pattern' => 'index_(.+).html'), array('dimension' => 'urlparam', 'pattern' => '...'))
     *                              Supported dimensions are  eg 'url', 'urlparam' and 'action_name'. To get an up to date list of
     *                              supported dimensions request the API method `CustomDimensions.getAvailableExtractionDimensions`.
     *                              Note: Extractions can be only set for dimensions in scope 'action'.
     * @param int|bool|null $caseSensitive  '0' if extractions should be applied case insensitive, '1' if extractions should be applied case sensitive, null to keep case sensitive unchanged
     * @return int Returns the ID of the configured dimension. Note that the same idDimension will be used for different websites.
     * @throws \Exception
     */
    public function configureExistingCustomDimension($idDimension, $idSite, $name, $active, $extractions = array(), $caseSensitive = null)
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

    private function checkExtractionsAreSupportedForScope($scope, $extractions)
    {
        if (!CustomDimensions::doesScopeSupportExtractions($scope) && !empty($extractions)) {
            throw new \Exception("Extractions can be used only in scope 'action'");
        }
    }

    /**
     * Get a list of all configured CustomDimensions for a given website. Requires at least Admin access for the
     * specified website.
     *
     * @param int $idSite
     * @return array
     */
    public function getConfiguredCustomDimensions($idSite)
    {
        Piwik::checkUserHasViewAccess($idSite);

        $configs = $this->getConfiguration()->getCustomDimensionsForSite($idSite);

        return $configs;
    }

    /**
     * For convenience. Hidden to reduce API surface area.
     * @hide
     */
    public function getConfiguredCustomDimensionsHavingScope($idSite, $scope)
    {
        $result = $this->getConfiguredCustomDimensions($idSite);
        $result = array_filter($result, function ($row) use ($scope) { return $row['scope'] == $scope; });
        $result = array_values($result);
        return $result;
    }

    private function checkCustomDimensionConfig($name, $active, $extractions, $caseSensitive)
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
     * Get a list of all supported scopes that can be used in the API method
     * `CustomDimensions.configureNewCustomDimension`. The response also contains information whether more Custom
     * Dimensions can be created or not. Requires at least Admin access for the specified website.
     *
     * @param int $idSite
     * @return array
     */
    public function getAvailableScopes($idSite)
    {
        Piwik::checkUserHasViewAccess($idSite);

        $scopes = array();
        foreach (CustomDimensions::getPublicScopes() as $scope) {

            $configs = $this->getConfiguredCustomDimensionsHavingScope($idSite, $scope);
            $indexes = $this->getTracking($scope)->getInstalledIndexes();

            $scopes[] = array(
                'value' => $scope,
                'name' => Piwik::translate('General_TrackingScope' . ucfirst($scope)),
                'numSlotsAvailable' => count($indexes),
                'numSlotsUsed' => count($configs),
                'numSlotsLeft' => count($indexes) - count($configs),
                'supportsExtractions' => CustomDimensions::doesScopeSupportExtractions($scope)
            );
        }

        return $scopes;
    }

    /**
     * Get a list of all available dimensions that can be used in an extraction. Requires at least Admin access
     * to one website.
     *
     * @return array
     */
    public function getAvailableExtractionDimensions()
    {
        Piwik::checkUserHasSomeWriteAccess();

        $supported = Extraction::getSupportedDimensions();

        $dimensions = array();
        foreach ($supported as $value => $dimension) {
            $dimensions[] = array('value' => $value, 'name' => $dimension);
        }

        return $dimensions;
    }

    private function getTracking($scope)
    {
        return new LogTable($scope);
    }

    private function getConfiguration()
    {
        return new Configuration();
    }

}

