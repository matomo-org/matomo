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

namespace Piwik\Visualization\HtmlTable;

use Piwik\Visualization\HtmlTable;

/**
 * TODO
 */
class Goals extends HtmlTable
{
    const ID = 'tableGoals';

    /**
     * TODO
     */
    public function __construct($view)
    {
        $view->visualization_properties->show_goals_columns = true;

        parent::__construct($view);
    }
}