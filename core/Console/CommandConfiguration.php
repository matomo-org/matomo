<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Console;

use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * Configuration of a command.
 */
class CommandConfiguration
{
    private $name;
    private $processTitle;
    private $aliases = array();
    private $help;
    private $description;
    private $arguments = array();
    private $options = array();

    /**
     * Apply the configuration to a Symfony command.
     *
     * @param SymfonyCommand $command
     */
    public function apply(SymfonyCommand $command)
    {
        if ($this->name !== null) {
            $command->setName($this->name);
        }
        if ($this->processTitle !== null) {
            $command->setProcessTitle($this->processTitle);
        }
        if (! empty($this->aliases)) {
            $command->setAliases($this->aliases);
        }
        if ($this->help !== null) {
            $command->setHelp($this->help);
        }
        if ($this->description !== null) {
            $command->setDescription($this->description);
        }

        $definition = $command->getDefinition();
        $definition->addArguments($this->arguments);
        $definition->addOptions($this->options);
    }

    /**
     * Adds an argument.
     *
     * @param string $name        The argument name
     * @param int    $mode        The argument mode: InputArgument::REQUIRED or InputArgument::OPTIONAL
     * @param string $description A description text
     * @param mixed  $default     The default value (for InputArgument::OPTIONAL mode only)
     *
     * @return CommandConfiguration The current instance
     */
    public function addArgument($name, $mode = null, $description = '', $default = null)
    {
        $this->arguments[] = new InputArgument($name, $mode, $description, $default);

        return $this;
    }

    /**
     * Adds an option.
     *
     * @param string $name        The option name
     * @param string $shortcut    The shortcut (can be null)
     * @param int    $mode        The option mode: One of the InputOption::VALUE_* constants
     * @param string $description A description text
     * @param mixed  $default     The default value (must be null for InputOption::VALUE_REQUIRED or InputOption::VALUE_NONE)
     *
     * @return CommandConfiguration The current instance
     */
    public function addOption($name, $shortcut = null, $mode = null, $description = '', $default = null)
    {
        $this->options[] = new InputOption($name, $shortcut, $mode, $description, $default);

        return $this;
    }

    /**
     * Sets the name of the command.
     *
     * This method can set both the namespace and the name if
     * you separate them by a colon (:)
     *
     *     $command->setName('foo:bar');
     *
     * @param string $name The command name
     *
     * @return CommandConfiguration The current instance
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Sets the process title of the command.
     *
     * This feature should be used only when creating a long process command,
     * like a daemon.
     *
     * PHP 5.5+ or the proctitle PECL library is required
     *
     * @param string $title The process title
     *
     * @return CommandConfiguration The current instance
     */
    public function setProcessTitle($title)
    {
        $this->processTitle = $title;

        return $this;
    }

    /**
     * Sets the description for the command.
     *
     * @param string $description The description for the command
     *
     * @return CommandConfiguration The current instance
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Sets the help for the command.
     *
     * @param string $help The help for the command
     *
     * @return CommandConfiguration The current instance
     */
    public function setHelp($help)
    {
        $this->help = $help;

        return $this;
    }

    /**
     * Sets the aliases for the command.
     *
     * @param string[] $aliases An array of aliases for the command
     *
     * @return CommandConfiguration The current instance
     *
     * @throws \InvalidArgumentException When an alias is invalid
     */
    public function setAliases($aliases)
    {
        if (!is_array($aliases) && !$aliases instanceof \Traversable) {
            throw new \InvalidArgumentException('$aliases must be an array or an instance of \Traversable');
        }

        $this->aliases = $aliases;

        return $this;
    }
}
