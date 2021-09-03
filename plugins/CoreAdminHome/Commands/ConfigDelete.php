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
use Symfony\Component\Yaml\Yaml;

class ConfigGet extends ConsoleCommand
{
    /*
     * SystemConfigSetting throws an error if the setting name is empty, so use a fake one that is unlikely to actually exist, which we will check for later.
     */

    private const NO_SETTING_NAME_FOUND_PLACEHOLDER = 'ConfigGet_FAKE_SETTING_NAME';
    public const OUTPUT_FORMATS = ['json', 'yaml', 'text'];
    public const OUTPUT_FORMAT_DEFAULT = 'json';

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
- [Section] is the name of the section or array value, e.g. database or PluginsInstalled.
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
#Return all settings in this array value; square brackets are optional.
$ ./console %command.name% 'PluginsInstalled'
$ ./console %command.name% 'PluginsInstalled[]'
#Return only this setting.
$ ./console %command.name% 'database.username'

NOTES:
- Section and key names are case-sensitive; so --section=Database fails but --section=database works.  Look in global.ini.php for the proper case.
- If no matching setting key is found, the string \"Nothing found\" shows.
- Else the output will be shown JSON-encoded.  You can use something like https://stedolan.github.io/jq to parse it.
");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        //Optionally could set this at runtime with: $output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE.
        $debug = false;

        // Gather options, then discard ones with an empty value so we do not need to check for empty later.
        $options = array_filter([
            'section' => $input->getOption('section'),
            'key' => $input->getOption('key'),
            'format' => $input->getOption('format'),
        ]);
        $debug && fwrite(STDERR, PHP_EOL . self::wrapInTag('info', __FUNCTION__ . '::Started with $options=' . (empty($options) ? '' : serialize($options))));

        // If none specified, set default format.
        if ( ! isset($options['format']) || empty($format = $options['format']) || ! in_array($format, self::OUTPUT_FORMATS, true)) {
            $format = self::OUTPUT_FORMAT_DEFAULT;
        }
        // Since it is config and not input data, remove format from the options so we can just work with the section and format.
        unset($options['format']);

        $argument = trim($input->getArgument('argument'));
        $debug && fwrite(STDERR, PHP_EOL . self::wrapInTag('info', __FUNCTION__ . '::Started with $argument=' . (empty($argument) ? '' : serialize($argument))));

        // Sanity check inputs.
        switch (true) {
            case empty($argument) && empty($options):
                throw new \InvalidArgumentException('You must set either an argument or set options --section and optional --key');
            case ! empty($argument) && ! empty($options):
                throw new \InvalidArgumentException('You cannot set both an argument (' . serialize($argument) . ') and options (' . serialize($argument) . ')');
            case empty($argument) && ( ! isset($options['section']) || empty($options['section'])):
                throw new \InvalidArgumentException('When using options, the --section value must be set');
            case ! empty($argument):
                $systemConfigSettingStr = $argument;
                break;
            case ! empty($options):
                $systemConfigSettingStr = implode('.', $options);
                break;
            default:
                // We should not get here, but just in case.
                throw new Exception('Some unexpected error occurred parsing input values');
        }
        $debug && fwrite(STDERR, PHP_EOL . self::wrapInTag('info', __FUNCTION__ . "::Found \$systemConfigSettingStr=$systemConfigSettingStr"));

        // Parse the $systemConfigSettingStr into a SystemConfigSetting object.
        $systemConfigSetting = self::make($systemConfigSettingStr);
        $debug && fwrite(STDERR, PHP_EOL . self::wrapInTag('info', __FUNCTION__ . '::Parsed \$systemConfigSetting=' . serialize($systemConfigSetting)));

        $config = Config::getInstance();

        $debug && fwrite(STDERR, PHP_EOL . self::wrapInTag('info', __FUNCTION__ . "::About to get config for \$systemConfigSettingStr={$systemConfigSettingStr}"));
        $result = $this->getConfigValue($config, $systemConfigSetting, $output);
        $debug && fwrite(STDERR, PHP_EOL . self::wrapInTag('comment', __FUNCTION__ . '::Got $result=' . serialize($result)));

        if (empty($result)) {
            $output->writeln(self::wrapInTag('comment', 'Nothing found.'));
        } else {
            $output->writeln($this->formatVariableForOutput($systemConfigSetting, $result, $format));
        }

        //Many matomo script output Done when they're done.  IMO it's not needed: $output->writeln(self::wrapInTag('info', 'Done.'));
        $debug && fwrite(STDERR, PHP_EOL . self::wrapInTag('info', __FUNCTION__ . '::Done'));
    }

    /**
     * Get a config section or section.value.
     *
     * @param Config $config A Matomo Config instance.
     * @param SystemConfigSetting $systemConfigSetting
     * @param OutputInterface $output Used only for debug output.
     * @return Mixed The config section or value.
     */
    private function getConfigValue(Config $config, SystemConfigSetting $systemConfigSetting, OutputInterface $output)
    {
        $debug = true;
        $debug && fwrite(STDERR, PHP_EOL . self::wrapInTag('info', __FUNCTION__ . '::Started with $systemConfigSetting=' . serialize($systemConfigSetting)));

        $configSectionName = $systemConfigSetting->getConfigSectionName();
        if (empty($configSectionName)) {
            throw new \InvalidArgumentException('A section name must be specified');
        }
        if (empty($systemConfigSection = $config->__get($configSectionName))) {
            $debug && fwrite(STDERR, PHP_EOL . self::wrapInTag('error', __FUNCTION__ . "::No config section matches \$configSectionName={$configSectionName}"));
            return null;
        }

        // Convert array to object since it is slightly cleaner/easier to work with.
        $systemConfigSection = (object) $systemConfigSection;
        $debug && fwrite(STDERR, PHP_EOL . self::wrapInTag('info', __FUNCTION__ . '::Got $systemConfigSection=' . print_r($systemConfigSection, true)));

        // Look for the specific setting.
        $systemConfigSettingName = $systemConfigSetting->getName();
        $debug && fwrite(STDERR, PHP_EOL . self::wrapInTag('info', __FUNCTION__ . "::Looking for \$systemConfigSettingName={$systemConfigSettingName}"));

        // Return the whole setting section if requested.
        // The name=FAKE_SETTING_NAME is a placeholder for when no setting is specified.
        if (empty($systemConfigSettingName) || $systemConfigSettingName === self::NO_SETTING_NAME_FOUND_PLACEHOLDER) {
            $debug && fwrite(STDERR, PHP_EOL . self::wrapInTag('info', __FUNCTION__ . "::No specific setting was requested, so return the whole section"));
            $systemConfigSectionContents = $systemConfigSection;
            $debug && fwrite(STDERR, PHP_EOL . self::wrapInTag('info', __FUNCTION__ . '::About to return $systemConfigSectionContents=' . serialize($systemConfigSectionContents)));
            return (array) $systemConfigSectionContents;
        }

        $debug && fwrite(STDERR, PHP_EOL . self::wrapInTag('info', __FUNCTION__ . "::About to look for config setting with \$systemConfigSettingName={$systemConfigSettingName}"));

        switch (true) {
            case ! isset($systemConfigSection->$systemConfigSettingName):
                $debug && fwrite(STDERR, PHP_EOL . self::wrapInTag('error', __FUNCTION__ . "::No config setting matches \$systemConfigSettingName={$systemConfigSettingName}"));
                $systemConfigSettingValue = null;
                break;
            case is_array($systemConfigSettingValue = $systemConfigSection->$systemConfigSettingName):
                $debug && fwrite(STDERR, PHP_EOL . self::wrapInTag('error', __FUNCTION__ . "::The config setting is an array value"));
                break;
            default:
                $debug && fwrite(STDERR, PHP_EOL . self::wrapInTag('error', __FUNCTION__ . "::The config setting is a scalar value"));
                $systemConfigSettingValue = $systemConfigSetting->getValue();
        }

        $debug && fwrite(STDERR, PHP_EOL . self::wrapInTag('info', __FUNCTION__ . '::About to return $systemConfigSettingValue=' . serialize($systemConfigSettingValue)));
        return $systemConfigSettingValue;
    }

    /**
     * Build a SystemConfigSetting object from a string like:
     * `SectionName.setting_name`
     * or
     * `SectionName.setting_name[]`
     *
     * @param string $systemConfigSettingStr Config setting string to parse.
     * @return SystemConfigSetting A SystemConfigSetting object.
     */
    public static function make(string $systemConfigSettingStr): SystemConfigSetting
    {
        $debug = false;
        $debug && fwrite(STDERR, PHP_EOL . __FUNCTION__ . "::Started with \$systemConfigSettingStr={$systemConfigSettingStr}");

        $matches = [];
        if ( ! preg_match('/^([a-zA-Z0-9_]+)(?:\.([a-zA-Z0-9_]+))?(\[\])?/', $systemConfigSettingStr, $matches) || empty($matches[1])) {
            throw new \InvalidArgumentException("Invalid input string='{$systemConfigSettingStr}': expected section.name or section.name[]");
        }

        $debug && fwrite(STDERR, PHP_EOL . __FUNCTION__ . '::Got regex $matches=' . serialize($matches));

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
     * @param SystemConfigSetting $systemConfigSetting The found SystemConfigSetting.
     * @param mixed $var The config setting value -- either scalar or an array of settings.
     * @param string $format The output format: One of self::OUTPUT_FORMAT_DEFAULT.
     * @return string The formatted output.
     */
    private function formatVariableForOutput(SystemConfigSetting $systemConfigSetting, $var, string $format = self::OUTPUT_FORMAT_DEFAULT): string
    {
        $debug = true;
        $debug && fwrite(STDERR, PHP_EOL . __FUNCTION__ . "::Started with \$format={$format}; \$var=" . serialize($var));

        switch ($format) {
            case 'json':
                return $this->toJson($var);
            case 'yaml':
                return $this->toYaml($var);
            case 'text':
                return $this->toText($systemConfigSetting, $var);
            default:
                throw new \InvalidArgumentException('Unsupported output format');
        }
    }

    /**
     * Convert $var to a JSON string.
     * Throws an error on invalid types (a PHP resource or object).
     *
     * @param mixed $var The variable to convert.
     * @return string The Yaml-formatted string.
     */
    private function toYaml($var): string
    {
        $debug = false;
        $debug && fwrite(STDERR, PHP_EOL . __FUNCTION__ . "::Started with \$var=" . serialize($var));

        return trim(Yaml::dump($var, 2, 2, true));
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
            throw new Exception('Failed to json_encode');
        }

        return $result;
    }

    /**
     * Convert $var to Symfony-colorized CLI output text.
     *
     * @param SystemConfigSetting $systemConfigSetting The found SystemConfigSetting.
     * @param mixed $var The variable to format.
     * @return string The formatted result.
     */
    private function toText(SystemConfigSetting $systemConfigSetting, $var): string
    {
        $debug = false;
        $output = '';

        $debug && fwrite(STDERR, PHP_EOL . __FUNCTION__ . '::Started with $var=' . serialize($var) . ' (' . getType($var) . ')');

        switch (true) {
            case is_array($var):
                $debug && fwrite(STDERR, PHP_EOL . __FUNCTION__ . '::Found var is array');
                foreach ($var as $key => &$val) {
                    $debug && fwrite(STDERR, PHP_EOL . __FUNCTION__ . "::Looking at key={$key}; val=" . serialize($val));
                    $output .= $this->wrapInTag('info', "{$key}: ");
                    $output .= $this->wrapInTag('info', $this->wrapInTag('comment', $val)) . PHP_EOL;
                }
                break;
            case is_scalar($output):
                $debug && fwrite(STDERR, PHP_EOL . __FUNCTION__ . '::Found var is scalar');
                $output .= $this->wrapInTag('info', $systemConfigSetting->getName() . ': ' . $this->wrapInTag('comment', $var));
                break;
            default:
                throw \InvalidArgumentException('Cannot output unknown type');
        }

        return $output;
    }
}
