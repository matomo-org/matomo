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
    public function addChanges(string $pluginName): void
    {
    }

    public function addChange(string $pluginName, array $change): void
    {
    }

    public function removeChanges(string $pluginName): void
    {
    }

    public function getChangeItems(): array
    {
        return [];
    }
}
