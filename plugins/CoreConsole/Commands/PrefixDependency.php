<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\CoreConsole\Commands;

use Piwik\Development;
use Piwik\Http;
use Piwik\Plugin\ConsoleCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 */
class PrefixDependency extends ConsoleCommand
{
    const PHP_SCOPER_VERSION = '0.17.5';

    public function isEnabled()
    {
        return Development::isEnabled();
    }

    protected function configure()
    {
        $this->setName('development:prefix-dependency');
        $this->setDescription('Prefix the namespace of every file in a composer dependency using php-scoper. Used to'
            . ' avoid collisions in environments where other third party software might use the same dependencies,'
            . ' like Matomo for Wordpress.');
        $this->addArgument('dependency', InputArgument::REQUIRED, 'The composer dependency to prefix, eg, "twig/twig"');
        $this->addOption('php-scoper-path', null, InputOption::VALUE_REQUIRED,
            'Specify a custom path to php-scoper. If not supplied, the PHAR will be downloaded from github.');
        $this->addOption('prefix', null, InputOption::VALUE_REQUIRED, 'The namespace prefix to use.',
            'Matomo\\Dependencies');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $phpScoperBinary = $this->downloadPhpScoperIfNeeded($input, $output);

        $command = $this->getPhpScoperCommand($phpScoperBinary, $input, $output);

        $output->writeln("Prefixing...");
        passthru($command);
        $output->writeln("Done.");
    }

    private function downloadPhpScoperIfNeeded(InputInterface $input, OutputInterface $output)
    {
        $customPhpScoperPath = $input->getOption('php-scoper-path');
        if ($customPhpScoperPath) {
            return $customPhpScoperPath;
        }

        $outputPath = PIWIK_INCLUDE_PATH . '/php-scoper.phar';
        $output->writeln("Downloading php-scoper from github...");
        Http::fetchRemoteFile('https://github.com/humbug/php-scoper/releases/download/'
            . self::PHP_SCOPER_VERSION . '/php-scoper.phar', $outputPath);
        $output->writeln("...Finished.");

        return $outputPath;
    }

    private function getPhpScoperCommand($phpScoperBinary, InputInterface $input, OutputInterface $output)
    {
        $dependency = $input->getArgument('dependency');
        $vendorPath = './vendor/' . $dependency;
        if (!is_dir($vendorPath)) {
            throw new \InvalidArgumentException('Cannot find dependency ' . $dependency);
        }

        $prefix = $input->getOption('prefix');

        $command = 'cd ' . $vendorPath . ' && ' . $phpScoperBinary . ' add --prefix="' . escapeshellarg($prefix)
            . '" --force --output-dir=./vendor/prefixed/' . $dependency;

        if ($output->getVerbosity() > OutputInterface::VERBOSITY_NORMAL) {
            $output->writeln("<comment>php-scoper command: $command</comment>");
        }

        return $command;
    }
}
