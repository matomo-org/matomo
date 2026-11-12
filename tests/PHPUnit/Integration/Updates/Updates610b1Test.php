<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Tests\Integration\Updates;

use Piwik\Common;
use Piwik\Container\StaticContainer;
use Piwik\Db;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Updater;
use Piwik\Updater\Migration\Factory as MigrationFactory;
use Piwik\Updates\Updates_6_1_0_b1;

require_once __DIR__ . '/../../../../core/Updates/6.1.0-b1.php';

/**
 * @group Updates
 */
class Updates610b1Test extends IntegrationTestCase
{
    public function testCreatesTheTransitionsPreprocessedPageTableAsAFreshInstallDoes(): void
    {
        $installedSchema = $this->getCreateTableStatement();

        Db::query('DROP TABLE ' . Common::prefixTable('transitions_preprocessed_page'));

        $this->runUpdate();
        $this->runUpdate();

        self::assertSame($installedSchema, $this->getCreateTableStatement());
    }

    private function getCreateTableStatement(): string
    {
        $row = Db::fetchRow('SHOW CREATE TABLE ' . Common::prefixTable('transitions_preprocessed_page'));

        return $row['Create Table'];
    }

    private function runUpdate(): void
    {
        $update = new Updates_6_1_0_b1(StaticContainer::get(MigrationFactory::class));
        $update->doUpdate(new Updater());
    }
}
