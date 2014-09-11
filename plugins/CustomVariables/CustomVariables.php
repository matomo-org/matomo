<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CustomVariables;

use Piwik\ArchiveProcessor;
use Piwik\Piwik;
use Piwik\Tracker\Cache;
use Piwik\Tracker;

class CustomVariables extends \Piwik\Plugin
{
    public function getInformation()
    {
        $info = parent::getInformation();
        $info['description'] .= ' <br/>Required to use <a href="http://piwik.org/docs/ecommerce-analytics/">Ecommerce Analytics</a> feature!';
        return $info;
    }

    /**
     * @see Piwik\Plugin::getListHooksRegistered
     */
    public function getListHooksRegistered()
    {
        return array(
            'API.getSegmentDimensionMetadata' => 'getSegmentsMetadata',
            'Live.getAllVisitorDetails'       => 'extendVisitorDetails'
        );
    }

    public function install()
    {
        Model::install();
    }

    public function uninstall()
    {
        Model::uninstall();
    }

    public function extendVisitorDetails(&$visitor, $details)
    {
        $customVariables = array();

        $maxCustomVariables = self::getMaxCustomVariables();

        for ($i = 1; $i <= $maxCustomVariables; $i++) {
            if (!empty($details['custom_var_k' . $i])) {
                $customVariables[$i] = array(
                    'customVariableName' .  $i => $details['custom_var_k' . $i],
                    'customVariableValue' . $i => $details['custom_var_v' . $i],
                );
            }
        }

        $visitor['customVariables'] = $customVariables;
    }

    /**
     * There are also some hardcoded places in JavaScript
     * @return int
     */
    public static function getMaxLengthCustomVariables()
    {
        return 200;
    }

    public static function getMaxCustomVariables()
    {
        $cache    = Cache::getCacheGeneral();
        $cacheKey = 'CustomVariables.MaxNumCustomVariables';

        if (!array_key_exists($cacheKey, $cache)) {

            $maxCustomVar = 0;

            foreach (Model::getScopes() as $scope) {
                $model = new Model($scope);
                $highestIndex = $model->getHighestCustomVarIndex();

                if ($highestIndex > $maxCustomVar) {
                    $maxCustomVar = $highestIndex;
                }
            }

            $cache[$cacheKey] = $maxCustomVar;
            Cache::setCacheGeneral($cache);
        }

        return $cache[$cacheKey];
    }

    public function getSegmentsMetadata(&$segments)
    {
        $maxCustomVariables = self::getMaxCustomVariables();

        for ($i = 1; $i <= $maxCustomVariables; $i++) {
            $segments[] = array(
                'type'       => 'dimension',
                'category'   => 'CustomVariables_CustomVariables',
                'name'       => Piwik::translate('CustomVariables_ColumnCustomVariableName') . ' ' . $i
                    . ' (' . Piwik::translate('CustomVariables_ScopeVisit') . ')',
                'segment'    => 'customVariableName' . $i,
                'sqlSegment' => 'log_visit.custom_var_k' . $i,
            );
            $segments[] = array(
                'type'       => 'dimension',
                'category'   => 'CustomVariables_CustomVariables',
                'name'       => Piwik::translate('CustomVariables_ColumnCustomVariableValue') . ' ' . $i
                    . ' (' . Piwik::translate('CustomVariables_ScopeVisit') . ')',
                'segment'    => 'customVariableValue' . $i,
                'sqlSegment' => 'log_visit.custom_var_v' . $i,
            );
            $segments[] = array(
                'type'       => 'dimension',
                'category'   => 'CustomVariables_CustomVariables',
                'name'       => Piwik::translate('CustomVariables_ColumnCustomVariableName') . ' ' . $i
                    . ' (' . Piwik::translate('CustomVariables_ScopePage') . ')',
                'segment'    => 'customVariablePageName' . $i,
                'sqlSegment' => 'log_link_visit_action.custom_var_k' . $i,
            );
            $segments[] = array(
                'type'       => 'dimension',
                'category'   => 'CustomVariables_CustomVariables',
                'name'       => Piwik::translate('CustomVariables_ColumnCustomVariableValue') . ' ' . $i
                    . ' (' . Piwik::translate('CustomVariables_ScopePage') . ')',
                'segment'    => 'customVariablePageValue' . $i,
                'sqlSegment' => 'log_link_visit_action.custom_var_v' . $i,
            );
        }
    }

}
