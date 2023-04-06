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
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

/**
 * The base class for console commands.
 *
 * @api
 */
class ConsoleCommand extends SymfonyCommand
{
    /**
     * @var ProgressBar|null
     */
    private $progress = null;

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
        // @todo remove in Matomo 6
        try {
            return parent::run($input, $output);
        } catch (\TypeError $e) {
            if (strpos($e->getMessage(), 'Return value of "') === 0) {
                Log::info('Deprecation warning: ' . $e->getMessage() . "\nPlease update the command implementation to return an int instead, as this won't be supported by Matomo 6 anymore");
                return self::SUCCESS;
            }
            throw $e;
        }
    }

    protected function askForConfirmation(InputInterface $input, OutputInterface $output, string $question, bool $default = true, string $trueAnswerRegex = '/^y/i')
    {
        /** @var QuestionHelper $helper */
        $helper   = $this->getHelper('question');
        $question = new ConfirmationQuestion($question, $default, $trueAnswerRegex);
        return $helper->ask($input, $output, $question);
    }

    /**
     * Ask the user for input and validates the provided value using the given callable
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param string          $question
     * @param callable|null   $validator
     * @param mixed|null      $default
     * @param iterable|null   $autocompleterValues
     * @return mixed
     */
    protected function askAndValidate(InputInterface $input, OutputInterface $output, string $question, callable $validator = null, $default = null, iterable $autocompleterValues = null)
    {
        /** @var QuestionHelper $helper */
        $helper   = $this->getHelper('question');
        $question = new Question($question, $default);
        $question->setValidator($validator);
        $question->setAutocompleterValues($autocompleterValues);
        return $helper->ask($input, $output, $question);
    }

    /**
     * Ask the user for input
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param string          $question
     * @param mixed|null      $default
     * @return mixed
     */
    protected function ask(InputInterface $input, OutputInterface $output, string $question, $default = null)
    {
        return $this->askAndValidate($input, $output, $question, null, $default);
    }

    /**
     * Initializes a progress bar for the current command
     *
     * Note: Only one progress bar can be used at a time
     *
     * @param OutputInterface $output
     * @param int             $numChangesToPerform
     * @return ProgressBar
     */
    protected function initProgressBar(OutputInterface $output, int $numChangesToPerform = 0): ProgressBar
    {
        $this->progress = new ProgressBar($output, $numChangesToPerform);
        return $this->progress;
    }

    /**
     * Starts a previously initialized progress bar
     *
     * @param int $numChangesToPerform
     * @return void
     */
    protected function startProgressBar(int $numChangesToPerform = 0): void
    {
        $this->progress->start($numChangesToPerform);
    }

    /**
     * Advances the previously initialized progress bar
     *
     * @param int $step
     * @return void
     */
    protected function advanceProgressBar(int $step = 1): void
    {
        if (empty($this->progress)) {
            throw new \Exception('No progress bar initialized.');
        }

        $this->progress->advance($step);
    }

    /**
     * Finished the initialized progress bar
     *
     * @return void
     */
    protected function finishProgressBar(): void
    {
        if (empty($this->progress)) {
            throw new \Exception('No progress bar initialized.');
        }

        $this->progress->finish();
    }
}
