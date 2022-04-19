<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\CustomDimensions\Dao;

use Piwik\API\Request;
use Piwik\DataTable;
use Piwik\Date;
use Piwik\Plugins\CustomDimensions\Archiver;

class AutoSuggest
{

    /**
     * @param array $dimension
     * @param int $idSite
     * @param int $maxValuesToReturn
     * @return array
     */
    public function getMostUsedActionDimensionValues($dimension, $idSite, $maxValuesToReturn)
    {
        // we use first day from the month so it only needs to aggregate archives of two/three months and no weeks etc
        $date = Date::now()->subMonth(2)->setDay(1)->toString() . ',today';
        /** @var DataTable $report */
        $report = Request::processRequest('CustomDimensions.getCustomDimension', array(
            'idDimension' => $dimension['idcustomdimension'],
            'idSite' => $idSite,
            'filter_offset' => 0,
            'filter_limit' => $maxValuesToReturn,
            'period' => 'range',
            'date' => $date,
            'disable_queued_filters' => 1
        ), array());

        $labels = $report->getColumn('label');
        $notDefinedKey = array_search(Archiver::LABEL_CUSTOM_VALUE_NOT_DEFINED, $labels);
        if ($notDefinedKey !== false) {
            unset($labels[$notDefinedKey]);
            $labels = array_values($labels);
        }

        return $labels;
    }

}