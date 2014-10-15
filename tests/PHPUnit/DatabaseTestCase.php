<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * @deprecated since 2.8.0 use \Piwik\Tests\Framework\TestCase\IntegrationTestCase instead
 */
class DatabaseTestCase extends \Piwik\Tests\Framework\TestCase\IntegrationTestCase
{

    public static function setUpBeforeClass()
    {
        \Piwik\Log::debug('\DatabaseTestCase is deprecated since 2.8.0 extend \Piwik\Tests\Framework\TestCase\IntegrationTestCase instead');

        parent::setUpBeforeClass();
    }
}
