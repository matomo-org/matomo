<?php
namespace Piwik\Tests\Framework\Mock\Tracker;

use Piwik\Tracker\Request;

class RequestAuthenticated extends Request
{
    protected $isAuthenticated = true;

}