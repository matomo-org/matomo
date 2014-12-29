<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\Period;

use Piwik\Container\StaticContainer;
use Piwik\Translate;
use Piwik\Translation\Translator;

abstract class BasePeriodTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        parent::setUp();

        Translate::loadCoreTranslation();
        /** @var Translator $translator */
        $translator = StaticContainer::getContainer()->get('Piwik\Translation\Translator');
        $translator->addDirectory(PIWIK_INCLUDE_PATH . '/plugins/CoreHome/lang');
    }

    public function tearDown()
    {
        parent::tearDown();

        Translate::reset();
    }
}