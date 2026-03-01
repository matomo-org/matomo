<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugin;

use Piwik\Container\StaticContainer;
use Piwik\Plugins\CoreConsole\FeatureFlags\SystemSignals;
use Piwik\Plugins\FeatureFlags\FeatureFlagManager;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Command\SignalableCommandInterface;
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
use Throwable;

/**
 * The base class for console commands.
 *
 * @api
 */
class ConsoleCommand extends SymfonyCommand implements SignalableCommandInterface
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

    /**
     * Sends the given message(s) as success message(s) to the output interface (surrounded by empty lines)
     *
     * @param string|string[] $messages
     */
    public function writeSuccessMessage($messages): void
    {
        if (is_string($messages)) {
            $messages = [$messages];
        }

        $this->getOutput()->writeln('');

        foreach ($messages as $message) {
            $this->getOutput()->writeln(self::wrapInTag('info', $message));
        }

        $this->getOutput()->writeln('');
    }

    /**
     * Sends the given message(s) as error message(s) to the output interface (surrounded by empty lines)
     *
     * @param string|string[] $messages
     */
    public function writeErrorMessage($messages): void
    {
        if (is_string($messages)) {
            $messages = [$messages];
        }

        $this->getOutput()->writeln('');

        foreach ($messages as $message) {
            $this->getOutput()->writeln(self::wrapInTag('error', $message));
        }

        $this->getOutput()->writeln('');
    }

    /**
     * Sends the given messages as comment message to the output interface (surrounded by empty lines)
     *
     * @param string|string[] $messages
     */
    public function writeComment($messages): void
    {
        if (is_string($messages)) {
            $messages = [$messages];
        }

        $this->getOutput()->writeln('');

        foreach ($messages as $message) {
            $this->getOutput()->writeln(self::wrapInTag('comment', $message));
        }

        $this->getOutput()->writeln('');
    }

    /**
     * Checks if all input options that are marked as requires-value were provided
     *
     * @throws \InvalidArgumentException
     */
    protected function checkAllRequiredOptionsAreNotEmpty(): void
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

    /**
     * This method can't be used.
     *
     * @see doExecute
     */
    final protected function execute(InputInterface $input, OutputInterface $output): int
    {
        return $this->doExecute();
    }

    /**
     * Method is final to make it impossible to overwrite it in plugin commands
     *
     */
    final public function run(InputInterface $input, OutputInterface $output): int
    {
        // Ensure input and output are available for methods like `doExecute`, `doInteract` and `doInitialize`
        $this->input  = $input;
        $this->output = $output;
        return parent::run($input, $output);
    }

    /**
     * Method is final to make it impossible to overwrite it in plugin commands
     * use getSystemSignalsToHandle() instead.
     *
     * Will only have an effect if the "SystemSignals" feature flag is enabled.
     *
     * @return array<int>
     */
    final public function getSubscribedSignals(): array
    {
        $canSubscribe = false;

        // The required DI configuration may not be loaded during the update process.
        // This can happen for an upgrade from a version that did not yet contain
        // the feature flag plugin.
        try {
            $featureFlagManager = StaticContainer::get(FeatureFlagManager::class);
            $canSubscribe       = $featureFlagManager->isFeatureActive(SystemSignals::class);
        } catch (Throwable $e) {
        }

        if (!$canSubscribe) {
            return [];
        }

        return $this->getSystemSignalsToHandle();
    }

    /**
     * Method is final to make it impossible to overwrite it in plugin commands
     * use handleSystemSignal() instead.
     *
     * Will only have an effect if the "SystemSignals" feature flag is enabled.
     */
    final public function handleSignal(int $signal): void
    {
        $this->handleSystemSignal($signal);
    }

    /**
     * Returns the list of system signals to subscribe.
     *
     * @return array<int>
     */
    public function getSystemSignalsToHandle(): array
    {
        return [];
    }

    /**
     * The method will be called when the application is signaled.
     */
    public function handleSystemSignal(int $signal): void
    {
    }

    /**
     * Adds a negatable option (e.g. --ansi / --no-ansi)
     *
     * @param array|null|string $shortcut
     * @param mixed             $default
     * @return ConsoleCommand
     */
    public function addNegatableOption(string $name, $shortcut = null, string $description = '', $default = null)
    {
        return parent::addOption($name, $shortcut, InputOption::VALUE_NEGATABLE, $description, $default);
    }

    /**
     * Adds an option with optional value
     *
     * @param array|null|string $shortcut
     * @param mixed             $default
     * @return ConsoleCommand
     */
    public function addOptionalValueOption(
        string $name,
        $shortcut = null,
        string $description = '',
        $default = null,
        bool $acceptArrays = false
    ) {
        $mode = $acceptArrays ? InputOption::VALUE_IS_ARRAY : 0;
        return parent::addOption($name, $shortcut, $mode | InputOption::VALUE_OPTIONAL, $description, $default);
    }

    /**
     * Adds a valueless option
     *
     * @param array|null|string $shortcut
     * @param mixed             $default
     * @return ConsoleCommand
     */
    public function addNoValueOption(string $name, $shortcut = null, string $description = '', $default = null)
    {
        return parent::addOption($name, $shortcut, InputOption::VALUE_NONE, $description, $default);
    }

    /**
     * Adds an option with required value
     *
     * @param array|null|string $shortcut
     * @param mixed             $default
     * @return ConsoleCommand
     */
    public function addRequiredValueOption(
        string $name,
        $shortcut = null,
        string $description = '',
        $default = null,
        bool $acceptArrays = false
    ) {
        $mode = $acceptArrays ? InputOption::VALUE_IS_ARRAY : 0;
        return parent::addOption($name, $shortcut, $mode | InputOption::VALUE_REQUIRED, $description, $default);
    }

    /**
     * This method can't be used.
     *
     * @see addNegatableOption, addOptionalValueOption, addNoValueOption, addRequiredValueOption
     */
    public function addOption(
        string $name,
        $shortcut = null,
        ?int $mode = null,
        string $description = '',
        $default = null
    ) {
        throw new \LogicException('addOption should not be used.');
    }

    /**
     * Adds an optional argument to the command
     *
     * @param string $name         Name of the command
     * @param null   $default
     * @param bool   $acceptArrays Defines if the option accepts multiple values (array)
     * @return ConsoleCommand
     */
    public function addOptionalArgument(
        string $name,
        string $description = '',
        $default = null,
        bool $acceptArrays = false
    ) {
        $mode = $acceptArrays ? InputArgument::IS_ARRAY : 0;
        return parent::addArgument($name, $mode | InputArgument::OPTIONAL, $description, $default);
    }

    /**
     * Adds a required argument to the command
     *
     * @param        $default
     * @param bool   $acceptArrays Defines if the option accepts multiple values (array)
     * @return ConsoleCommand
     */
    public function addRequiredArgument(
        string $name,
        string $description = '',
        $default = null,
        bool $acceptArrays = false
    ) {
        $mode = $acceptArrays ? InputArgument::IS_ARRAY : 0;
        return parent::addArgument($name, $mode | InputArgument::REQUIRED, $description, $default);
    }

    /**
     * This method can't be used.
     *
     * @see addOptionalArgument, addRequiredArgument
     */
    public function addArgument(string $name, ?int $mode = null, string $description = '', $default = null)
    {
        throw new \LogicException('addArgument can not be used.');
    }

    /**
     * Method that implements the actual command code
     *
     * @return int  use self::SUCCESS or self::FAILURE
     */
    protected function doExecute(): int
    {
        throw new LogicException('You must override the doExecute() method in the concrete command class.');
    }

    /**
     * This method can't be used.
     *
     * @see doInteract
     */
    final protected function interact(InputInterface $input, OutputInterface $output)
    {
        $this->doInteract();
    }

    /**
     * Interacts with the user.
     *
     * Can be overwritten by plugin command
     *
     * @see parent::interact()
     *
     */
    protected function doInteract(): void
    {
    }

    /**
     * This method can't be used.
     *
     * @see doInitialize
     */
    final protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->doInitialize();
    }

    /**
     * Initializes the command after the input has been bound and before the input is validated.
     *
     * Can be overwritten by plugin command
     *
     * @see parent::initialize()
     *
     */
    protected function doInitialize(): void
    {
    }

    protected function getOutput(): OutputInterface
    {
        return $this->output;
    }

    protected function setOutput(OutputInterface $output): void
    {
        $this->output = $output;
    }

    protected function getInput(): InputInterface
    {
        return $this->input;
    }

    /**
     * This method can't be used.
     *
     * @see askAndValidate(), askForConfirmation(), ask(), initProgressBar(), startProgressBar(), advanceProgressBar(), finishProgressBar(), renderTable()
     */
    public function getHelper(string $name)
    {
        throw new \LogicException('getHelper can not be used');
    }

    /**
     * Helper method to ask the user for confirmation
     *
     * @see QuestionHelper
     *
     */
    protected function askForConfirmation(string $question, bool $default = true, string $trueAnswerRegex = '/^y/i'): bool
    {
        /** @var QuestionHelper $helper */
        $helper   = parent::getHelper('question');
        $question = new ConfirmationQuestion($question, $default, $trueAnswerRegex);
        return (bool) $helper->ask($this->getInput(), $this->getOutput(), $question);
    }

    /**
     * Ask the user for input and validates the provided value using the given callable
     *
     * @see QuestionHelper
     *
     * @param mixed|null    $default
     * @param iterable|null $autocompleterValues
     * @return mixed
     */
    protected function askAndValidate(
        string $question,
        ?callable $validator = null,
        $default = null,
        ?iterable $autocompleterValues = null
    ) {
        /** @var QuestionHelper $helper */
        $helper   = parent::getHelper('question');
        $question = new Question($question, $default);
        $question->setValidator($validator);
        $question->setAutocompleterValues($autocompleterValues);
        return $helper->ask($this->getInput(), $this->getOutput(), $question);
    }

    /**
     * Ask the user for input
     *
     * @see QuestionHelper
     *
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
     * @see ProgressBar
     *
     */
    protected function initProgressBar(int $numChangesToPerform = 0): ProgressBar
    {
        $this->progress = new ProgressBar($this->getOutput(), $numChangesToPerform);
        return $this->progress;
    }

    /**
     * Starts a previously initialized progress bar
     *
     */
    protected function startProgressBar(int $numChangesToPerform = 0): void
    {
        $this->progress->start($numChangesToPerform);
    }

    /**
     * Advances the previously initialized progress bar
     *
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
     */
    protected function finishProgressBar(): void
    {
        if (empty($this->progress)) {
            throw new \Exception('No progress bar initialized.');
        }

        $this->progress->finish();
    }

    /**
     * Helper for rendering tables in console output
     *
     * @see Table
     *
     * @param array $header
     * @param array $rows
     * @return void
     */
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
     *
     * @param array  $arguments
     * @throws \Symfony\Component\Console\Exception\ExceptionInterface
     */
    protected function runCommand(string $command, array $arguments, bool $hideOutput = false): int
    {
        $command     = $this->getApplication()->find($command);
        $arguments   = ['command' => $command] + $arguments;
        $inputObject = new ArrayInput($arguments);
        $inputObject->setInteractive($this->getInput()->isInteractive());
        return $command->run($inputObject, $hideOutput ? new NullOutput() : $this->getOutput());
    }

    /**
     * Wrap the input string in an open and closing HTML/XML tag.
     * E.g. wrap_in_tag('info', 'my string') returns '<info>my string</info>'
     *
     * @param string $tagname Tag name to wrap the string in.
     * @param string $str String to wrap with the tag.
     * @return string The wrapped string.
     */
    public static function wrapInTag(string $tagname, string $str): string
    {
        return "<$tagname>$str</$tagname>";
    }
}
