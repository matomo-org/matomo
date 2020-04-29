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

        $this->addOption('yes', null, InputOption::VALUE_NONE, Piwik::translate('CoreUpdater_ConsoleParameterDescription'));
    }

    public function isEnabled()
    {
        return strtolower(DbHelper::getUsedCharset()) !== 'utf8mb4';
    }

    /**
     * Execute command like: ./console core:convert-to-utf8mb4 --yes
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $yes = $input->getOption('yes');

        $queries = DbHelper::getUtf8mb4ConversionQueries();
        $output->writeln("This command will convert all Matomo database tables to utf8mb4. The following tables will be converted:\n");

        if (DbHelper::getDefaultCharset() !== 'utf8mb4') {
            $this->writeSuccessMessage($output, array('Your database does not support utf8mb4'));
            return;
        }

        $output->writeln(implode(', ', DbHelper::getTablesInstalled()));

        if (!$yes) {
            $yes = $this->askForUpdateConfirmation($input, $output);
        }

        if ($yes) {
            $output->writeln("\n" . Piwik::translate('CoreUpdater_ConsoleStartingDbUpgrade'));

            foreach ($queries as $query) {
                $output->write("\n" . 'Executing ' . $query . '... ');
                Db::get()->exec($query);
                $output->write(' done.');
            }

            try {
                $output->writeln("\n" . 'Updating used database charset in config.ini.php.');
                $config = Config::getInstance();
                $config->database['charset'] = 'utf8mb4';
                $config->forceSave();

                $this->writeSuccessMessage($output, array('Conversion to utf8mb4 successful.'));

            } catch (\Exception $e) {
                $this->writeSuccessMessage($output, array("All database tables have been converted successfully, but the database charset in config.ini.php could not be updated. Please manually update your config.ini.php with:\n[database]\ncharset = \"utf8mb4\""));
            }

        } else {
            $this->writeSuccessMessage($output, array('Database conversion skipped.'));
        }
    }

    private function askForUpdateConfirmation(InputInterface $input, OutputInterface $output)
    {
        $helper   = $this->getHelper('question');
        $question = new ConfirmationQuestion('<comment>Execute updates? (y/N) </comment>', false);

        return $helper->ask($input, $output, $question);
    }
}
