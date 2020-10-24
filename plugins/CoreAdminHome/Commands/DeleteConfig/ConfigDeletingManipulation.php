<?php declare(strict_types=1);

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreAdminHome\Commands\DeleteConfig;

use \RuntimeException;
use \InvalidArgumentException;
use Piwik\Config;

/**
 * Representation of a INI config manipulation operation. Only supports three types
 * of manipulations: removing a config array, remove a config or remove a config array position.
 */
class ConfigDeletingManipulation
{
    /**
     * @var string
     */
    private $sectionName;

    /**
     * @var string|null
     */
    private $name;

    /**
     * @var bool
     */
    private $isSectionDeletion;

    /**
     * @var int|null
     */
    private $settingPositionToDelete;

    /**
     * @param string      $sectionName
     * @param string|null $name
     * @param bool        $isSectionDeletion
     * @param int|null    $settingPositionToDelete
     */
    public function __construct(string $sectionName, ?string $name, bool $isSectionDeletion = false, ?int $settingPositionToDelete = null)
    {
        $this->sectionName = $sectionName;
        $this->name = $name;
        $this->isSectionDeletion = $isSectionDeletion;
        $this->settingPositionToDelete = $settingPositionToDelete;
    }

    /**
     * Performs the INI config manipulation.
     *
     * @param Config $config
     * @throws RuntimeException If trying to delete not existing section, config or config array position
     */
    public function manipulate(Config $config): void
    {
        if ($this->isSectionDeletion) {
            $this->deleteConfigSection($config);
        } elseif ($this->settingPositionToDelete) {
            $this->deleteConfigSettingPosition($config);
        } else {
            $this->deleteConfigSetting($config);
        }
    }

    /**
     * @param Config $config
     *
     * @throws RuntimeException When trying to delete not existing section in config
     */
    private function deleteConfigSection(Config $config): void
    {
        $sectionName = $this->sectionName;
        $section = $config->$sectionName;

        if (empty($section)) {
            throw new RuntimeException("Trying to delete not existing config section ".$this->getSettingString().".");
        }

        $config->$sectionName = null;
    }

    /**
     * @param Config $config
     *
     * @throws RuntimeException When trying to delete not existing config or not existing config array position in config
     */
    private function deleteConfigSettingPosition(Config $config): void
    {
        $sectionName = $this->sectionName;
        $section = $config->$sectionName;

        if (!isset($section[$this->name]) || !is_array($section[$this->name])) {
            throw new RuntimeException("Trying to delete not existing config in array setting ".$this->getSettingString().".");
        }

        if (!array_key_exists($this->settingPositionToDelete, $section[$this->name])) {
            throw new RuntimeException("Trying to delete not existing position in array setting ".$this->getSettingString().".");
        }

        unset($section[$this->name][$this->settingPositionToDelete]);

        $config->$sectionName = $section;
    }

    /**
     * @param Config $config
     *
     * @throws RuntimeException When trying to delete not existing config or not existing config array position in config
     */
    private function deleteConfigSetting(Config $config): void
    {
        $sectionName = $this->sectionName;
        $section = $config->$sectionName;

        if (!isset($section[$this->name])) {
            throw new RuntimeException("Trying to delete not existing config in array setting ".$this->getSettingString().".");
        }

        unset($section[$this->name]);

        $config->$sectionName = $section;
    }

    /**
     * Creates a ConfigSettingManipulation instance from a string like:
     *
     * `sectionName`
     *
     * or
     *
     * `sectionName.setting_name[]`
     *
     * or
     *
     * `sectionName.setting_name[position]`
     *
     * The position must be numeric from 0 to sectionName.setting_name.length-1
     *
     * @param string $assignment
     *
     * @return self
     */
    public static function make(string $assignment): self
    {
        if (!preg_match("/^(?'section'[\w]+)\.?(?'setting_key'[\w]+)?(?:\[(?'setting_key_position'[a-zA-Z0-9_-]+)\])?/", $assignment, $matches)) {
            throw new InvalidArgumentException("Invalid assignment string '$assignment': expected section, section.config_setting_key or section.config_setting_key[position]");
        }

        $section = $matches['section'];
        $setting_key = $matches['setting_key'] ?? null;
        $settingPositionToDelete = isset($matches['setting_key_position']) ? self::getSettingPositionToDeleteArgument($matches['setting_key_position']) : null;
        $isSectionDeletion = empty($matches['setting_key']);

        return new self($section, $setting_key, $isSectionDeletion, $settingPositionToDelete);
    }

    /**
     * @param string $position
     *
     * @return int|null
     */
    private static function getSettingPositionToDeleteArgument(string $position):?int {
        if (!is_numeric($position)) {
            throw new InvalidArgumentException("Setting positions can only be numeric");
        }

        $position = (int)$position;

        if (0 > $position) {
            throw new InvalidArgumentException("Setting positions can only go from 0 to section.config_setting_key.length-1");
        }

        return $position;
    }

    /**
     * @return string
     */
    private function getSettingString(): string
    {
        $string = "[{$this->sectionName}] {$this->name}";

        if (!is_null($this->settingPositionToDelete)) {
            $string.= "[{$this->settingPositionToDelete}]";
        }

        return $string;
    }

    /**
     * @return string
     */
    public function getSectionName(): string
    {
        return $this->sectionName;
    }

    /**
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @return boolean
     */
    public function isSectionDeletion(): bool
    {
        return $this->isSectionDeletion;
    }

    /**
     * @return int|null
     */
    public function getSettingPositionToDelete():?int
    {
        return $this->settingPositionToDelete;
    }
}