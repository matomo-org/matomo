<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugin;

use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\NullOutput;
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

    /**
     * @var OutputInterface|null
     */
    private $output = null;

    /**
     * @var InputInterface|null
     */
    private $input = null;

    public function writeSuccessMessage($messages)
    {
        $this->getOutput()->writeln('');

        foreach ($messages as $message) {
            $this->getOutput()->writeln('<info>' . $message . '</info>');
        }

        $this->getOutput()->writeln('');
    }

    public function writeComment($messages)
    {
        $this->getOutput()->writeln('');

        foreach ($messages as $message) {
            $this->getOutput()->writeln('<comment>' . $message . '</comment>');
        }

        $this->getOutput()->writeln('');
    }

    protected function checkAllRequiredOptionsAreNotEmpty()
    {
        $options = $this->getDefinition()->getOptions();

        foreach ($options as $option) {
            $name  = $option->getName();
            $value = $this->getInput()->getOption($name);

            if ($option->isValueRequired() && empty($value)) {
                throw new \InvalidArgumentException(sprintf('The required option --%s is not set', $name));
            }
        }
    }

    final protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->input = $input;
        $this->output = $output;
        return $this->doExecute();
    }

    public function addNegatableOption(string $name, $shortcut = null, string $description = '', $default = null)
    {
        return parent::addOption($name, $shortcut, InputOption::VALUE_NEGATABLE, $description, $default);
    }

    public function addOptionalValueOption(string $name, $shortcut = null, string $description = '', $default = null, bool $acceptArrays = false)
    {
        $mode = $acceptArrays ? InputOption::VALUE_IS_ARRAY : 0;
        return parent::addOption($name, $shortcut, $mode | InputOption::VALUE_OPTIONAL, $description, $default);
    }

    public function addNoValueOption(string $name, $shortcut = null, string $description = '', $default = null)
    {
        return parent::addOption($name, $shortcut, InputOption::VALUE_NONE, $description, $default);
    }

    public function addRequiredValueOption(string $name, $shortcut = null, string $description = '', $default = null, bool $acceptArrays = false)
    {
        $mode = $acceptArrays ? InputOption::VALUE_IS_ARRAY : 0;
        return parent::addOption($name, $shortcut, $mode | InputOption::VALUE_REQUIRED, $description, $default);
    }

    public function addOption(string $name, $shortcut = null, int $mode = null, string $description = '', $default = null)
    {
        throw new \LogicException('addOption should not be used.');
    }

    public function addOptionalArgument(string $name, string $description = '', $default = null, bool $acceptArrays = false)
    {
        $mode = $acceptArrays ? InputOption::VALUE_IS_ARRAY : 0;
        parent::addArgument($name, $mode | InputArgument::OPTIONAL, $description, $default);
    }

    public function addRequiredArgument(string $name, string $description = '', $default = null, bool $acceptArrays = false)
    {
        $mode = $acceptArrays ? InputOption::VALUE_IS_ARRAY : 0;
        parent::addArgument($name, $mode | InputArgument::REQUIRED, $description, $default);
    }
    public function addArgument(string $name, int $mode = null, string $description = '', $default = null)
    {
        throw new \LogicException('addArgument should not be used.');
    }

    protected function doExecute(): int
    {
        throw new LogicException('You must override the doExecute() method in the concrete command class.');
    }

    final protected function interact(InputInterface $input, OutputInterface $output)
    {
        $this->doInteract();
    }

    protected function doInteract()
    {
    }

    final protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->doInitialize();
    }

    protected function doInitialize()
    {
    }

    /**
     * @return OutputInterface
     */
    protected function getOutput(): OutputInterface
    {
        return $this->output;
    }

    /**
     * @return InputInterface
     */
    protected function getInput(): InputInterface
    {
        return $this->input;
    }

    protected function askForConfirmation(string $question, bool $default = true, string $trueAnswerRegex = '/^y/i')
    {
        /** @var QuestionHelper $helper */
        $helper   = $this->getHelper('question');
        $question = new ConfirmationQuestion($question, $default, $trueAnswerRegex);
        return $helper->ask($this->getInput(), $this->getOutput(), $question);
    }

    /**
     * Ask the user for input and validates the provided value using the given callable
     *
     * @param string        $question
     * @param callable|null $validator
     * @param mixed|null    $default
     * @param iterable|null $autocompleterValues
     * @return mixed
     */
    protected function askAndValidate(
        string $question,
        callable $validator = null,
        $default = null,
        iterable $autocompleterValues = null
    )
    {
        /** @var QuestionHelper $helper */
        $helper   = $this->getHelper('question');
        $question = new Question($question, $default);
        $question->setValidator($validator);
        $question->setAutocompleterValues($autocompleterValues);
        return $helper->ask($this->getInput(), $this->getOutput(), $question);
    }

    /**
     * Ask the user for input
     *
     * @param string     $question
     * @param mixed|null $default
     * @return mixed
     */
    protected function ask(string $question, $default = null)
    {
        return $this->askAndValidate($question, null, $default);
    }

    /**
     * Initializes a progress bar for the current command
     *
     * Note: Only one progress bar can be used at a time
     *
     * @param int $numChangesToPerform
     * @return ProgressBar
     */
    protected function initProgressBar(int $numChangesToPerform = 0): ProgressBar
    {
        $this->progress = new ProgressBar($this->getOutput(), $numChangesToPerform);
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

    protected function renderTable(array $header, array $rows)
    {
        $table = new Table($this->getOutput());
        $table
            ->setHeaders($header)
            ->setRows($rows);
        $table->render();
    }

    /**
     * Runs a certain command
     * @param string $command
     * @param array  $arguments
     * @param bool   $hideOutput
     * @return int
     * @throws \Symfony\Component\Console\Exception\ExceptionInterface
     */
    protected function runCommand(string $command, array $arguments, bool $hideOutput = false): int
    {
        $command = $this->getApplication()->find($command);
        $arguments = ['command' => $command] + $arguments;
        $inputObject = new ArrayInput($arguments);
        $inputObject->setInteractive($this->getInput()->isInteractive());
        return $command->run($inputObject, $hideOutput ? new NullOutput() : $this->getOutput());
    }
}
