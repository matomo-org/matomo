<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugin;

use Piwik\Log;
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
        $output->writeln('');

        foreach ($messages as $message) {
            $output->writeln('<info>' . $message . '</info>');
        }

        $output->writeln('');
    }

    public function writeComment(OutputInterface $output, $messages)
    {
        $output->writeln('');

        foreach ($messages as $message) {
            $output->writeln('<comment>' . $message . '</comment>');
        }

        $output->writeln('');
    }

    protected function checkAllRequiredOptionsAreNotEmpty(InputInterface $input)
    {
        $options = $this->getDefinition()->getOptions();

        foreach ($options as $option) {
            $name  = $option->getName();
            $value = $input->getOption($name);

            if ($option->isValueRequired() && empty($value)) {
                throw new \InvalidArgumentException(sprintf('The required option --%s is not set', $name));
            }
        }
    }

    public function run(InputInterface $input, OutputInterface $output)
    {
        // This method used to return `0` if a non numeric value was returned from execute()
        // To have our commands still compatible till the next major release we imitate that behaviour but output
        // an info message so old commands will get updated
        // @todo remove in Matomo 5
        try {
            return parent::run($input, $output);
        } catch (\TypeError $e) {
            if (strpos($e->getMessage(), 'Return value of "') === 0) {
                Log::info('Deprecation warning: ' . $e->getMessage() . "\nPlease update the command implementation, as this won't be supported by Matomo 5 anymore");
                return 0;
            }
            throw $e;
        }
    }
}
