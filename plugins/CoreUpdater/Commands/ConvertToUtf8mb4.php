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
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * @package CoreUpdater
 */
class ConvertToUtf8mb4 extends ConsoleCommand
{
    protected function configure()
    {
        $this->setName('core:convert-to-utf8mb4');

        $this->setDescription('Converts the database to utf8mb4');

        $this->addOption('show', null, InputOption::VALUE_NONE, Piwik::translate('Show all commands / queries only.'));
        $this->addOption('yes', null, InputOption::VALUE_NONE, Piwik::translate('CoreUpdater_ConsoleParameterDescription'));
        $this->addOption('keep-tracking', null, InputOption::VALUE_NONE, 'Do not disable tracking while conversion is running');
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
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $yes = $input->getOption('yes');
        $keepTracking = $input->getOption('keep-tracking');
        $show = $input->getOption('show');

        $queries = DbHelper::getUtf8mb4ConversionQueries();

        if ($show) {
            $this->showCommands($queries, $keepTracking, $output);
            return;
        }

        $output->writeln("This command will convert all Matomo database tables to utf8mb4.\n");

        if (DbHelper::getDefaultCharset() !== 'utf8mb4') {
            $this->writeSuccessMessage($output, array('Your database does not support utf8mb4'));
            return;
        }

        if (!$keepTracking) {
            $output->writeln("Tracking will be disabled during this process.\n");
        }

        $output->writeln('If you want to see what this command is going to do use the --show option.');

        if (!$yes) {
            $yes = $this->askForUpdateConfirmation($input, $output);
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

            $this->writeSuccessMessage($output, array('Conversion to utf8mb4 successful.'));

        } else {
            $this->writeSuccessMessage($output, array('Database conversion skipped.'));
        }
    }

    protected function showCommands($queries, $keepTracking, OutputInterface $output)
    {
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

    private function askForUpdateConfirmation(InputInterface $input, OutputInterface $output)
    {
        $helper   = $this->getHelper('question');
        $question = new ConfirmationQuestion('<comment>Execute updates? (y/N) </comment>', false);

        return $helper->ask($input, $output, $question);
    }
}
