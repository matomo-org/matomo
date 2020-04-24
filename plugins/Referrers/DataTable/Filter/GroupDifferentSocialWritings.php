<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Referrers\DataTable\Filter;

use Piwik\DataTable;

class GroupDifferentSocialWritings extends DataTable\BaseFilter
{
    public function filter($table)
    {
        if ($table->getRowFromLabel('instagram')) {
            $table->filter('GroupBy', ['label', function ($value) {
                if ($value === 'instagram') {
                    return 'Instagram';
                }
                return $value;
            }]);
        }
    }
}