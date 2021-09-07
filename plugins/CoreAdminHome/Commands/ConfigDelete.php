<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreAdminHome\Commands;

use Piwik\Config;
use Piwik\Plugin\ConsoleCommand;
use Piwik\Settings\FieldConfig;
use Piwik\Settings\Plugin\SystemConfigSetting;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ConfigDelete extends ConsoleCommand
{

    // Message output if no matching setting is found.
    private const MSG_NOTHING_FOUND = 'Nothing found';
    // Message output on success.
    private const MSG_SUCCESS = 'Success: The setting has been deleted';

    protected function configure()
    {
        $this->setName('config:delete');
        $this->setDescription('Delete a config setting');
        $this->addArgument(
            'argument',
            InputArgument::OPTIONAL,
            "A config setting in the format Section.key or Section.array_key[], e.g. 'Database.username' or 'PluginsInstalled.PluginsInstalled[CustomDimensions]"
        );
        $this->addOption('section', 's', InputOption::VALUE_REQUIRED, 'The section the INI config setting belongs to.');
        $this->addOption('key', 'k', InputOption::VALUE_REQUIRED, 'The name of the INI config setting.');
        $this->addOption('value', 'i', InputOption::VALUE_REQUIRED, 'For arrays, specify the array value to be deleted.');

        $this->setHelp("This command can be used to delete a INI config setting.

You can delete config values per the two sections below, where:
- [Section] is the name of the section, e.g. database or General.
- [config_setting_name] the name of the setting, e.g. username.
- [array_value] For arrays, the specific array value to delete.

(1) By settings options --section=[Section] and --key=[config_setting_name], and optionally --value=[array_value].  Examples:
#Delete this setting.
$ ./console %command.name% --section=database --key=username
#Delete one value in an array:
$ ./console %command.name% --section=PluginsInstalled --key=PluginsInstalled --value=DevicesDetection

OR

(2) By using a command argument in the format [Section].[config_setting_name].[array_value]. Examples:
#Delete this setting.
$ ./console %command.name% 'database.username'
#Delete one value in an array:
$ ./console %command.name% 'PluginsInstalled.PluginsInstalled.DevicesDetection'

NOTES:
- Settings may still appear to exist if they are set in global.ini.php or elsewhere.
- Section names, key names, and array values are all case-sensitive; so e.g. --section=Database fails but --section=database works.  Look in config.ini.php and global.ini.php for the proper case.
- If no matching section/setting is found, the string \"" . self::MSG_NOTHING_FOUND . "\" shows.
- For safety, this tool cannot be used to delete a whole section of settings or an array of values in a single command.
");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        //Optionally could set this at runtime with: $output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE.
        $debug = false;

        // Gather options, then discard ones that are empty so we do not need to check for empty later.
        $options = array_filter([
            'section' => $input->getOption('section'),
            'key' => $input->getOption('key'),
            'value' => $input->getOption('value'),
        ]);
        $debug && fwrite(STDERR, PHP_EOL . __FUNCTION__ . '::Started with $options=' . (empty($options) ? '' : serialize($options)));

        $argument = trim($input->getArgument('argument'));
        $debug && fwrite(STDERR, PHP_EOL . __FUNCTION__ . '::Started with $argument=' . (empty($argument) ? '' : serialize($argument)));

        // Sanity check inputs.
        switch (true) {
            case empty($argument) && empty($options):
                throw new \InvalidArgumentException('You must set either an argument or set options --section and optional --key');
            case ! empty($argument) && ! empty($options):
                throw new \InvalidArgumentException('You cannot set both an argument (' . serialize($argument) . ') and options (' . serialize($argument) . ')');
            case empty($argument) && ( ! isset($options['section']) || empty($options['section']) || ! isset($options['key']) || empty($options['key'])):
                throw new \InvalidArgumentException('When using options, --section and --key must be set');
            case ! empty($argument):
                $settingStr = $argument;
                break;
            case ! empty($options):
                $settingStr = implode('.', $options);
                break;
            default:
                // We should not get here, but just in case.
                throw new \Exception('Some unexpected error occurred parsing input values');
        }
        $debug && fwrite(STDERR, PHP_EOL . __FUNCTION__ . "::Found \$settingStr=$settingStr");

        // Convenience wrapper used to augment SystemConfigSetting without extending SystemConfigSetting or adding random properties to the instance.
        $settingWrapped = (object) [
                'setting' => null,
                'isArray' => false,
                'arrayVal' => '',
        ];

        // Parse the $settingStr into a $settingWrapped object.
        $settingWrapped = self::parseSettingStr($settingStr, $settingWrapped);
        $debug && fwrite(STDERR, PHP_EOL . __FUNCTION__ . '::After parseSettingStr() \$settingWrapped=' . serialize($settingWrapped));

        // Check the setting exists and user has permissions, then populates the $settingWrapped properties.
        $settingWrapped = $this->checkAndPopulate($settingWrapped);
        $debug && fwrite(STDERR, PHP_EOL . __FUNCTION__ . '::After fileterSetting() \$settingWrapped=' . print_r($settingWrapped, true));

        if ( ! isset($settingWrapped->setting) || empty($settingWrapped->setting)) {
            $output->writeln(self::wrapInTag('comment', self::MSG_NOTHING_FOUND));
        } else {
            // Pass both static and array config items out to the delete logic.
            $debug && fwrite(STDERR, PHP_EOL . __FUNCTION__ . "::About to delete config for \$settingStr={$settingStr}");
            $result = $this->deleteConfigSetting($settingWrapped);
            $debug && fwrite(STDERR, PHP_EOL . __FUNCTION__ . '::Got $result=' . serialize($result));

            if ($result) {
                $output->writeln($this->wrapInTag('info', self::MSG_SUCCESS));
            }
        }

        //Many matomo script output Done when they're done.  IMO it's not needed: $output->writeln(self::wrapInTag('info', 'Done.'));
        $debug && fwrite(STDERR, PHP_EOL . __FUNCTION__ . '::Done');
    }

    /**
     * Check the setting exists and user has permissions, then return a new, populated SystemConfigSetting wrapper.
     *
     * @param object $settingWrapped A wrapped SystemConfigSetting object e.g. what is returned from parseSettingStr().
     * @return object A new wrapped SystemConfigSetting object.
     */
    private function checkAndPopulate(object $settingWrapped): object
    {
        $debug = false;
        $debug && fwrite(STDERR, PHP_EOL . __FUNCTION__ . '::Started with $settingWrapped=' . print_r($settingWrapped, true));

        // Sanity check inputs.
        if ( ! ($settingWrapped->setting instanceof SystemConfigSetting)) {
            throw new \InvalidArgumentException('This function expects $settingWrapped->setting to be a SystemConfigSetting instance');
        }

        $config = Config::getInstance();

        // Check the setting exists and user has permissions. If so, put it in the wrapper.
        switch (true) {
            case ! $settingWrapped->setting->isWritableByCurrentUser():
                throw new \Exception('No write permissions to this setting');
            case empty($sectionName = $settingWrapped->setting->getConfigSectionName()):
                throw new \InvalidArgumentException('A section name must be specified');
            case empty($settingName = $settingWrapped->setting->getName()):
                throw new \InvalidArgumentException('A setting name must be specified');
            case empty($section = $config->__get($sectionName)):
                $debug && fwrite(STDERR, PHP_EOL . __FUNCTION__ . "::No config section matches \$sectionName={$sectionName}");
                return new \stdClass();
            case empty($section = (object) $section) || ! isset($section->$settingName):
                $debug && fwrite(STDERR, PHP_EOL . __FUNCTION__ . "::Section {$sectionName} has no setting matching \$settingName={$settingName}");
                $debug && fwrite(STDERR, PHP_EOL . __FUNCTION__ . "::Section=" . print_r($section, true));
                return new \stdClass();
            default:
                // We have a valid scalar or array setting in a valid section, so just fall out of the switch statement.
                break;
        }

        $settingWrappedNew = clone($settingWrapped);
        $settingWrappedNew->isArray = is_array($section->$settingName);
        $debug && fwrite(STDERR, PHP_EOL . __FUNCTION__ . "Set \$settingWrappedNew->isArray={$settingWrappedNew->isArray}");

        if ( ! $settingWrappedNew->isArray && ! empty($settingWrappedNew->arrayVal)) {
            throw new \InvalidArgumentException('This config setting is not an array');
        }
        if ($settingWrappedNew->isArray) {
            $debug && fwrite(STDERR, PHP_EOL . __FUNCTION__ . "This config setting is an array");
            if (empty($settingWrappedNew->arrayVal)) {
                throw new \InvalidArgumentException('This config setting an array, and no array value was specified for deletion');
            }
            if (false === array_search($settingWrappedNew->arrayVal, $section->$settingName)) {
                $debug && fwrite(STDERR, PHP_EOL . __FUNCTION__ . "This config setting is an array, but does not contain the requested value");
                return new \stdClass();
            }
        }

        $debug && fwrite(STDERR, PHP_EOL . __FUNCTION__ . '::About to return $settingWrappedNew=' . print_r($settingWrappedNew, true));
        return $settingWrappedNew;
    }

    /**
     * Delete a single config section.setting or section.setting[array_key].
     *
     * @param object $settingWrapped Wrapper around a setting object describing what to get e.g. from self::make().
     * @return bool True on success.  If the delete fails, throws an exception.
     */
    private function deleteConfigSetting(object $settingWrapped): bool
    {
        $debug = false;
        $debug && fwrite(STDERR, PHP_EOL . __FUNCTION__ . '::Started with $settingWrapped=' . print_r($settingWrapped, true));

        // Sanity check inputs.
        if ( ! ($settingWrapped->setting instanceof SystemConfigSetting)) {
            throw new \InvalidArgumentException('This function expects $settingWrapped->setting to be a SystemConfigSetting instance');
        }

        // Make easy shortcuts to some info.
        $sectionName = $settingWrapped->setting->getConfigSectionName();
        $settingName = $settingWrapped->setting->getName();

        // Get the actual config section.
        $config = Config::getInstance();
        $section = $config->$sectionName;
        $setting = $section[$settingName];

        // Do the delete.
        // This does not do the job with value=null or value='': $config->setSetting($sectionName, $settingName, $value).
        switch (true) {
            case $settingWrapped->isArray === true && empty($settingWrapped->arrayVal):
                throw new \InvalidArgumentException('This function refuses to delete config arrays. See usage for how to delete config array values.');
            case $settingWrapped->isArray === true:
                // Array config values.
                $debug && fwrite(STDERR, PHP_EOL . __FUNCTION__ . '::This config setting is an array');

                $key = array_search($settingWrapped->arrayVal, $setting);
                $debug && fwrite(STDERR, PHP_EOL . __FUNCTION__ . '::Searched for \$settingWrapped->arrayVal=' . $settingWrapped->arrayVal . ' in array=' . print_r($settingWrapped->setting, true));
                if ($key !== false) {
                    unset($setting[$key]);
                }
                $debug && fwrite(STDERR, PHP_EOL . __FUNCTION__ . '::After unset $setting=' . print_r($setting, true));

                // Save the setting into the section.
                $section[$settingName] = $setting;
                $debug && fwrite(STDERR, PHP_EOL . __FUNCTION__ . '::After Save the setting into the section $section=' . print_r($section, true));
                break;
            default:
                // Scalar config values.
                $debug && fwrite(STDERR, PHP_EOL . __FUNCTION__ . '::This config setting is a scalar');

                // Remove the setting from the section.
                unset($section[$settingName]);
                $debug && fwrite(STDERR, PHP_EOL . __FUNCTION__ . '::After unset $section=' . print_r($section, true));
                break;
        }

        // Save the section into the config.
        $config->$sectionName = $section;

        // Save the config.
        $config->forceSave();

        $debug && fwrite(STDERR, PHP_EOL . __FUNCTION__ . '::Done');
        return true;
    }

    /**
     * Build a SystemConfigSetting object from a string.
     *
     * @param string $settingStr Config setting string to parse.
     * @return object A new wrapped SystemConfigSetting object.
     */
    public static function parseSettingStr(string $settingStr, object $settingWrapped): object
    {
        $debug = false;
        $debug && fwrite(STDERR, PHP_EOL . __FUNCTION__ . "::Started with \$settingStr={$settingStr}");

        $matches = [];
        if ( ! preg_match('/^([a-zA-Z0-9_]+)(?:\.([a-zA-Z0-9_]+))?(?:\[\])?(?:\.([a-zA-Z0-9_]+))?/', $settingStr, $matches) || empty($matches[1])) {
            throw new \InvalidArgumentException("Invalid input string='{$settingStr}': expected section.name or section.name[]");
        }

        $debug && fwrite(STDERR, __FUNCTION__ . '::Got regex $matches=' . serialize($matches));

        $settingName = $matches[2] ?? null;
        $arrayVal = $matches[3] ?? null;

        $systemConfigSetting = new SystemConfigSetting(
            // Setting name.
            $settingName,
            // Default value.
            '',
            // Type.
            FieldConfig::TYPE_STRING,
            // Plugin name.
            'core',
            // Section name.
            $matches[1]
        );

        $settingWrappedNew = clone($settingWrapped);
        $settingWrappedNew->setting = $systemConfigSetting;
        if ($settingWrappedNew->isArray = ! empty($arrayVal)) {
            $settingWrappedNew->arrayVal = $arrayVal;
        }

        return $settingWrappedNew;
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
