<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Ecommerce\Columns;

use Piwik\Columns\Dimension;
use Piwik\Columns\Join\ActionNameJoin;

class ProductName extends Dimension
{
    protected $type = self::TYPE_JOIN_ID;
    protected $dbTableName = 'log_conversion_item';
    protected $columnName = 'idaction_name';
    protected $nameSingular = 'Goals_ProductName';
    protected $category = 'Goals_Ecommerce';

    public function getDbColumnJoin()
    {
        return new ActionNameJoin();
    }
}