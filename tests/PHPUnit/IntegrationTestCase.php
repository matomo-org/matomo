<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * @deprecated since 2.8.0 extend \Piwik\Tests\Impl\SystemTestCase instead
 */
class IntegrationTestCase extends \Piwik\Tests\Impl\SystemTestCase
{

    public static function setUpBeforeClass()
    {
        \Piwik\Log::debug('\IntegrationTestCase is deprecated since 2.8.0 extend \Piwik\Tests\Impl\SystemTestCase instead');

        parent::setUpBeforeClass();
    }
}

IntegrationTestCase::$fixture = new \Piwik\Tests\Impl\Fixture();