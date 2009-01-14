<?php

// Advertiser Console specific config file
require_once dirname(__FILE__) . '/../TestFramework/classes/TestEnvFactory.php';
require_once dirname(__FILE__) . '/../TestFramework/classes/TestConfig.php';
require_once dirname(__FILE__) . '/classes/PiwikTestEnv.php';

define('PEAR_LIBRARY_PATH', dirname(__FILE__) . '/../pear/');
define('PROJECT_PATH', dirname(__FILE__) . '/../../');
define('SIMPLETEST_PATH', dirname(__FILE__) . '/../simpletest/');

TestEnvFactory::setTestEnv(new PiwikTestEnv());

TestConfig::getInstance()->addDirectory('core');
TestConfig::getInstance()->addDirectory('plugins');

TestConfig::getInstance()->addTestType('unit', 'tests/unit');
TestConfig::getInstance()->addLayer('unit', 'cor', 'Core Classes', EnvType::NO_DB);
TestConfig::getInstance()->addLayer('unit', 'dal', 'Data Abstraction Layer (DB)', EnvType::DB_WITH_TABLES);
TestConfig::getInstance()->addLayer('unit', 'extdb', 'Extensions to the system (DB)', EnvType::DB_WITH_TABLES);
TestConfig::getInstance()->addLayer('unit', 'db', 'DB utils', EnvType::DB_WITH_TABLES);
TestConfig::getInstance()->addLayer('unit', 'util', 'Commonly used utilities', EnvType::NO_DB);
TestConfig::getInstance()->addLayer('unit', 'mt', 'Maintenance', EnvType::DB_WITH_TABLES);

TestConfig::getInstance()->addTestType('integration', 'tests/integration');
TestConfig::getInstance()->addLayer('integration', 'cor', 'Integration tests', EnvType::NO_DB);
