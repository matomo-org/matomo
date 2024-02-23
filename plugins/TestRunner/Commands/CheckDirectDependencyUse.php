<?php

namespace Piwik\Plugins\TestRunner\Commands;

use Piwik\Plugin\ConsoleCommand;

class CheckDirectDependencyUse extends ConsoleCommand
{
    public $usesFoundList = [];

    protected function configure()
    {
        parent::configure();

        $this->setName('localdev:check-direct-dependency-use');
        $this->addRequiredValueOption('plugin', null, 'Run only for a specific plugin');
        $this->setDescription('checks for direct dependency use in plugins');
    }

    protected function doExecute(): int
    {
        [$psr4NamespacePrefixes, $psr0Prefixes] = $this->getCoreDependencyNamespacePrefixes();

        $input = $this->getInput();
        $plugin = $input->getOption('plugin');

        if (!empty($plugin)) {
            $this->usesFoundList[$plugin] = [];
        }

        foreach ($psr4NamespacePrefixes as $prefix) {
            $directUses = $this->grepForUses($prefix, 'psr4', $plugin);
            if (!empty($directUses)) {
                $this->reportDirectUses($prefix, $directUses, 'psr4');
            }
        }

        foreach ($psr0Prefixes as $prefix) {
            $directUses = $this->grepForUses($prefix, 'psr0', $plugin);
            if (!empty($directUses)) {
                $this->reportDirectUses($prefix, $directUses, 'psr0');
            }
        }

        return self::SUCCESS;
    }

    private function getCoreDependencyNamespacePrefixes()
    {
        $psr4NamespacePrefixes = [];
        $psr0Prefixes = [];

        $coreComposerLock = PIWIK_INCLUDE_PATH . '/composer.lock';
        $coreComposerLockContents = file_get_contents($coreComposerLock);
        $coreComposerLockContents = json_decode($coreComposerLockContents, true);

        foreach ($coreComposerLockContents['packages'] as $package) {
            $psr4NamespacePrefixes = array_merge(
                $psr4NamespacePrefixes,
                array_keys($package['autoload']['psr-4'] ?? [])
            );

            $psr0Prefixes = array_merge(
                $psr0Prefixes,
                array_keys($package['autoload']['psr-0'] ?? [])
            );
        }

        $psr4NamespacePrefixes = array_filter($psr4NamespacePrefixes);
        $psr4NamespacePrefixes = array_unique($psr4NamespacePrefixes);

        $psr0Prefixes = array_filter($psr0Prefixes);
        $psr0Prefixes = array_unique($psr0Prefixes);

        return [$psr4NamespacePrefixes, $psr0Prefixes];
    }

    private function grepForUses($prefix, $psrType, $plugin)
    {
        $uses = [];
        $rgOutput = [];

        if ($plugin) {
            $plugin = '/' . $plugin;
        }

        if ($psrType === 'psr4') {
            $prefix = rtrim($prefix, '\\');
            $regex = ' \\\\?' . preg_quote($prefix) . '\\b';
            $command = 'rg \'' . $regex . '\' --glob=*.php --glob=\\!vendor --json ' . PIWIK_INCLUDE_PATH . '/plugins' . $plugin;

            exec($command, $rgOutput, $returnCode);
        } else if ($psrType === 'psr0') {
            $regex = '\\b' . preg_quote($prefix) . '_';
            $command = 'rg \'' . $regex . '\' --glob=*.php --glob=\\!vendor --json ' . PIWIK_INCLUDE_PATH . '/plugins' . $plugin;

            exec($command, $rgOutput, $returnCode);
        }

        foreach ($rgOutput as $line) {
            $line = json_decode($line, true);
            if ($line['type'] !== 'match') {
                continue;
            }

            $path = $line['data']['path']['text'];
            $path = str_replace(PIWIK_INCLUDE_PATH, '', $path);
            $path = ltrim($path, '/');

            $parts = explode('/', $path);
            array_shift($parts);
            $pluginName = array_shift($parts);

            if (file_exists(PIWIK_INCLUDE_PATH . '/plugins/' . $pluginName . '/.git')) {
                $remainingPath = implode('/', $parts);
                $uses[$pluginName][] = $remainingPath;
            }
        }

        foreach ($uses as $pluginName => $entries) {
            $uses[$pluginName] = array_unique($entries);
        }

        return $uses;
    }

    private function reportDirectUses($prefix, $directUses, $type)
    {
        $output = $this->getOutput();
        $output->writeln("<info>Found '$prefix' ($type) usage in:</info>");
        foreach ($directUses as $plugin => $files) {
            foreach ($files as $file) {
                $this->usesFoundList[$plugin][$prefix][] = $plugin . '\\' . $file;
            }
            $output->writeln("  - $plugin, " . count($files) . " files");
        }
    }
}
