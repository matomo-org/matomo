<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CustomDimensions;

use Piwik\API\Request;
use Piwik\Plugins\CustomDimensions\Dao\LogTable;
use Piwik\Plugins\CustomDimensions\Tracker\CustomDimensionsRequestProcessor;
use Piwik\Plugins\Live\VisitorDetailsAbstract;
use Piwik\View;

class VisitorDetails extends VisitorDetailsAbstract
{
    public function extendVisitorDetails(&$visitor)
    {
        if (empty($visitor['idSite'])) {
            return;
        }

        $idSite     = $visitor['idSite'];
        $dimensions = $this->getActiveCustomDimensionsInScope($idSite, CustomDimensions::SCOPE_VISIT);

        foreach ($dimensions as $dimension) {
            // field in DB, eg custom_dimension_1
            $field = LogTable::buildCustomDimensionColumnName($dimension);
            // field for user, eg dimension1
            $column = CustomDimensionsRequestProcessor::buildCustomDimensionTrackingApiName($dimension);
            if (array_key_exists($field, $this->details)) {
                $visitor[$column] = $this->details[$field];
            } else {
                $visitor[$column] = null;
            }
        }
    }

    public function extendActionDetails(&$action, $nextAction, $visitorDetails)
    {
        if (empty($visitorDetails['idSite'])) {
            return;
        }

        $idSite     = $visitorDetails['idSite'];
        $dimensions = $this->getActiveCustomDimensionsInScope($idSite, CustomDimensions::SCOPE_ACTION);

        foreach ($dimensions as $dimension) {
            // field in DB, eg custom_dimension_1
            $field = LogTable::buildCustomDimensionColumnName($dimension);
            // field for user, eg dimension1
            $column = CustomDimensionsRequestProcessor::buildCustomDimensionTrackingApiName($dimension);

            if (array_key_exists($field, $action)) {
                $action[$column] = $action[$field];
            } else {
                $action[$column] = null;
            }
            unset($action[$field]);
        }

        static $indices;

        if (is_null($indices)) {
            $logTable = new Dao\LogTable(CustomDimensions::SCOPE_ACTION);
            $indices  = $logTable->getInstalledIndexes();
        }

        foreach ($indices as $index) {
            $field = Dao\LogTable::buildCustomDimensionColumnName($index);
            unset($action[$field]);
        }
    }

    public function renderVisitorDetails($visitorDetails)
    {
        if (empty($visitorDetails['idSite'])) {
            return [];
        }

        $view                   = new View('@CustomDimensions/_visitorDetails');
        $view->sendHeadersWhenRendering = false;
        $view->visitInfo        = $visitorDetails;
        $view->customDimensions = $this->getCustomDimensionsFromVisit($visitorDetails);
        return [[ 40, $view->render() ]];
    }

    protected function getCustomDimensionsFromVisit($visitorDetails)
    {
        $idSite           = $visitorDetails['idSite'];
        $dimensions       = $this->getActiveCustomDimensionsInScope($idSite, CustomDimensions::SCOPE_VISIT);
        $customDimensions = array();

        if (count($dimensions) > 0) {
            foreach ($dimensions as $dimension) {
                $column             = CustomDimensionsRequestProcessor::buildCustomDimensionTrackingApiName($dimension);
                $customDimensions[] = array(
                    'id'    => $dimension['idcustomdimension'],
                    'name'  => $dimension['name'],
                    'value' => $visitorDetails[$column]
                );
            }
        }

        return $customDimensions;
    }

    public function renderActionTooltip($action, $visitInfo)
    {
        $customDimensions = $this->getCustomDimensionsFromAction($action, $visitInfo);

        if (empty($customDimensions)) {
            return [];
        }

        $action['customDimensions'] = $customDimensions;

        $view         = new View('@CustomDimensions/_actionTooltip');
        $view->sendHeadersWhenRendering = false;
        $view->action = $action;
        return [[ 30, $view->render() ]];
    }

    protected function getCustomDimensionsFromAction($action, $visitInfo)
    {
        $idSite           = $visitInfo['idSite'];
        $dimensions       = $this->getActiveCustomDimensionsInScope($idSite, CustomDimensions::SCOPE_ACTION);
        $customDimensions = array();

        foreach ($dimensions as $dimension) {
            $column                               = CustomDimensionsRequestProcessor::buildCustomDimensionTrackingApiName($dimension);
            $customDimensions[$dimension['name']] = $action[$column];
        }

        return $customDimensions;
    }

    protected $activeCustomDimensionsCache = array();

    protected function getActiveCustomDimensionsInScope($idSite, $scope)
    {
        if (array_key_exists($idSite . $scope, $this->activeCustomDimensionsCache)) {
            return $this->activeCustomDimensionsCache[$idSite . $scope];
        }

        $dimensions    = Request::processRequest('CustomDimensions.getConfiguredCustomDimensionsHavingScope', [
            'idSite' => $idSite,
            'scope' => $scope,
        ], $default = []);
        $dimensions    = array_filter($dimensions, function ($dimension) use ($scope) {
            return ($dimension['active'] && $dimension['scope'] === $scope);
        });

        $this->activeCustomDimensionsCache[$idSite . $scope] = $dimensions;
        return $this->activeCustomDimensionsCache[$idSite . $scope];
    }

    protected $customDimensions = [];
    protected $lastVisit = null;

    public function initProfile($visits, &$profile)
    {
        $this->customDimensions = [
            CustomDimensions::SCOPE_ACTION => [],
            CustomDimensions::SCOPE_VISIT  => [],
        ];
        $this->lastVisit = $visits->getLastRow();
    }

    public function handleProfileAction($action, &$profile)
    {
        $customDimensions = $this->getCustomDimensionsFromAction($action, $this->lastVisit);

        if (!empty($customDimensions)) {
            foreach ($customDimensions as $name => $value) {

                $scope = CustomDimensions::SCOPE_ACTION;

                if (empty($value)) {
                    continue;
                }

                if (!array_key_exists($name, $this->customDimensions[$scope])) {
                    $this->customDimensions[$scope][$name] = [
                    ];
                }

                if (!array_key_exists($value, $this->customDimensions[$scope][$name])) {
                    $this->customDimensions[$scope][$name][$value] = 0;
                }

                $this->customDimensions[$scope][$name][$value]++;
            }
        }
    }

    public function handleProfileVisit($visit, &$profile)
    {
        $customDimensions = $this->getCustomDimensionsFromVisit($visit);

        if (!empty($customDimensions)) {
            foreach ($customDimensions as $dimension) {

                $scope = CustomDimensions::SCOPE_VISIT;
                $name  = $dimension['name'];
                $value = $dimension['value'];

                if (empty($value)) {
                    continue;
                }

                if (!array_key_exists($name, $this->customDimensions[$scope])) {
                    $this->customDimensions[$scope][$name] = [
                    ];
                }

                if (!array_key_exists($value, $this->customDimensions[$scope][$name])) {
                    $this->customDimensions[$scope][$name][$value] = 0;
                }

                $this->customDimensions[$scope][$name][$value]++;
            }
        }
    }

    public function finalizeProfile($visits, &$profile)
    {
        $customDimensions = $this->customDimensions;
        foreach ($customDimensions as $scope => &$dimensions) {

            if (empty($dimensions)) {
                unset($customDimensions[$scope]);
                continue;
            }

            foreach ($dimensions AS $name => &$values) {
                arsort($values);
            }
        }
        if (!empty($customDimensions)) {

            $profile['customDimensions'] = $this->convertForProfile($customDimensions);
        }
    }

    protected function convertForProfile($customDimensions)
    {
        $convertedDimensions = [];

        foreach ($customDimensions as $scope => $scopeDimensions) {

            $convertedDimensions[$scope] = [];

            foreach ($scopeDimensions as $name => $values) {

                $dimension = [
                    'name' => $name,
                    'values' => []
                ];

                foreach ($values as $value => $count) {
                    $dimension['values'][] = [
                        'value' => $value,
                        'count' => $count
                    ];
                }

                $convertedDimensions[$scope][] = $dimension;
            }
        }

        return $convertedDimensions;
    }
}