<?php
namespace Piwik\Plugins\AdvancedAPI;

class API extends \Piwik\Plugin\API
{
    public function sayHello()
    {
        return 'Hello World!';
    }
}