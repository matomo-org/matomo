<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\UserId\Visualizations;

use Piwik\Plugins\CoreVisualizations\Visualizations\HtmlTable;
use Piwik\View;

/**
 * Custom report visualization to add data-visitor-url HTML attribute for each report row.
 * Visitor URL is used then to open a popover with detailed visitor information.
 */
class UserIds extends HtmlTable
{
    const ID = 'UserIds';
    const TEMPLATE_FILE = "@UserId/_dataTableViz_userIds.twig";
}
