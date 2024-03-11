<?php

namespace Piwik\Plugins\TestRunner\Commands;

use Piwik\Exception\Exception;
use Piwik\Plugin\ConsoleCommand;

class CheckDirectDependencyUse extends ConsoleCommand
{
    public $usesFoundList = [];

    protected function configure()
    {
        parent::configure();

        $this->setName('tests:check-direct-dependency-use');
        $this->addRequiredValueOption('plugin', null, 'Run test only for a specific plugin');
        $this->addNoValueOption('grep-vendor', null, 'Do not skip vendor folders and scan them too');
        $this->setDescription('checks for direct dependency use in plugins');
    }

    protected function doExecute(): int
    {
        [$psr4NamespacePrefixes, $psr0Prefixes] = $this->getCoreDependencyNamespacePrefixes();

        $input = $this->getInput();
        $plugin = $input->getOption('plugin');
        $isGrepVendorFolder = $input->getOption('grep-vendor');

        if (!empty($plugin)) {
            $this->usesFoundList[$plugin] = [];
        }

        $this->grepUses($psr4NamespacePrefixes, 'psr4', $plugin, $isGrepVendorFolder);
        $this->grepUses($psr0Prefixes, 'psr0', $plugin, $isGrepVendorFolder);

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

    private function grepUses($prefixes, $psrType, $plugin, $isGrepVendorFolder)
    {
        foreach ($prefixes as $prefix) {
            $directUses = $this->grepForUses($prefix, $psrType, $plugin, $isGrepVendorFolder);
            if (!empty($directUses)) {
                $this->reportDirectUses($prefix, $directUses, $psrType);
            }
        }
    }

    private function grepForUses($prefix, $psrType, $plugin, $isGrepVendorFolder)
    {
        $uses = [];
        $rgOutput = [];

        if ($plugin) {
            $plugin = '/plugins/' . $plugin;
        }

        $vendorScan = '--glob=\\!vendor';
        if ($isGrepVendorFolder) {
            $vendorScan = '';
        }

        if ($psrType === 'psr4') {
            $prefix = rtrim($prefix, '\\');
            $regex = ' \\\\?' . preg_quote($prefix) . '\\b';
        } else if ($psrType === 'psr0') {
            $regex = '\\b' . preg_quote($prefix) . '_';
        }

        $command = 'rg \'' . $regex . '\' --glob=*.php ' . $vendorScan . ' --json --sort path ' . PIWIK_INCLUDE_PATH . $plugin;
        exec($command, $rgOutput, $returnCode);

        if (isset($returnCode) && $returnCode == 127) {
            throw new Exception('Please install ripgrep package, Check https://github.com/BurntSushi/ripgrep?tab=readme-ov-file#installation for installation');
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

            if ($pluginName) {
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
                $this->usesFoundList[rtrim($plugin, '\\')][rtrim($prefix, '\\')][] = $plugin . '/' . $file;
            }
            $output->writeln("  - $plugin, " . count($files) . " files");
        }
    }
}
