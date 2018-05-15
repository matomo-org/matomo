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
use Piwik\Piwik;

class ProductCategory extends Dimension
{
    protected $type = self::TYPE_TEXT;
    protected $category = 'Goals_Ecommerce';
    protected $nameSingular = 'Goals_ProductCategory';
}