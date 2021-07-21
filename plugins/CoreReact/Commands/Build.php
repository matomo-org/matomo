<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreReact\Commands;

use Piwik\Plugin\ConsoleCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Build extends ConsoleCommand
{
    protected function configure()
    {
        $this->setName('corereact:build');
        $this->setDescription('Build');
        $this->addArgument('plugin', InputArgument::IS_ARRAY | InputOption::VALUE_REQUIRED, 'List of plugins to build.');
        $this->addOption('env', null, InputOption::VALUE_REQUIRED, 'The environment to build for, either "development" or "production".', 'production');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $env = $input->getOption('env');
        $plugins = $input->getArgument('plugin');

        if (empty($plugins)) {
            throw new \InvalidArgumentException("No plugins specified.");
        }

        foreach ($plugins as $plugin) {
            $this->build($plugin, $env, $output);
        }
    }

    private function build($plugin, $env, OutputInterface $output)
    {
        $reactPath = PIWIK_INCLUDE_PATH . '/plugins/' . $plugin . '/react';
        if (!is_dir($reactPath)) {
            $output->writeln("Plugin $plugin does not have a react folder, skipping.");
            return;
        }

        $output->writeln("<comment>Building $plugin</comment>");

        $buildCommand = "cd '$reactPath' && NODE_ENV=$env node ../../CoreReact/react/scripts/build.js";
        passthru($buildCommand);
    }
}
