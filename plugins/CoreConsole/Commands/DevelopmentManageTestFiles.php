<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreConsole\Commands;

use Piwik\Development;
use Piwik\Plugin\ConsoleCommand;

class DevelopmentManageTestFiles extends ConsoleCommand
{
    public function isEnabled()
    {
        return Development::isEnabled();
    }

    protected function configure()
    {
        $this->setName('development:test-files');
        $this->setDescription("Manage test files.");

        $this->addRequiredArgument('operation', 'The operation to apply. Supported operations include: '
            . '"copy"');
        $this->addRequiredValueOption('file', null, "The file (or files) to apply the operation to.");

        // TODO: allow copying by regex pattern
    }

    protected function doExecute(): int
    {
        $operation = $this->getInput()->getArgument('operation');

        if ($operation == 'copy') {
            $this->copy();
        } else {
            throw new \Exception("Invalid operation '$operation'.");
        }

        return self::SUCCESS;
    }

    private function copy()
    {
        $file = $this->getInput()->getOption('file');

        $prefix = PIWIK_INCLUDE_PATH . '/tests/PHPUnit/System/processed/';
        $guesses = array(
            '/' . $file,
            $prefix . $file,
            $prefix . $file . '.xml'
        );

        foreach ($guesses as $guess) {
            if (is_file($guess)) {
                $file = $guess;
            }
        }

        copy($file, PIWIK_INCLUDE_PATH . '/tests/PHPUnit/System/expected/' . basename($file));
    }
}
