<?php

require_once dirname(__FILE__) . '/../../TestFramework/classes/AbstractTestEnv.php';

class PiwikTestEnv extends AbstractTestEnv
{
    public function ensureTestDatabaseConnection()
    {
    }
    
    public function setupDB($ignore_errors = false)
    {
    }

    public function setupCoreTables()
    {
    }

    public function setupDefaultData()
    {
    }

    public function teardownDB()
    {
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
