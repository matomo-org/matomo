<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\CoreVisualizations\Visualizations\HtmlTable;

use Piwik\DataTable;
use Piwik\Plugins\CoreVisualizations\Visualizations\HtmlTable;
use Piwik\View;

/**
 * DataTable Visualization that derives from HtmlTable and sets show_extra_columns to true.
 */
class PivotBy extends HtmlTable
{
    public function beforeGenericFiltersAreAppliedToLoadedDataTable()
    {
        $this->config->columns_to_display = $this->dataTable->getColumns();

        $this->dataTable->applyQueuedFilters();

        parent::beforeGenericFiltersAreAppliedToLoadedDataTable();
    }

    public function beforeRender()
    {
        parent::beforeRender();

        $this->config->columns_to_display = $this->dataTable->getColumns();
    }
}
