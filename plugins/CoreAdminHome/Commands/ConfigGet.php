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
use Spyc;

class ConfigGet extends ConsoleCommand
{

    //SystemConfigSetting throws an error if the setting name is empty, so use a fake one that is unlikely to actually exist, which we will check for later.
    private const NO_SETTING_NAME_FOUND_PLACEHOLDER = 'ConfigGet_FAKE_SETTING_NAME';
    // Valid output formats.
    public const OUTPUT_FORMATS = ['json', 'yaml', 'text'];
    // Default output format.
    public const OUTPUT_FORMAT_DEFAULT = 'json';
    // Message output if no matching setting is found.
    private const MSG_NOTHING_FOUND = 'Nothing found';

    protected function configure()
    {
        $this->setName('config:get');
        $this->setDescription('Get a config value or section');
        $this->addArgument(
            'argument',
            InputArgument::OPTIONAL,
            "A config setting in the format Section.key or Section.array_key[], e.g. 'Database.username' or 'PluginsInstalled'"
        );
        $this->addOption('section', 's', InputOption::VALUE_REQUIRED, 'The section the INI config setting belongs to.');
        $this->addOption('key', 'k', InputOption::VALUE_REQUIRED, 'The name of the INI config setting.');
        $this->addOption('format', 'f', InputOption::VALUE_OPTIONAL, 'Format the output as ' . json_encode(self::OUTPUT_FORMATS) . '; Default is ' . self::OUTPUT_FORMAT_DEFAULT, self::OUTPUT_FORMAT_DEFAULT);

        $this->setHelp("This command can be used to get a INI config setting or a whole section of settings on a Piwik instance.

You can get config values per the two sections below, where:
- [Section] is the name of the section, e.g. database or General.
- [config_setting_name] the name of the setting, e.g. username.

(1) By settings options --section=[Section] and --key=[config_setting_name].  The option --section is required. Examples:
#Return all settings in this section.
$ ./console %command.name% --section=database
#Return only this setting.
$ ./console %command.name% --section=database --key=username

OR

(2) By using a command argument in the format [Section].[config_setting_name]. Examples:
#Return all settings in this section.
$ ./console %command.name% 'database'
#Return all settings in this array; square brackets are optional.
$ ./console %command.name% 'PluginsInstalled.PluginsInstalled'
$ ./console %command.name% 'PluginsInstalled.PluginsInstalled[]'
#Return only this setting.
$ ./console %command.name% 'database.username'

NOTES:
- Section and key names are case-sensitive; so e.g. --section=Database fails but --section=database works.  Look in global.ini.php for the proper case.
- If no matching section/setting is found, the string \"" . self::MSG_NOTHING_FOUND . "\" shows.
- Else the output will be shown JSON-encoded.  You can use something like https://stedolan.github.io/jq to parse it.
- If you ask for 'PluginsInstalled.PluginsInstalled[\"some_array_item\"]', it will ignore the array key (\"some_array_item\") and you will get back the whole array of values (e.g. all PluginsInstalled[] values).
");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Gather options, then discard ones with an empty value so we do not need to check for empty later.
        $options = array_filter([
            'section' => $input->getOption('section'),
            'key' => $input->getOption('key'),
        ]);

        // If none specified, set default format.
        $format = $input->getOption('format');
        if (empty($format) || !in_array($format, self::OUTPUT_FORMATS, true)) {
            $format = self::OUTPUT_FORMAT_DEFAULT;
        }

        $argument = trim($input->getArgument('argument') ?? '');

        // If there are multiple arguments, just use the last one.
        $argument = array_slice(explode(' ', $argument), -1)[0];

        // Sanity check inputs.
        switch (true) {
            case empty($argument) && empty($options):
                throw new \InvalidArgumentException('You must set either an argument or set options --section and optional --key');
            case (!empty($argument) && !empty($options)):
                throw new \InvalidArgumentException('You cannot set both an argument (' . serialize($argument) . ') and options (' . serialize($argument) . ')');
            case empty($argument) && (!isset($options['section']) || empty($options['section'])):
                throw new \InvalidArgumentException('When using options, the --section value must be set');
            case (!empty($argument)):
                $settingStr = $argument;
                break;
            case (!empty($options)):
                $settingStr = implode('.', $options);
                break;
            default:
                // We should not get here, but just in case.
                throw new \Exception('Some unexpected error occurred in ' . __FUNCTION__ . ' at line ' . __LINE__);
        }

        // Parse the $settingStr into a SystemConfigSetting object.
        $setting = self::parseSettingStr($settingStr);

        $result = $this->getConfigValue(Config::getInstance(), $setting);

        if (empty($result)) {
            $output->writeln(self::wrapInTag('comment', self::MSG_NOTHING_FOUND));
        } else {
            $output->writeln($this->formatVariableForOutput($setting, $result, $format));
        }

        //Many matomo script output Done when they're done.  IMO it's not needed: $output->writeln(self::wrapInTag('info', 'Done.'));
    }

    /**
     * Get a config section or section.value.
     *
     * @param Config $config A Matomo Config instance.
     * @param SystemConfigSetting $setting A setting object describing what to get e.g. from self::make().
     * @return Mixed The config section or value.
     */
    private function getConfigValue(Config $config, SystemConfigSetting $setting)
    {

        // This should have been caught in the calling function, so assume a bad implementation and throw an error.
        if (empty($sectionName = $setting->getConfigSectionName())) {
            throw new \InvalidArgumentException('A section name must be specified');
        }
        if (empty($section = $config->__get($sectionName))) {
            return null;
        }

        // Convert array to object since it is slightly cleaner/easier to work with.
        $section = (object) $section;

        // Look for the specific setting.
        $settingName = $setting->getName();

        // Return the whole setting section if requested.
        // The name=FAKE_SETTING_NAME is a placeholder for when no setting is specified.
        if (empty($settingName) || $settingName === self::NO_SETTING_NAME_FOUND_PLACEHOLDER) {
            $sectionContents = $section;
            return (array) $sectionContents;
        }


        switch (true) {
            case (!isset($section->$settingName)):
                $settingValue = null;
                break;
            case is_array($settingValue = $section->$settingName):
                break;
            default:
                $settingValue = $setting->getValue();
        }

        return $settingValue;
    }

    /**
     * Build a SystemConfigSetting object from a string.
     *
     * @param string $settingStr Config setting string to parse.
     * @return SystemConfigSetting A SystemConfigSetting object.
     */
    public static function parseSettingStr(string $settingStr): SystemConfigSetting
    {

        $matches = [];
        if (!preg_match('/^([a-zA-Z0-9_]+)(?:\.([a-zA-Z0-9_]+))?(\[\])?/', $settingStr, $matches) || empty($matches[1])) {
            throw new \InvalidArgumentException("Invalid input string='{$settingStr}' =expected section.name or section.name[]");
        }


        return new SystemConfigSetting(
            // Setting name. SystemConfigSetting throws an error if the setting name is empty, so use placeholder that flags that no setting was specified.
            $matches[2] ?? self::NO_SETTING_NAME_FOUND_PLACEHOLDER,
            // Default value.
            '',
            // Type.
            FieldConfig::TYPE_STRING,
            // Plugin name.
            'core',
            // Section name.
            $matches[1]
        );
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

    /**
     * Format the config setting to the specified output format.
     *
     * @param SystemConfigSetting $setting The found SystemConfigSetting.
     * @param mixed $var The config setting -- either scalar or an array of settings.
     * @param string $format The output format: One of self::OUTPUT_FORMAT_DEFAULT.
     * @return string The formatted output.
     */
    private function formatVariableForOutput(SystemConfigSetting $setting, $var, string $format = self::OUTPUT_FORMAT_DEFAULT): string
    {

        switch ($format) {
            case 'json':
                return $this->toJson($var);
            case 'yaml':
                return $this->toYaml($var);
            case 'text':
                return $this->toText($setting, $var);
            default:
                throw new \InvalidArgumentException('Unsupported output format');
        }
    }

    /**
     * Convert $var to a YAML string.
     * Throws an error on invalid types (a PHP resource or object).
     *
     * @param mixed $var The variable to convert.
     * @return string The Yaml-formatted string.
     */
    private function toYaml($var): string
    {

        // Remove leading dash and spaces Spyc adds so we just output the bare value.
        return trim(ltrim(Spyc::YAMLDump($var, 2, 0, true), '-'));
    }

    /**
     * Convert $var to a JSON string.
     * Throws an error on json_encode failure.
     *
     * @param mixed $var The variable to convert.
     * @return string The JSON-formatted string.
     */
    private function toJson($var): string
    {
        $result = json_encode($var, JSON_UNESCAPED_SLASHES);
        if ($result === false) {
            throw new \Exception('Failed to json_encode');
        }

        return $result;
    }

    /**
     * Convert $var to Symfony-colorized CLI output text.
     *
     * @param SystemConfigSetting $setting The found SystemConfigSetting.
     * @param mixed $var The thing to format: Config scalar values lead to $var being scalar;  Config array values lead to $var being an array of scalars; Config sections lead to $var being a mixed array of both scalar and array values.
     * @return string The formatted result.
     */
    private function toText(SystemConfigSetting $setting, $var): string
    {

        // Strip off the NO_SETTING_NAME_FOUND_PLACEHOLDER.
        $settingName = $setting->getName() === self::NO_SETTING_NAME_FOUND_PLACEHOLDER ? '' : $setting->getName();
        $sectionAndSettingName = implode('.', array_filter([$setting->getConfigSectionName(), $settingName]));

        $output = '';

        switch (true) {
            case is_array($var):
                $output .= $this->wrapInTag('info', ($settingName ? $sectionAndSettingName : "[{$sectionAndSettingName}]") . PHP_EOL);
                $output .= $this->wrapInTag('info', '--' . PHP_EOL);
                foreach ($var as $thisSettingName => &$val) {
                    if (is_array($val)) {
                        foreach ($val as &$arrayVal) {
                            $output .= $this->wrapInTag('info', "{$thisSettingName}[] = " . $this->wrapInTag('comment', $arrayVal)) . PHP_EOL;
                        }
                    } else {
                        $output .= $this->wrapInTag('info', $thisSettingName . ' = ' . $this->wrapInTag('comment', $val)) . PHP_EOL;
                    }
                }
                $output .= $this->wrapInTag('info', '--' . PHP_EOL);
                break;
            case is_scalar($var):
                $output .= $this->wrapInTag('info', $sectionAndSettingName . ' = ' . $this->wrapInTag('comment', $var));
                break;
            default:
                throw \InvalidArgumentException('Cannot output unknown type');
        }

        return $output;
    }
}
