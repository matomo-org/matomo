<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\TestRunner\Commands;

use Piwik\Plugin\ConsoleCommand;

class TestsRunVue extends ConsoleCommand
{
    protected function configure()
    {
        $this->setName('tests:run-vue');
        $this->setDescription('Run Vue component unit tests');
        $this->setHelp(<<<'EOF'
The <info>%command.name%</info> command run all Vue component tests by default:

<info>ddev matomo:console %command.name%</info>

You can also run only one Vue spec by adding a specific file path, file name, or a regex:

<info>ddev matomo:console %command.name% plugins/CoreHome/vue/src/Alert/Alert.spec.ts</info>

<info>ddev matomo:console %command.name% Alert.spec.ts</info>

You can run the Vue tests for a specific plugin:

<info>ddev matomo:console %command.name% --plugin=CoreHome</info>

Notes:
- if <info>--plugin</info> is provided, <info>specs</info> arguments are ignored and discovery is scoped to that plugin.
- if the plugin does not exist, the command exits with a non-zero status.

It's possible to run the tests serially with verbose output:

<info>ddev matomo:console %command.name% --run-in-band --verbose</info>

EOF
        );

        $this->addOptionalArgument(
            'specs',
            'One or more Vue spec file paths or test path regex fragments. Separate multiple values by a space.',
            null,
            true
        );
        $this->addRequiredValueOption('plugin', null, 'The plugin to run Vue tests for (eg CoreHome or plugins/CoreHome).');
        $this->addNoValueOption('run-in-band', null, 'Run Jest tests serially in a single process.');
    }

    protected function doExecute(): int
    {
        $input = $this->getInput();
        $output = $this->getOutput();

        $plugin = $input->getOption('plugin');
        $runInBand = $input->getOption('run-in-band');
        $verbose = $output->isVerbose();
        $specs = $input->getArgument('specs');

        $testOptions = [];

        if ($runInBand) {
            $testOptions[] = '--runInBand';
        }

        if ($verbose) {
            $testOptions[] = '--verbose';
        }

        $pluginEnv = '';
        if (!empty($plugin)) {
            $plugin = trim((string) $plugin);
            if (strpos($plugin, 'plugins/') !== 0) {
                $plugin = 'plugins/' . $plugin;
            }

            $pluginPath = PIWIK_INCLUDE_PATH . '/' . $plugin;
            if (!is_dir($pluginPath)) {
                $output->writeln('<error>Plugin path not found: ' . $plugin . '</error>');
                $output->writeln('<error>Use --plugin=CoreHome or --plugin=plugins/CoreHome.</error>');
                return 1;
            }

            $pluginPattern = preg_quote($plugin, '/') . '\/vue\/.*\.spec\.[tj]s$';
            $testOptions[] = '--testPathPattern=' . escapeshellarg($pluginPattern);

            if (!empty($specs)) {
                $output->writeln('<comment>Ignoring specs arguments because --plugin scopes test discovery.</comment>');
            }

            $pluginEnv = 'MATOMO_CURRENT_PLUGIN=' . escapeshellarg($plugin) . ' ';
        } elseif (!empty($specs)) {
            $pattern = implode('|', $specs);
            $testOptions[] = '--testPathPattern=' . escapeshellarg($pattern);
        }

        $cmd = "cd '" . PIWIK_INCLUDE_PATH . "' && " . $pluginEnv . 'npm test';
        if (!empty($testOptions)) {
            $cmd .= ' -- ' . implode(' ', $testOptions);
        }

        $output->writeln('Executing command: <info>' . $cmd . '</info>');
        $output->writeln('');

        passthru($cmd, $returnCode);
        return $returnCode;
    }
}
