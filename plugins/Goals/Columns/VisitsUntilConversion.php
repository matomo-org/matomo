<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Goals\Columns;

use Piwik\Columns\Dimension;

class VisitsUntilConversion extends Dimension
{
    protected $type = self::TYPE_NUMBER;
    protected $nameSingular = 'Goals_VisitsUntilConv';

}