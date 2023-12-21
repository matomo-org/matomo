<?php

namespace Piwik\Tests\Framework\Mock;

use Piwik\Config;

class FakeConfig extends Config
{
    private $configValues = array();

    public function __construct($configValues = array())
    {
        $this->configValues = $configValues;
    }

    public function &__get($name)
    {
        if (isset($this->configValues[$name])) {
            return $this->configValues[$name];
        }
    }

    public function __set($name, $value)
    {
         $this->configValues[$name] = $value;
    }
}
