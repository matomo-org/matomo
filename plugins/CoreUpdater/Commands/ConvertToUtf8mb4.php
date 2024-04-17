<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\CoreUpdater\Commands;

use Piwik\Config;
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

        $queries = DbHelper::getUtf8mb4ConversionQueries();

        if ($show) {
            $this->showCommands($queries, $keepTracking);
            return self::SUCCESS;
        }

        $output->writeln("This command will convert all Matomo database tables to utf8mb4.\n");

        if (DbHelper::getDefaultCharset() !== 'utf8mb4') {
            $this->writeSuccessMessage(array('Your database does not support utf8mb4'));
            return self::FAILURE;
        }

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
            } finally {
                if (!$keepTracking) {
                    $output->writeln("\n" . Piwik::translate('Enabling Matomo Tracking'));
                    $config->Tracker['record_statistics'] = '1';
                }
                $config->forceSave();
            }

            $this->writeSuccessMessage(array('Conversion to utf8mb4 successful.'));
        } else {
            $this->writeSuccessMessage(array('Database conversion skipped.'));
        }

        return self::SUCCESS;
    }

    protected function showCommands($queries, $keepTracking)
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
        if (!$keepTracking) {
            $output->writeln('');
            $output->writeln('** Enable Matomo Tracking again with this command: **');
            $output->writeln('./console config:set --section=Tracker --key=record_statistics --value=1');
        }
    }
}
