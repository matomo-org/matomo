<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreAdminHome\Commands;

use Piwik\Config;
use Piwik\Plugin\ConsoleCommand;
use Piwik\Plugins\CoreAdminHome\Commands\SetConfig\ConfigSettingManipulation;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SetConfig extends ConsoleCommand
{
    protected function configure()
    {
        $this->setName('config:set');
        $this->setDescription('Set one or more config settings in the file config/config.ini.php');
        $this->addArgument('assignment', InputArgument::OPTIONAL | InputArgument::IS_ARRAY,
            "List of config setting assignments, eg, Section.key=1 or Section.array_key[]=value");
        $this->addOption('section', null, InputOption::VALUE_REQUIRED, 'The section the INI config setting belongs to.');
        $this->addOption('key', null, InputOption::VALUE_REQUIRED, 'The name of the INI config setting.');
        $this->addOption('value', null, InputOption::VALUE_REQUIRED, 'The value of the setting. (Not JSON encoded)');
        $this->setHelp("This command can be used to set INI config settings on a Piwik instance.

You can set config values two ways, via --section, --key, --value or by command arguments.

To use --section, --key, --value, simply supply those options. You can only set one setting this way, and you cannot
append to arrays.

To use arguments, supply one or more arguments in the following format: Section.config_setting_name=\"value\"
'Section' is the name of the section, 'config_setting_name' the name of the setting and 'value' is the value.
NOTE: 'value' must be JSON encoded, so Section.config_setting_name=\"value\" would work but
Section.config_setting_name=value would not.

To append to an array setting, supply an argument like this: Section.config_setting_name[]=\"value to append\"

To reset an array setting, supply an argument like this: Section.config_setting_name=[]
Resetting an array will not work if the array has default values in global.ini.php (such as, [log] log_writers).
In this case the values in global.ini.php will be used, since there is no way to explicitly set an
array setting to empty in INI config.

Use the --piwik-domain option to specify which instance to modify.

");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $section = $input->getOption('section');
        $key = $input->getOption('key');
        $value = $input->getOption('value');

        $manipulations = $this->getAssignments($input);

        $isSingleAssignment = !empty($section) && !empty($key) && $value !== false;
        if ($isSingleAssignment) {
            $manipulations[] = new ConfigSettingManipulation($section, $key, $value);
        }

        if (empty($manipulations)) {
            throw new \InvalidArgumentException("Nothing to assign. Add assignments as arguments or use the "
                . "--section, --key and --value options.");
        }

        $config = Config::getInstance();
        foreach ($manipulations as $manipulation) {
            $manipulation->manipulate($config);

            $output->writeln("<info>Setting [{$manipulation->getSectionName()}] {$manipulation->getName()} = {$manipulation->getValueString()}</info>");
        }

        $config->forceSave();

        $this->writeSuccessMessage($output, array("Done."));
    }

    /**
     * @return ConfigSettingManipulation[]
     */
    private function getAssignments(InputInterface $input)
    {
        $assignments = $input->getArgument('assignment');

        $result = array();
        foreach ($assignments as $assignment) {
            $result[] = ConfigSettingManipulation::make($assignment);
        }
        return $result;
    }
}