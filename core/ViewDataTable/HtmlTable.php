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
namespace Piwik\ViewDataTable;

use Piwik\Config;
use Piwik\DataTable\Renderer;
use Piwik\Piwik;
use Piwik\Common;
use Piwik\ViewDataTable;
use Piwik\View;
use Piwik\Visualization;

/**
 * Outputs an AJAX Table for a given DataTable.
 *
 * Reads the requested DataTable from the API.
 *
 * @package Piwik
 * @subpackage ViewDataTable
 */
class HtmlTable extends ViewDataTable
{
    public function __construct()
    {
        parent::__construct();

        $this->visualization = new Visualization\HtmlTable();
    }
}