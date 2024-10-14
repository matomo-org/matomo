<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreUpdater\Commands;

use Piwik\Common;
use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\DataAccess\ArchiveTableCreator;
use Piwik\Db;
use Piwik\DbHelper;
use Piwik\Piwik;
use Piwik\Plugin\ConsoleCommand;

/**
 * @package CoreUpdater
 */
class ConvertToUtf8mb4 extends ConsoleCommand
{
    protected function configure()
    {
        $this->setName('core:convert-to-utf8mb4');

        $this->setDescription('Converts the database to utf8mb4');

        $this->addNoValueOption('show', null, Piwik::translate('Show all commands / queries only.'));
        $this->addNoValueOption('yes', null, Piwik::translate('CoreUpdater_ConsoleParameterDescription'));
        $this->addNoValueOption('keep-tracking', null, 'Do not disable tracking while conversion is running');
    }

    public function isEnabled()
    {
        $dbSettings   = new Db\Settings();
        $charset      = $dbSettings->getUsedCharset();

        return $charset !== 'utf8mb4';
    }

    /**
     * Execute command like: ./console core:convert-to-utf8mb4 --yes
     */
    protected function doExecute(): int
    {
        $input = $this->getInput();
        $output = $this->getOutput();
        $yes = $input->getOption('yes');
        $keepTracking = $input->getOption('keep-tracking');
        $show = $input->getOption('show');

        if (DbHelper::getDefaultCharset() !== 'utf8mb4') {
            $this->writeErrorMessage('Your database does not support utf8mb4');
            return self::FAILURE;
        }

        $defaultCollation = DbHelper::getDefaultCollationForCharset('utf8mb4');
        if (empty($defaultCollation)) {
            $this->writeErrorMessage('Could not detect default collation for utf8mb4 charset');
            return self::FAILURE;
        }

        $queries = DbHelper::getUtf8mb4ConversionQueries();
        if ($show) {
            $this->showCommands($queries, $keepTracking, $defaultCollation);
            return self::SUCCESS;
        }

        $output->writeln("This command will convert all Matomo database tables to utf8mb4.\n");

        if (!$keepTracking) {
            $output->writeln("Tracking will be disabled during this process.\n");
        }

        $output->writeln('If you want to see what this command is going to do use the --show option.');

        if (!$yes) {
            $yes = $this->askForConfirmation('<comment>Execute updates? (y/N) </comment>', false);
        }

        if ($yes) {
            $config = Config::getInstance();

            if (!$keepTracking) {
                $output->writeln("\n" . Piwik::translate('Disabling Matomo Tracking'));
                $config->Tracker['record_statistics'] = '0';
                $config->forceSave();
            }

            $output->writeln("\n" . Piwik::translate('CoreUpdater_ConsoleStartingDbUpgrade'));

            try {
                foreach ($queries as $query) {
                    $output->write("\n" . 'Executing ' . $query . '... ');
                    Db::get()->exec($query);
                    $output->write(' done.');
                }

                $output->writeln("\n" . 'Updating used database charset in config.ini.php.');
                $config->database['charset'] = 'utf8mb4';

                $collation = $this->detectCollation() ?: $defaultCollation;

                $output->writeln("\n" . 'Updating used database collation in config.ini.php.');
                $config->database['collation'] = $collation;
            } finally {
                if (!$keepTracking) {
                    $output->writeln("\n" . Piwik::translate('Enabling Matomo Tracking'));
                    $config->Tracker['record_statistics'] = '1';
                }
                $config->forceSave();
            }

            $this->writeSuccessMessage('Conversion to utf8mb4 successful.');
        } else {
            $this->writeSuccessMessage('Database conversion skipped.');
        }

        return self::SUCCESS;
    }

    protected function showCommands($queries, $keepTracking, $collation)
    {
        $output = $this->getOutput();
        $output->writeln("To manually convert all Matomo database tables to utf8mb4 follow these steps.");
        if (!$keepTracking) {
            $output->writeln('');
            $output->writeln('** Disable Matomo Tracking with this command: **');
            $output->writeln('./console config:set --section=Tracker --key=record_statistics --value=0');
        }
        $output->writeln('');
        $output->writeln('** Execute the following database queries: **');
        $output->writeln(implode("\n", $queries));
        $output->writeln('');
        $output->writeln('** Change configured database charset to utf8mb4 with this command: **');
        $output->writeln('./console config:set --section=database --key=charset --value=utf8mb4');
        $output->writeln('');
        $output->writeln("** Change configured database collation to {$collation} with this command: **");
        $output->writeln("(the actual collation value may differ based on your specific database settings)");
        $output->writeln("./console config:set --section=database --key=collation --value={$collation}");
        if (!$keepTracking) {
            $output->writeln('');
            $output->writeln('** Enable Matomo Tracking again with this command: **');
            $output->writeln('./console config:set --section=Tracker --key=record_statistics --value=1');
        }
    }

    private function detectCollation(): ?string
    {
        try {
            $metadataProvider = StaticContainer::get('Piwik\Plugins\DBStats\MySQLMetadataProvider');
            $userTableStatus = $metadataProvider->getTableStatus('user');
            if (empty($userTableStatus['Collation'] ?? null)) {
                // if there is no user table, or no collation for it, abort detection
                // this table should always exist and something must be wrong in this case
                return null;
            }
            $userTableCollation = $userTableStatus['Collation'];

            $archiveTable = ArchiveTableCreator::getLatestArchiveTableInstalled(ArchiveTableCreator::NUMERIC_TABLE);
            if (null === $archiveTable) {
                return null;
            }

            $archiveTableStatus = $metadataProvider->getTableStatus(Common::unprefixTable($archiveTable));

            if (
                !empty($archiveTableStatus['Collation'])
                && $archiveTableStatus['Collation'] === $userTableCollation
            ) {
                // the most recent numeric archive table is matching the collation
                // of the users table, should be a good value to choose
                return $userTableCollation;
            }
        } catch (\Exception $e) {
            // no-op if there are any issues, will default to the default collation
        }

        return null;
    }
}
