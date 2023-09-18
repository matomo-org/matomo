<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Tests\Framework\Mock;

use Piwik\Changes\Model as ChangesModel;

/**
 * Change model class
 *
 * Handle all data access operations for changes
 *
 */
class FakeChangesModel extends ChangesModel
{

    /**
     * Add a change item to the database table
     *
     * @param string $pluginName
     * @param array  $change
     */
    public function addChange(string $pluginName, array $change): void
    {
        // Do not load any changes
    }

}
