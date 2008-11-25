<?php

require_once dirname(__FILE__) . '/../../../application/config.php';
require_once dirname(__FILE__) . '/../../TestFramework/classes/AbstractTestEnv.php';
require_once dirname(__FILE__) . '/../../../library/doctrine/Doctrine.php';
require_once dirname(__FILE__) . '/../../../library/OX/Doctrine/cfg/doctrine-init.php';
require_once dirname(__FILE__) . '/../../../library/OX/Common/zend-init.php';
require_once dirname(__FILE__) . '/../../../library/OX/Doctrine/DB.php';
require_once dirname(__FILE__) . '/../../../library/OX/Common/Config.php';
require_once dirname(__FILE__) . '/../../../application/model-utils/SampleDataGenerator.php';

class PiwikTestEnv extends AbstractTestEnv
{
    public function ensureTestDatabaseConnection()
    {
        $connection = Doctrine_Manager::connection();
        $dsn = OX_Doctrine_DB::getDsn(OX_DSN_STRING, 
            OX_Common_Config::instance('database', 
                $this->getConfigFilename())->toArray());
        $newConnection = Doctrine_Manager::connection($dsn);
        if (0 != strcmp($newConnection->getOption('dsn'), 
          $connection->getOption('dsn'))) {
            
          Doctrine_Manager::getInstance()->closeConnection(
              $connection);
          $connection = Doctrine_Manager::getInstance(
              )->openConnection($dsn, null, TRUE);
          global $conn;
          $conn = $connection; 
        }
        Doctrine_Manager::getInstance()->closeConnection(
            $newConnection);
    }
    
    public function setupDB($ignore_errors = false)
    {
        $this->ensureTestDatabaseConnection();
        $connection = Doctrine_Manager::connection();
        
        if (isset($connection)) {
            $result = $connection->createDatabase();
            
            if (!$ignore_errors) {
                if (!is_string($result)) {
                	throw new TestFrameworkException(
                	   $result->errorMessage());
                }
            }
        }
    }

    public function setupCoreTables()
    {
        Doctrine::createTablesFromModels();
    }

    public function setupDefaultData()
    {
        SampleDataGenerator::generateData(false);
    }

    public function teardownDB()
    {
        $this->ensureTestDatabaseConnection();
        $connection = Doctrine_Manager::connection();
        $connection->dropDatabase();
    }

    public function getConfigFilename()
    {
    	return dirname(__FILE__) . '/../../../config/config.ini.php';
    	
    }
    
    public function getApplicationVersion()
    {
    	return '0.2';
    }
    
    public function getApplicationName()
    {
    	return 'Piwik';
    }
}
