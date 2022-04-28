<?php
/**
 * Copyright (C) InnoCraft Ltd - All rights reserved.
 *
 * NOTICE:  All information contained herein is, and remains the property of InnoCraft Ltd.
 * The intellectual and technical concepts contained herein are protected by trade secret or copyright law.
 * Redistribution of this information or reproduction of this material is strictly forbidden
 * unless prior written permission is obtained from InnoCraft Ltd.
 *
 * You shall use this code only in accordance with the license agreement obtained from InnoCraft Ltd.
 *
 * @link https://www.innocraft.com/
 * @license For license details see https://www.innocraft.com/license
 */

namespace Piwik\Plugins\MediaAnalytics\Visualizations;

use Piwik\DataTable;
use Piwik\Piwik;
use Piwik\Plugin\ViewDataTable;
use Piwik\Plugins\CoreVisualizations\Visualizations\JqplotGraph;
use Piwik\Plugins\MediaAnalytics\Archiver;
use Piwik\Plugins\MediaAnalytics\Columns\Metrics\SegmentPlayRate;

class MediaEvolution extends JqplotGraph\Evolution
{
    const ID = 'mediaEvolution';
    const SERIES_COLOR_COUNT = 8;
    const FOOTER_ICON_TITLE = '';
    const FOOTER_ICON = '';

    public function beforeLoadDataTable()
    {
        JqplotGraph::beforeLoadDataTable();
        $this->config->datatable_css_class = 'dataTableVizEvolution';
    }
    /**
     * Load the datatable from the API using the pre-configured request object
     *
     * @param array $forcedParams   Optional parameters which will be used to overwrite the request parameters
     *
     * @return mixed
     */
    protected function loadDataTableFromAPI(array $forcedParams = [])
    {
        $result = parent::loadDataTableFromAPI($forcedParams);

        $this->dataTable->setMetadata(DataTable::EXTRA_PROCESSED_METRICS_METADATA_NAME, array(new SegmentPlayRate()));

        return $result;
    }

    public function beforeRender()
    {
        parent::beforeRender();
        $this->config->datatable_css_class = 'dataTableVizEvolution';
        $this->config->custom_parameters['viewDataTable'] = 'graphEvolution';
    }


    protected function checkRequestIsOnlyForMultiplePeriods()
    {
    }
    public function afterAllFiltersAreApplied()
    {
        parent::afterAllFiltersAreApplied();

        if (false === $this->config->x_axis_step_size) {
            $rowCount = $this->dataTable->getRowsCount();

            $this->config->x_axis_step_size = $this->getDefaultXAxisStepSize($rowCount);
        }
    }

    public function getDefaultXAxisStepSize($countGraphElements)
    {
        // when the number of elements plotted can be small, make sure the X legend is useful
        if ($countGraphElements <= 7) {
            return 1;
        }

        $steps = 5;

        $paddedCount = $countGraphElements + 2; // pad count so last label won't be cut off

        return ceil($paddedCount / $steps);
    }

    public static function canDisplayViewDataTable(ViewDataTable $view)
    {
        return Piwik::getModule() === 'MediaAnalytics' && Piwik::getAction() === 'detail'
            && !empty($view->requestConfig->request_parameters_to_modify['secondaryDimension'])
            && $view->requestConfig->request_parameters_to_modify['secondaryDimension'] == Archiver::SECONDARY_DIMENSION_MEDIA_SEGMENTS;
    }

}
