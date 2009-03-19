<?php

// Advertiser Console specific config file
require_once dirname(__FILE__) . '/../TestFramework/classes/TestEnvFactory.php';
require_once dirname(__FILE__) . '/../TestFramework/classes/TestConfig.php';
require_once dirname(__FILE__) . '/classes/PiwikTestEnv.php';

define('PEAR_LIBRARY_PATH', dirname(__FILE__) . '/../pear/');
define('PROJECT_PATH', dirname(__FILE__) . '/../../');
define('SIMPLETEST_PATH', dirname(__FILE__) . '/../simpletest/');

TestEnvFactory::setTestEnv(new PiwikTestEnv());

TestConfig::getInstance()->addDirectory('tests');
TestConfig::getInstance()->addTestType('unit', 'core');
TestConfig::getInstance()->addLayer('unit', 'core', 'core unit tests', EnvType::NO_DB);
