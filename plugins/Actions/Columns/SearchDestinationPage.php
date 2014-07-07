<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Actions\Columns;

use Piwik\Columns\Dimension;
use Piwik\Piwik;

class SearchDestinationPage extends Dimension
{
    public function getName()
    {
        return Piwik::translate('General_ColumnDestinationPage');
    }
}
