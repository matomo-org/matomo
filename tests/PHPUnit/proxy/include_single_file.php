<?php

use Piwik\Application\Environment;
use Piwik\Tests\Framework\TestingEnvironmentManipulator;
use Piwik\Tests\Framework\TestingEnvironmentVariables;

require_once __DIR__ . '/../../../core/Application/Environment.php';
require_once __DIR__ . '/../../../core/Application/Kernel/PluginList.php';
require_once __DIR__ . '/../../../core/Application/EnvironmentManipulator.php';
require_once __DIR__ . '/../Framework/TestingEnvironmentVariables.php';
require_once __DIR__ . '/../Framework/TestingEnvironmentManipulator.php';

Environment::setGlobalEnvironmentManipulator(new TestingEnvironmentManipulator(new TestingEnvironmentVariables()));

$scriptToInclude = $argv[1];

include $scriptToInclude;
