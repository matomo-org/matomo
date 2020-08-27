<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Goals\Columns;

use Piwik\Plugin\Dimension\ConversionDimension;

class Revenue extends ConversionDimension
{
    protected $columnName = 'revenue';
    protected $type = self::TYPE_MONEY;
    protected $category = 'Goals_Goals';
    protected $nameSingular = 'Goals_ColumnOverallRevenue';

}