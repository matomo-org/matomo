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

class VisitsbyVisitNumber extends Dimension
{
    protected $nameSingular = 'VisitorInterest_visitsByVisitCount';
}