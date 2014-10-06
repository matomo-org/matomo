<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link     http://piwik.org
 * @license  http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\LanguagesManager\Commands;

use Piwik\Plugin\ConsoleCommand;
use Piwik\Translate;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 */
class CompareKeys extends ConsoleCommand
{
    protected function configure()
    {
        $this->setName('translations:compare')
            ->setDescription('Updates translation files')
            ->addOption('username', 'u', InputOption::VALUE_OPTIONAL, 'oTrance username')
            ->addOption('password', 'p', InputOption::VALUE_OPTIONAL, 'oTrance password');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $command = $this->getApplication()->find('translations:fetch');
        $arguments = array(
            'command'    => 'translations:fetch',
            '--username' => $input->getOption('username'),
            '--password' => $input->getOption('password'),
            '--keep-english' => true,
        );
        $inputObject = new ArrayInput($arguments);
        $inputObject->setInteractive($input->isInteractive());
        $command->run($inputObject, $output);

        $englishFromOTrance = FetchFromOTrance::getDownloadPath() . DIRECTORY_SEPARATOR . 'en.json';

        if (!file_exists($englishFromOTrance)) {
            $output->writeln("English file from oTrance missing. Aborting");
            return;
        }

        $englishFromOTrance = json_decode(file_get_contents($englishFromOTrance), true);

        Translate::reloadLanguage('en');
        $availableTranslations = $GLOBALS['Piwik_translations'];

        $categories = array_unique(array_merge(array_keys($englishFromOTrance), array_keys($availableTranslations)));
        sort($categories);

        $unnecessary = $outdated = $missing = array();

        foreach ($categories as $category)
        {
            if (!empty($englishFromOTrance[$category])) {
                foreach ($englishFromOTrance[$category] as $key => $value) {
                    if (!array_key_exists($category, $availableTranslations) || !array_key_exists($key, $availableTranslations[$category])) {
                        $unnecessary[] = sprintf('%s_%s', $category, $key);
                        continue;
                    } else if (html_entity_decode($availableTranslations[$category][$key]) != html_entity_decode($englishFromOTrance[$category][$key])) {
                        $outdated[] = sprintf('%s_%s', $category, $key);
                        continue;
                    }
                }
            }
            if (!empty($availableTranslations[$category])) {
                foreach ($availableTranslations[$category] as $key => $value) {
                    if (!array_key_exists($category, $englishFromOTrance) || !array_key_exists($key, $englishFromOTrance[$category])) {
                        $missing[] = sprintf('%s_%s', $category, $key);
                        continue;
                    }
                }
            }
        }

        $output->writeln("");

        if (!empty($missing)) {
            $output->writeln("<bg=yellow;options=bold>-- Following keys are missing on oTrance --</bg=yellow;options=bold>");
            $output->writeln(implode("\n", $missing));

            $output->writeln("");
        }

        if (!empty($unnecessary)) {
            $output->writeln("<bg=yellow;options=bold>-- Following keys might be unnecessary on oTrance --</bg=yellow;options=bold>");
            $output->writeln(implode("\n", $unnecessary));

            $output->writeln("");
        }

        if (!empty($outdated)) {
            $output->writeln("<bg=yellow;options=bold>-- Following keys are outdated on oTrance --</bg=yellow;options=bold>");
            $output->writeln(implode("\n", $outdated));

            $output->writeln("");
        }

        $output->writeln("Finished.");
    }
}
