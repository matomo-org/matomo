<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugin;

use Piwik\Common;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * The base class for console commands.
 *
 * @api
 */
class ConsoleCommand extends SymfonyCommand
{
    public function writeSuccessMessage(OutputInterface $output, $messages)
    {
        $lengths = array_map('strlen', $messages);
        $maxLen = max($lengths) + 4;

        $separator = str_pad('', $maxLen, '*');

        $output->writeln('');
        $output->writeln('<info>' . $separator . '</info>');

        foreach ($messages as $message) {
            $output->writeln('  ' . $message . '  ');
        }

        $output->writeln('<info>' . $separator . '</info>');
        $output->writeln('');
    }

    protected function checkAllRequiredOptionsAreNotEmpty(InputInterface $input)
    {
        $options = $this->getDefinition()->getOptions();

        foreach ($options as $option) {
            $name  = $option->getName();
            $value = $input->getOption($name);

            if ($option->isValueRequired() && empty($value)) {
                throw new \InvalidArgumentException(sprintf('The required option %s is not set', $name));
            }
        }
    }
}
