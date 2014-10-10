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
             ->addOption('php-versions', null, InputOption::VALUE_OPTIONAL,
                "List of PHP versions to test against, ie, 5.4,5.5,5.6. Defaults to: 5.3.3,5.4,5.5,5.6.")
             ->addOption('dump', null, InputOption::VALUE_REQUIRED, "Debugging option. Saves the output .travis.yml to the specified file.");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $targetPlugin  = $input->getOption('plugin');
        $outputYmlPath = $this->getTravisYmlOutputPath($input, $targetPlugin);

        $view = $this->createTravisYmlView($input, $output, $targetPlugin, $outputYmlPath);
        $travisYmlContents = $view->render();

        $this->dumpTravisYmlContents($input, $output, $outputYmlPath, $travisYmlContents);
    }

    private function createTravisYmlView(InputInterface $input, OutputInterface $output, $targetPlugin, $outputYmlPath)
    {
        $view = new TravisYmlView();
        $view->setPlugin($targetPlugin);

        $thisConsoleCommand = $this->getExecutedConsoleCommandForTravis($input);
        $view->setGenerateYmlCommand($thisConsoleCommand);

        $phpVersions = $input->getOption('php-versions');
        if (!empty($phpVersions)) {
            $view->setPhpVersions(explode(',', $phpVersions));
        }

        if (file_exists($outputYmlPath)) {
            $output->writeln("<info>Found existing YAML file at $outputYmlPath.</info>");

            $view->processExistingTravisYml($outputYmlPath);
        } else {
            $output->writeln("<info>Could not find existing YAML file at $outputYmlPath, generating a new one.</info>");
        }

        $this->setExtraEnvironmentVariables($view, $input, $output);

        return $view;
    }

    private function dumpTravisYmlContents(InputInterface $input, OutputInterface $output, $outputYmlPath, $travisYmlContents)
    {
        $writePath = $input->getOption('dump');
        if (empty($writePath)) {
            $writePath = $outputYmlPath;
        }

        file_put_contents($writePath, $travisYmlContents);

        $this->writeSuccessMessage($output, array("Generated .travis.yml file at '$writePath'!"));
    }

    private function setExtraEnvironmentVariables(TravisYmlView $view, InputInterface $input, OutputInterface $output)
    {
        if (!empty($view->existingEnv)) {
            $output->writeln("<info>Existing .yml file found, ignoring global variables specified on command line.</info>");
            return;
        }

        $extraVars = array();

        $artifactsPass = $input->getOption('artifacts-pass');
        if (!empty($artifactsPass)) {
            $extraVars[] = $this->travisEncrypt("ARTIFACTS_PASS=" . $artifactsPass, $view, $output);
        }

        $githubToken = $input->getOption('github-token');
        if (!empty($githubToken)) {
            $extraVars[] = $this->travisEncrypt("GITHUB_USER_TOKEN=" . $githubToken, $view, $output);
        }

        $view->setExtraGlobalEnvVars($extraVars);
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
        unset($arguments['github-token']);
        unset($arguments['artifacts-pass']);
        unset($arguments['dump']);

        foreach ($arguments as $name => $value) {
            if ($value === false
                || $value === null
            ) {
                continue;
            }

            if ($value === true) {
                $command .= " --$name";
            } else if (is_array($value)) {
                foreach ($value as $arrayValue) {
                    $command .= " --$name=\"" . addslashes($arrayValue) . "\"";
                }
            } else {
                $command .= " --$name=\"" . addslashes($value) . "\"";
            }
        }

        return $command;
    }


    private function travisEncrypt($data, TravisYmlView $view, OutputInterface $output)
    {
        $output->writeln("Encrypting \"$data\"...");

        $command = "travis encrypt \"$data\"";

        // change dir to target plugin since plugin will be in its own git repo
        if (!empty($view->pluginName)) {
            $command = "cd \"" . $view->getPluginRootFolder() . "\" && " . $command;
        }

        exec($command, $commandOutput, $returnCode);
        if ($returnCode !== 0) {
            throw new Exception("Cannot encrypt \"$data\" for travis! Please make sure you have the travis command line "
                              . "utility installed (see http://blog.travis-ci.com/2013-01-14-new-client/).\n\n"
                              . "return code: $returnCode\n\n"
                              . "travis output:\n\n" . implode("\n", $commandOutput));
        }

        if (empty($commandOutput)) {
            throw new Exception("Cannot parse travis encrypt output:\n\n" . implode("\n", $commandOutput));
        }

        // when not executed from a command line travis encrypt will return only the encrypted data
        $encryptedData = $commandOutput[0];

        if (substr($encryptedData, 0, 1) != '"'
            || substr($encryptedData, -1) != '"'
        ) {
            $encryptedData = '"' . addslashes($encryptedData) . '"';
        }

        return "secure: " . $encryptedData;
    }
}