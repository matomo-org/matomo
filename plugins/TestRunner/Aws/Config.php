<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\TestRunner\Aws;

use \Piwik\Config as PiwikConfig;

class Config
{
    public function getRegion()
    {
        return trim($this->getConfigValue('aws_region'));
    }

    public function getAmi()
    {
        return trim($this->getConfigValue('aws_ami'));
    }

    public function getInstanceType()
    {
        return trim($this->getConfigValue('aws_instance_type'));
    }

    public function getKeyName()
    {
        return $this->getConfigValue('aws_keyname');
    }

    public function getPemFile()
    {
        return trim($this->getConfigValue('aws_pem_file'));
    }

    public function getAccessKey()
    {
        return trim($this->getConfigValue('aws_accesskey'));
    }

    public function getSecretKey()
    {
        return trim($this->getConfigValue('aws_secret'));
    }

    public function getSecurityGroups()
    {
        $groups = $this->getConfigValue('aws_securitygroups');

        if (empty($groups)) {
            $groups = array();
        }

        return (array) $groups;
    }

    public function validate()
    {
        $configKeysToValidate = array(
            'aws_accesskey',
            'aws_secret',
            'aws_region',
            'aws_ami',
            'aws_instance_type',
            'aws_pem_file',
            'aws_keyname',
            'aws_securitygroups',
        );

        foreach ($configKeysToValidate as $key) {
            if (!$this->getConfigValue($key)) {
                throw new \RuntimeException("[tests]$key is not configured in config/config.ini.php");
            }
        }

        $pemFile = $this->getPemFile();

        if (!file_exists($pemFile)) {
            throw new \RuntimeException('[tests]aws_pem_file the file does not exist or is not readable');
        }
    }

    private function getConfig()
    {
        return PiwikConfig::getInstance()->tests;
    }

    private function getConfigValue($key)
    {
        $config = $this->getConfig();

        return $config[$key];
    }
}
