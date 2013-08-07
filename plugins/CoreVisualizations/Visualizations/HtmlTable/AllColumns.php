<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package Piwik
 */

namespace Piwik\Plugins\CoreVisualizations\Visualizations\HtmlTable;

use Piwik\Plugins\CoreVisualizations\Visualizations\HtmlTable;

/**
 * TODO
 */
class AllColumns extends HtmlTable
{
    const ID = 'tableAllColumns';

    /**
     * Constructor.
     * 
     * @param ViewDataTable $view
     */
    public function __construct($view)
    {
        $view->visualization_properties->show_extra_columns = true;

        parent::__construct($view);
    }
}