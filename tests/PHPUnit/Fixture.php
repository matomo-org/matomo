<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Tests;
use Piwik\Log;

/**
 * @deprecated since 2.8.0 use \Piwik\Tests\Impl\Fixture instead
 */
class Fixture extends \Piwik\Tests\Impl\Fixture
{

    /** Adds data to Piwik. Creates sites, tracks visits, imports log files, etc. */
    public function setUp()
    {
        Log::warning('Piwik\Tests\Fixture is deprecated, use \Piwik\Tests\Impl\Fixture instead');

        parent::setUp();
    }
}

/**
 * @deprecated since 2.8.0 use \Piwik\Tests\Impl\OverrideLogin instead
 */
class OverrideLogin extends \Piwik\Tests\Impl\OverrideLogin
{
}