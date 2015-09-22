<?php
/**
 * Copyright (C) Piwik PRO - All rights reserved.
 *
 * Using this code requires that you first get a license from Piwik PRO.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * @link http://piwik.pro
 */

namespace Piwik\Plugins\FileSynchronizer;

use Piwik\Common;
use Piwik\Settings\Setting;
use Piwik\Settings\SystemSetting;

/**
 * Defines Settings for FileSynchronizer.
 *
 * Usage like this:
 * $settings = new Settings('FileSynchronizer');
 * $settings->enabled->getValue();
 * $settings->sourceDirectory->getValue();
 */
class Settings extends \Piwik\Plugin\Settings
{
    /** @var SystemSetting */
    public $enabled;

    /** @var SystemSetting */
    public $sourceDirectory;

    /** @var SystemSetting */
    public $filePattern;

    /** @var SystemSetting */
    public $targetDirectory;

    /** @var SystemSetting */
    public $targetFilenameTemplate;

    /** @var SystemSetting */
    public $copyCommandTemplate;

    protected function init()
    {
        $this->createEnabledSetting();
        $this->createSourceDirectorySetting();
        $this->createFilePatternSetting();
        $this->createTargetDirectorySetting();
        $this->createTargetFilenameSetting();
        $this->createCopyTemplateSetting();
    }

    private function createEnabledSetting()
    {
        $this->enabled = new SystemSetting('enabled', 'Enabled');
        $this->enabled->type  = static::TYPE_BOOL;
        $this->enabled->uiControlType = static::CONTROL_CHECKBOX;
        $this->enabled->defaultValue  = false;

        $this->addSetting($this->enabled);
    }

    private function createSourceDirectorySetting()
    {
        $this->sourceDirectory = new SystemSetting('sourceDirectory', 'Source directory');
        $this->configureDirectorySetting($this->sourceDirectory, $checkDir = true);
        $this->sourceDirectory->description = 'Defines the source directory that contains the files that need to be synced.';

        $this->addSetting($this->sourceDirectory);
    }

    private function createFilePatternSetting()
    {
        $this->filePattern = new SystemSetting('filePattern', 'File Pattern');
        $this->filePattern->type  = static::TYPE_STRING;
        $this->filePattern->uiControlType = static::CONTROL_TEXT;
        $this->filePattern->defaultValue = '*';
        $this->filePattern->description = 'If specified, only files matching this pattern will be synced.';
        $this->filePattern->inlineHelp = 'Any shell wildcards can be specified, for example "*.log".';
        $this->filePattern->transform = function ($value) {
            if (!empty($value)) {
                return trim($value);
            }

            return '*';
        };

        $this->addSetting($this->filePattern);
    }

    private function createTargetDirectorySetting()
    {
        $this->targetDirectory = new SystemSetting('targetDirectory', 'Target directory');
        $this->configureDirectorySetting($this->targetDirectory, $checkDir = false);
        // we do not check dir as it might be on a remote computer
        $this->targetDirectory->description = 'Defines the target directory the files should be copied to.';

        $this->addSetting($this->targetDirectory);
    }

    private function createTargetFilenameSetting()
    {
        $this->targetFilenameTemplate = new SystemSetting('targetFilenameTemplate', 'Target filename template');
        $this->targetFilenameTemplate->type = static::TYPE_STRING;
        $this->targetFilenameTemplate->uiControlType = static::CONTROL_TEXT;
        $this->targetFilenameTemplate->defaultValue = '$basename';
        $this->targetFilenameTemplate->description = 'Allows to modify the filename. "$basename" or "$filename" must be specified, "$extension" can be specified optionally.';
        $this->targetFilenameTemplate->inlineHelp = '"$basename" will be replaced with the filename including extension (eg "apache.log"), "$filename" will be replaced by the filename excluding extension (eg "apache") and "$extension" will be replaced by the file extension (eg "log"). This allows to modify the filename in the target directory for example like this: "$filename_idsite_1.$extension"';

        $self = $this;
        $this->targetFilenameTemplate->validate = function ($value, $setting) use ($self) {
            if (empty($value) && !$self->enabled->getValue()) {
                // it is not enabled, an empty value is okay.
                return;
            }

            if (empty($value)) {
                throw new \Exception('A value must be specified');
            }

            if (false === strpos($value, '$filename') && false === strpos($value, '$basename')) {
                throw new \Exception('$filename or $basename must be specified');
            }
        };
        $this->targetFilenameTemplate->transform = function ($value) {
            if (!empty($value)) {
                return trim($value);
            }

            return $value;
        };

        $this->addSetting($this->targetFilenameTemplate);
    }

    private function configureDirectorySetting(Setting $setting, $checkDir)
    {
        $setting->type  = static::TYPE_STRING;
        $setting->uiControlType = static::CONTROL_TEXT;

        $self = $this;
        $setting->validate = function ($value, $setting) use ($self, $checkDir) {
            if (empty($value) && !$self->enabled->getValue()) {
                // it is not enabled, an empty value is okay.
                return;
            }
            if (empty($value)) {
                throw new \Exception('A value must be specified');
            }
            if ($checkDir && !file_exists($value)) {
                throw new \Exception('This path does not exist');
            }
            if ($checkDir && !is_dir($value)) {
                throw new \Exception('Path is not a directory');
            }
            if ($checkDir && !is_readable($value)) {
                throw new \Exception('This directory is not readable');
            }
        };
        $setting->transform = function ($value) {
            if (!empty($value) && Common::stringEndsWith($value, '/')) {
                $value = substr($value, 0, -1);
            }

            return $value;
        };
    }

    private function createCopyTemplateSetting()
    {
        $this->copyCommandTemplate = new SystemSetting('copyCommandTemplate', 'Copy Command Template');
        $this->copyCommandTemplate->type = static::TYPE_STRING;
        $this->copyCommandTemplate->uiControlType = static::CONTROL_TEXT;
        $this->copyCommandTemplate->description = 'The shell command to sync files. "$source" and "$target" must be specified.';
        $this->copyCommandTemplate->inlineHelp = '"$source" will be replaced by the path of the source file, "$target" by the path to the target file.';
        $this->copyCommandTemplate->defaultValue = 'cp $source $target';

        $self = $this;
        $this->copyCommandTemplate->validate = function ($value, $setting) use ($self) {
            if (empty($value) && !$self->enabled->getValue()) {
                // it is not enabled, an empty value is okay.
                return;
            }

            if (empty($value)) {
                throw new \Exception('A value must be specified');
            }
            if (false === strpos($value, '$source')) {
                throw new \Exception('$source is not specified');
            }
            if (false === strpos($value, '$target')) {
                throw new \Exception('$target is not specified');
            }
        };
        $this->copyCommandTemplate->transform = function ($value) {
            if (!empty($value)) {
                return trim($value);
            }

            return $value;
        };

        $this->addSetting($this->copyCommandTemplate);
    }
}
