<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Updates;

use Piwik\Db\Schema;
use Piwik\Updater;
use Piwik\Updater\Migration\Factory as MigrationFactory;
use Piwik\Updates;

class Updates_5_9_0_b2 extends Updates
{
    /**
     * @var MigrationFactory
     */
    private $migration;

    public function __construct(MigrationFactory $factory)
    {
        $this->migration = $factory;
    }

    public function getMigrations(Updater $updater): array
    {
        $migrations = [
            $this->migration->plugin->activate('BotTracking'),
        ];

        if ($this->shouldActivateAIAgents()) {
            // We only activate this plugin on newer MySQL/MariaDB versions,
            // to avoid adding a new column to log_visit, where this isn't a no-op
            $migrations[] = $this->migration->plugin->activate('AIAgents');
        }

        return $migrations;
    }

    public function doUpdate(Updater $updater): void
    {
        $updater->executeMigrations(__FILE__, $this->getMigrations($updater));
    }

    protected function getDatabaseType(): string
    {
        return Schema::getInstance()->getDatabaseType();
    }

    protected function getDatabaseVersion(): string
    {
        return Schema::getInstance()->getVersion();
    }

    private function shouldActivateAIAgents(): bool
    {
        $databaseType = $this->getDatabaseType();
        $version = $this->getDatabaseVersion();

        // fallback if database type isn't configured correctly
        if ($databaseType === 'MySQL' && strpos(strtolower($version), 'mariadb') !== false) {
            $databaseType = 'MariaDB';
        }

        $databaseVersion = $this->extractSemanticVersion($version);

        if (empty($databaseVersion)) {
            return false;
        }

        if ($databaseType === 'MySQL') {
            return version_compare($databaseVersion, '8.0.12', '>=');
        }

        if ($databaseType === 'MariaDB') {
            return version_compare($databaseVersion, '10.3.2', '>=');
        }

        return false;
    }

    private function extractSemanticVersion(string $databaseVersion): string
    {
        if (preg_match('/\d+(?:\.\d+)+/', $databaseVersion, $matches)) {
            return $matches[0];
        }

        return '';
    }
}
