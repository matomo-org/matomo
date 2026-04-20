<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreUpdater\Commands;

use Piwik\Config;
use Piwik\DataAccess\ArchiveBlobColumnType;
use Piwik\Plugin\ConsoleCommand;

/**
 * CLI command that rechecks whether any archive_blob_* tables still use MEDIUMBLOB for their
 * `value` column and clears the `[database] archive_blob_tables_may_contain_mediumblob` config
 * flag once all tables have been migrated to LONGBLOB.
 *
 * @package CoreUpdater
 */
class RecheckArchiveBlobTypes extends ConsoleCommand
{
    protected function configure(): void
    {
        $this->setName('core:recheck-archive-blob-types');

        $this->setDescription(
            'Checks whether archive_blob tables still use a legacy MEDIUMBLOB column. ' .
            'When all tables have been migrated to LONGBLOB, the ' .
            '[database] archive_blob_tables_may_contain_mediumblob config flag is removed so ' .
            'the row-limit cap is no longer applied.'
        );
    }

    protected function doExecute(): int
    {
        $output = $this->getOutput();

        // Short-circuit when the flag is not set (fresh installs, or already cleared).
        $flag = (int) (Config::getInstance()->database[ArchiveBlobColumnType::CONFIG_KEY] ?? 0);
        if ($flag === 0) {
            $output->writeln(
                'No MEDIUMBLOB archive_blob tables possible (flag not set); nothing to do.'
            );
            return self::SUCCESS;
        }

        // Delegate all detection and flag-clearing logic to the single canonical implementation.
        $mediumBlobTables = ArchiveBlobColumnType::recheckAndUpdateFlag();

        if (empty($mediumBlobTables)) {
            $output->writeln(
                'No MEDIUMBLOB archive_blob tables found. ' .
                'The ' . ArchiveBlobColumnType::CONFIG_KEY . ' flag has been removed from config.ini.php. ' .
                'Archive row-limit cap will no longer be applied.'
            );
        } else {
            $output->writeln(sprintf(
                'Found %d archive_blob table(s) still using MEDIUMBLOB:',
                count($mediumBlobTables)
            ));
            foreach ($mediumBlobTables as $table) {
                $output->writeln('  - ' . $table);
            }
            $output->writeln(
                'The ' . ArchiveBlobColumnType::CONFIG_KEY . ' flag remains set. ' .
                'To migrate, run ALTER TABLE on the listed tables to change the `value` column to LONGBLOB, ' .
                'then re-run this command.'
            );
        }

        return self::SUCCESS;
    }
}
