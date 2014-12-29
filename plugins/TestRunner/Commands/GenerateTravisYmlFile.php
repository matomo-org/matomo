<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\TestRunner\Commands;

use Piwik\Plugins\TestRunner\TravisYml\Generator\CoreTravisYmlGenerator;
use Piwik\Plugins\TestRunner\TravisYml\Generator\PiwikTestsPluginsTravisYmlGenerator;
use Piwik\Plugins\TestRunner\TravisYml\Generator\PluginTravisYmlGenerator;
use Piwik\View;
use Piwik\Plugin\ConsoleCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Exception;

/**
 * Command to generate an self-updating .travis.yml file either for Piwik Core or
 * an individual Piwik plugin.
 */
class GenerateTravisYmlFile extends ConsoleCommand
{
    const COMMAND_NAME = 'generate:travis-yml';

    protected function configure()
    {
        $this->setName(self::COMMAND_NAME)
             ->setDescription('Generates a .travis.yml file for a plugin. The file can be auto-updating based on the parameters supplied.')
             ->addOption('plugin', null, InputOption::VALUE_REQUIRED, 'The plugin for whom a .travis.yml file should be generated.')
             ->addOption('core', null, InputOption::VALUE_NONE, 'Supplied when generating the .travis.yml file for Piwik core.'
                                                          . ' Should only be used by core developers.')
             ->addOption('piwik-tests-plugins', null, InputOption::VALUE_NONE, 'Supplied when generating the .travis.yml file for the '
                                                          . 'piwik-tests-plugins repository. Should only be used by core developers.')
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
        $generator = $this->createTravisYmlGenerator($input);
        $travisYmlContents = $generator->generate();

        $writePath = $generator->dumpTravisYmlContents($travisYmlContents);
        $this->writeSuccessMessage($output, array("Generated .travis.yml file at '$writePath'!"));
    }

    private function createTravisYmlGenerator(InputInterface $input)
    {
        $allOptions = $input->getOptions();

        $isCore = $input->getOption('core');
        if ($isCore) {
            return new CoreTravisYmlGenerator($allOptions);
        }

        $targetPlugin = $input->getOption('plugin');
        if ($targetPlugin) {
            return new PluginTravisYmlGenerator($targetPlugin, $allOptions);
        }

        $isPiwikTestsPlugin = $input->getOption('piwik-tests-plugins');
        if ($isPiwikTestsPlugin) {
            return new PiwikTestsPluginsTravisYmlGenerator($allOptions);
        }

        throw new Exception("Neither --plugin option, --core option or --piwik-tests-plugins specified; don't know what type"
            . " of .travis.yml file to generate. Execute './console help generate:travis-yml' for more info");
    }
}