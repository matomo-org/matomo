<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Metrics\Sorter;

class Config
{
    public $naturalSort = false;

    public $primaryColumnToSort;
    public $primarySortFlags;
    public $primarySortOrder;

    public $secondaryColumnToSort;
    public $secondarySortOrder;
    public $secondarySortFlags;

    public $isSecondaryColumnSortEnabled = true;

}