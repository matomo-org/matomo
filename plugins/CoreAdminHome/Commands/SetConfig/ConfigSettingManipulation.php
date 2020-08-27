<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreAdminHome\Commands\SetConfig;

use Piwik\Config;

/**
 * Representation of a INI config manipulation operation. Only supports two types
 * of manipulations: appending to a config array and assigning a config value.
 */
class ConfigSettingManipulation
{
    /**
     * @var string
     */
    private $sectionName;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $value;

    /**
     * @var bool
     */
    private $isArrayAppend;

    /**
     * @param string $sectionName
     * @param string $name
     * @param string $value
     * @param bool $isArrayAppend
     */
    public function __construct($sectionName, $name, $value, $isArrayAppend = false)
    {
        $this->sectionName = $sectionName;
        $this->name = $name;
        $this->value = $value;
        $this->isArrayAppend = $isArrayAppend;
    }

    /**
     * Performs the INI config manipulation.
     *
     * @param Config $config
     * @throws \Exception if trying to append to a non-array setting value or if trying to set an
     *                    array value to a non-array setting
     */
    public function manipulate(Config $config)
    {
        if ($this->isArrayAppend) {
            $this->appendToArraySetting($config);
        } else {
            $this->setSingleConfigValue($config);
        }
    }

    private function setSingleConfigValue(Config $config)
    {
        $sectionName = $this->sectionName;
        $section = $config->$sectionName;

        if (isset($section[$this->name])
            && is_array($section[$this->name])
            && !is_array($this->value)
        ) {
            throw new \Exception("Trying to set non-array value to array setting " . $this->getSettingString() . ".");
        }

        $section[$this->name] = $this->value;
        $config->$sectionName = $section;
    }

    private function appendToArraySetting(Config $config)
    {
        $sectionName = $this->sectionName;
        $section = $config->$sectionName;

        if (isset($section[$this->name])
            && !is_array($section[$this->name])
        ) {
            throw new \Exception("Trying to append to non-array setting value " . $this->getSettingString() . ".");
        }

        $section[$this->name][] = $this->value;
        $config->$sectionName = $section;
    }

    /**
     * Creates a ConfigSettingManipulation instance from a string like:
     *
     * `SectionName.setting_name=value`
     *
     * or
     *
     * `SectionName.setting_name[]=value`
     *
     * The value must be JSON so `="string"` will work but `=string` will not.
     *
     * @param string $assignment
     * @return self
     */
    public static function make($assignment)
    {
        if (!preg_match('/^([a-zA-Z0-9_]+)\.([a-zA-Z0-9_]+)(\[\])?=(.*)/', $assignment, $matches)) {
            throw new \InvalidArgumentException("Invalid assignment string '$assignment': expected section.name=value or section.name[]=value");
        }

        $section = $matches[1];
        $name = $matches[2];
        $isAppend = !empty($matches[3]);

        $value = json_decode($matches[4], $isAssoc = true);
        if ($value === null) {
            throw new \InvalidArgumentException("Invalid assignment string '$assignment': could not parse value as JSON");
        }

        return new self($section, $name, $value, $isAppend);
    }

    private function getSettingString()
    {
        return "[{$this->sectionName}] {$this->name}";
    }

    /**
     * @return string
     */
    public function getSectionName()
    {
        return $this->sectionName;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return boolean
     */
    public function isArrayAppend()
    {
        return $this->isArrayAppend;
    }

    /**
     * @return string
     */
    public function getValueString()
    {
        return json_encode($this->value);
    }
}