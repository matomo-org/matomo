<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\CoreConsole\Commands;

use Piwik\View;
use Piwik\Plugin\ConsoleCommand;
use Piwik\Plugins\CoreConsole\TravisYmlView;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Exception;

/**
 * Command to generate an self-updating .travis.yml file either for Piwik Core or
 * an individual Piwik plugin.
 */
class GenerateTravisYmlFile extends ConsoleCommand
{
    protected function configure()
    {
        $this->setName('generate:travis-yml')
             ->setDescription('Generates a .travis.yml file for a plugin. The file can be auto-updating based on the parameters supplied.')
             ->addOption('plugin', null, InputOption::VALUE_REQUIRED, 'The plugin for whom a .travis.yml file should be generated.')
             ->addOption('core', null, InputOption::VALUE_NONE, 'Supplied when generating the .travis.yml file for Piwik core.'
                                                          . ' Should only be used by core developers.')
             ->addOption('artifacts-pass', null, InputOption::VALUE_REQUIRED,
                "Password to the Piwik build artifacts server. Will be encrypted in the .travis.yml file.")
             ->addOption('github-token', null, InputOption::VALUE_REQUIRED,
                "Github token of a user w/ push access to this repository. Used to auto-commit updates to the "
              . ".travis.yml file and checkout dependencies. Will be encrypted in the .travis.yml file.\n\n"
              . "If not supplied, the .travis.yml will fail the build if it needs updating.")
             ->addOption('dump', null, InputOption::VALUE_REQUIRED, "Debugging option. Saves the output .travis.yml to the specified file.");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $targetPlugin = $input->getOption('plugin');
        $artifactsPass = $input->getOption('artifacts-pass');
        $githubToken = $input->getOption('github-token');
        $outputYmlPath = $this->getTravisYmlOutputPath($input, $targetPlugin);
        $thisConsoleCommand = $this->getExecutedConsoleCommandForTravis($input);

        $view = new TravisYmlView();

        if (file_exists($outputYmlPath)) {
            $output->writeln("<info>Found existing YAML file at $outputYmlPath.</info>");

            $view->processExistingTravisYml($outputYmlPath);
        } else {
            $output->writeln("<info>Could not find existing YAML file at $outputYmlPath, generating a new one.</info>");
        }

        $view->configure($targetPlugin, $artifactsPass, $githubToken, $thisConsoleCommand, $output);
        $travisYmlContents = $view->render();

        $writePath = $input->getOption('dump');
        if (empty($writePath)) {
            $writePath = $outputYmlPath;
        }

        file_put_contents($writePath, $travisYmlContents);

        $this->writeSuccessMessage($output, array("Generated .travis.yml file at '$writePath'!"));
    }

    private function getTravisYmlOutputPath(InputInterface $input, $targetPlugin)
    {
        if ($input->getOption('core')) {
            return PIWIK_INCLUDE_PATH . '/.travis.yml';
        } else if ($targetPlugin) {
            $pluginDirectory = PIWIK_INCLUDE_PATH . '/plugins/' . $targetPlugin;
            if (!is_writable($pluginDirectory)) {
                throw new Exception("Cannot write to '$pluginDirectory'!");
            }

            return $pluginDirectory . '/.travis.yml';
        } else {
            throw new Exception("Neither --plugin option or --core option specified; don't know where to generate .travis.yml."
                              . " Execute './console help generate:travis-yml' for more info");
        }
    }

    private function getExecutedConsoleCommandForTravis(InputInterface $input)
    {
        $command = "php ./console " . $this->getName();

        $arguments = $input->getOptions();
        if (isset($arguments['github-token'])) {
            $arguments['github-token'] = '$GITHUB_USER_TOKEN';
        }
        if (isset($arguments['artifacts-pass'])) {
            $arguments['artifacts-pass'] = '$ARTIFACTS_PASS';
        }
        unset($arguments['dump']);

        foreach ($arguments as $name => $value) {
            if ($value === false
                || $value === null
            ) {
                continue;
            }

            if ($value === true) {
                $command .= " --$name";
            } else {
                $command .= " --$name=". addslashes($value);
            }
        }

        return $command;
    }
}