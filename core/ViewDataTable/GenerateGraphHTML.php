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

use Exception;
use Piwik\API\Request;
use Piwik\Piwik;
use Piwik\Access;
use Piwik\NoAccessException;
use Piwik\Common;
use Piwik\JqplotDataGenerator;
use Piwik\ViewDataTable;
use Piwik\View;
use Piwik\Visualization\JqplotGraph;

/**
 * This class generates the HTML code to embed graphs in the page.
 * It doesn't call the API but simply prints the html snippet.
 *
 * @package Piwik
 * @subpackage ViewDataTable
 */
abstract class GenerateGraphHTML extends ViewDataTable
{
    public function __construct()
    {
        parent::__construct('\\Piwik\\Visualization\\JqplotGraph');
    }
    
    public function getDefaultDataTableCssClass()
    {
        return 'dataTableGraph';
    }
}
