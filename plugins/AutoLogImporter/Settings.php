<?php
/**
 * Copyright (C) Piwik PRO - All rights reserved.
 *
 * Using this code requires that you first get a license from Piwik PRO.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * @link http://piwik.pro
 */

namespace Piwik\Plugins\AutoLogImporter;

use Piwik\Common;
use Piwik\Settings\SystemSetting;

/**
 * Defines Settings for AutoLogImporter.
 *
 * Usage like this:
 * $settings = new Settings('AutoLogImporter');
 * $settings->autoRefresh->getValue();
 * $settings->metric->getValue();
 */
class Settings extends \Piwik\Plugin\Settings
{

    /** @var SystemSetting */
    public $enabled;

    /** @var SystemSetting */
    public $logFilesPath;

    /** @var SystemSetting */
    public $filePattern;

    protected function init()
    {
        $this->createEnabledSetting();
        $this->createLogFilesPathSetting();
        $this->createFilePatternSetting();
    }

    private function createEnabledSetting()
    {
        $this->enabled = new SystemSetting('enabled', 'Enabled');
        $this->enabled->type  = static::TYPE_BOOL;
        $this->enabled->uiControlType = static::CONTROL_CHECKBOX;
        $this->enabled->defaultValue  = false;

        $this->addSetting($this->enabled);
    }

    private function createLogFilesPathSetting()
    {
        $this->logFilesPath = new SystemSetting('logFilesPath', 'Path to log files');
        $this->logFilesPath->type  = static::TYPE_STRING;
        $this->logFilesPath->uiControlType = static::CONTROL_TEXT;

        $self = $this;
        $this->logFilesPath->validate = function ($value, $setting) use ($self) {
            if (empty($value) && !$self->enabled->getValue()) {
                // it is not enabled, an empty value is okay.
                return;
            }
            if (empty($value)) {
                throw new \Exception('A value must be specified');
            }
            if (!file_exists($value)) {
                throw new \Exception('This path does not exist');
            }
            if (!is_dir($value)) {
                throw new \Exception('Path is not a directory');
            }
            if (!is_readable($value)) {
                throw new \Exception('This directory is not readable');
            }
        };
        $this->logFilesPath->transform = function ($value) {
            if (!empty($value) && Common::stringEndsWith($value, '/')) {
                $value = substr($value, 0, -1);
            }

            return $value;
        };
        $this->logFilesPath->description = 'Defines the path to the log files.';

        $this->addSetting($this->logFilesPath);
    }

    private function createFilePatternSetting()
    {
        $this->filePattern = new SystemSetting('filePattern', 'File Pattern');
        $this->filePattern->type  = static::TYPE_STRING;
        $this->filePattern->uiControlType = static::CONTROL_TEXT;
        $this->filePattern->defaultValue = '*.log';
        $this->filePattern->description = 'If specified, only files matching this pattern will be imported.';
        $this->filePattern->transform = function ($value) {
            if (!empty($value)) {
                return trim($value);
            }

            return '*';
        };

        $this->addSetting($this->filePattern);
    }

}
