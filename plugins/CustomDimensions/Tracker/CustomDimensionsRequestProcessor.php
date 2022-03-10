<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CustomDimensions\Tracker;

use Piwik\Common;
use Piwik\Plugins\CustomDimensions\CustomDimensions;
use Piwik\Plugins\CustomDimensions\Dao;
use Piwik\Plugins\CustomDimensions\Dimension\Extraction;
use Piwik\Tracker\Action;
use Piwik\Tracker\Cache;
use Piwik\Tracker\Model;
use Piwik\Tracker\Request;
use Piwik\Tracker\RequestProcessor;
use Piwik\Tracker\Visit\VisitProperties;

/**
 * Handles tracking of custom dimensions
 */
class CustomDimensionsRequestProcessor extends RequestProcessor
{

    public function recordLogs(VisitProperties $visitProperties, Request $request)
    {
        if (!self::hasActionCustomDimensionConfiguredInSite($request)) {
            return;
        }

        $model = new Model();

        /** @var Action $action */
        $action = $request->getMetadata('Actions', 'action');

        if (!empty($action) && $request->getMetadata('CoreHome', 'isNewVisit')) {
            // this logic is only for new visits because we have to create the visit to get an idvisit before we can insert
            // the action and only then we can update the last_idink_va of the previously on the visit from the previously created actions.
            // so flow is:
            // * Create visit -> creates idVisit A
            // * Create link visit action (requires idvisit) -> creates idlink_va B
            // * Update visit A and set the idlink_va B
            $idLinkVisit = $action->getIdLinkVisitAction();
            $idVisit     = $visitProperties->getProperty('idvisit');
            $model->updateVisit($request->getIdSite(), $idVisit, array('last_idlink_va' => $idLinkVisit));
        }

        $lastIdLinkVa = $visitProperties->getProperty('last_idlink_va');
        $previousIdLinkVa = $request->getMetadata('CustomDimensions', 'previous_idlink_va');
        if ($previousIdLinkVa) {
            // when last_idlink_va was already updated in this visit because it was existing visit... we need to get the idlink_va from previous tracking request
            // it's actually not needed to store previous_idlink_va in metadata currently but figured it be better just in case the logic changes in the future
            // to prevent updating the wrong idlink_va
            $lastIdLinkVa = $previousIdLinkVa;
        }
        $timeSpent    = $visitProperties->getProperty('time_spent_ref_action');

        if (!empty($lastIdLinkVa) && $timeSpent > 0) {
            // here we don't update the action that was created in this request but the action of the previous tracking request
            // we can only know how much time was spent on the previous action when the next action is recorded.
            $model->updateAction($lastIdLinkVa, array('time_spent' => $timeSpent));
        }
    }

    public static function hasActionCustomDimensionConfiguredInSite($request)
    {
        $dimensions = self::getCachedCustomDimensions($request);
        if (empty($dimensions)) {
            return false;
        }
        foreach ($dimensions as $dimension) {
            if ($dimension['scope'] == CustomDimensions::SCOPE_ACTION) {
                return true;
            }
        }
        return false;
    }

    public function onNewVisit(VisitProperties $visitProperties, Request $request)
    {
        $dimensionsToSet = $this->getCustomDimensionsInScope(CustomDimensions::SCOPE_VISIT, $request);

        foreach ($dimensionsToSet as $field => $value) {
            $visitProperties->setProperty($field, $value);
        }
    }

    public function onExistingVisit(&$valuesToUpdate, VisitProperties $visitProperties, Request $request)
    {
        $dimensionsToSet = $this->getCustomDimensionsInScope(CustomDimensions::SCOPE_VISIT, $request);

        foreach ($dimensionsToSet as $field => $value) {
            $valuesToUpdate[$field] = $value;
            $visitProperties->setProperty($field, $value);
        }

        $action = $request->getMetadata('Actions', 'action');
        /** @var Action $action */
        if (!empty($action) && $action->getIdLinkVisitAction() && self::hasActionCustomDimensionConfiguredInSite($request)) {
            // when it is an existing visit, then we first create the action before recording the visit. This allows us
            // to update last_idlink_va in the regular visit update
            $request->setMetadata('CustomDimensions','previous_idlink_va', $visitProperties->getProperty('last_idlink_va'));
            $valuesToUpdate['last_idlink_va'] = $action->getIdLinkVisitAction();
        }
    }

    public function afterRequestProcessed(VisitProperties $visitProperties, Request $request)
    {
        $action = $request->getMetadata('Actions', 'action');

        if (empty($action) || !($action instanceof Action)) {
            return;
        }

        $dimensionsToSet = $this->getCustomDimensionsInScope(CustomDimensions::SCOPE_ACTION, $request);

        foreach ($dimensionsToSet as $field => $value) {
            $action->setCustomField($field, $value);
        }
    }

    private function getCustomDimensionsInScope($scope, Request $request)
    {
        $dimensions = self::getCachedCustomDimensions($request);
        $params = $request->getParams();

        $values = array();

        foreach ($dimensions as $dimension) {
            if ($dimension['scope'] !== $scope) {
                continue;
            }

            $field = self::buildCustomDimensionTrackingApiName($dimension);
            $dbField = Dao\LogTable::buildCustomDimensionColumnName($dimension);

            $value = Common::getRequestVar($field, '', 'string', $params);
            $hasSentEmptyString = isset($params[$field]) && $params[$field] === '';
            if ($value !== '' || $hasSentEmptyString) {
                $values[$dbField] = self::prepareValue($value);
                continue;
            }

            $extractions = $dimension['extractions'];
            if (is_array($extractions)) {
                foreach ($extractions as $extraction) {
                    if (!array_key_exists('dimension', $extraction)
                     || !array_key_exists('pattern', $extraction)
                     || empty($extraction['pattern'])) {
                        continue;
                    }

                    $extraction = new Extraction($extraction['dimension'], $extraction['pattern']);
                    $extraction->setCaseSensitive($dimension['case_sensitive']);
                    $value = $extraction->extract($request);

                    if (!isset($value) || '' === $value) {
                        continue;
                    }

                    $values[$dbField] = self::prepareValue(urldecode($value));
                    break;
                }
            }
        }

        return $values;
    }

    private static function prepareValue($value)
    {
        return mb_substr(trim($value), 0, 250);
    }

    public static function buildCustomDimensionTrackingApiName($idDimensionOrDimension)
    {
        if (is_array($idDimensionOrDimension) && isset($idDimensionOrDimension['idcustomdimension'])) {
            $idDimensionOrDimension = $idDimensionOrDimension['idcustomdimension'];
        }

        $idDimensionOrDimension = (int) $idDimensionOrDimension;

        if ($idDimensionOrDimension >= 1) {
            return 'dimension' . (int) $idDimensionOrDimension;
        }
    }

    public static function getCachedCustomDimensionIndexes($scope)
    {
        $cache = Cache::getCacheGeneral();
        $key = 'custom_dimension_indexes_installed_' . $scope;

        if (empty($cache[$key])) {
            return array();
        }

        return $cache[$key];
    }

    /**
     * Get Cached Custom Dimensions during tracking. Returns only active custom dimensions.
     *
     * @param Request $request
     * @return array
     * @throws \Piwik\Exception\UnexpectedWebsiteFoundException
     */
    public static function getCachedCustomDimensions(Request $request)
    {
        $idSite = $request->getIdSite();
        $cache  = Cache::getCacheWebsiteAttributes($idSite);

        if (empty($cache['custom_dimensions'])) {
            // no custom dimensions set
            return array();
        }

        return $cache['custom_dimensions'];
    }

}
