<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\VisitorInterest\Columns;

use Piwik\Columns\Dimension;

class VisitDuration extends Dimension
{

    protected $type = self::TYPE_DURATION_S;
    protected $nameSingular = 'VisitorInterest_ColumnVisitDuration';
}