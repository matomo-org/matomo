<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CustomDimensions\tests\Integration\Dimension;

use Piwik\Piwik;
use Piwik\Plugins\CustomDimensions\Dimension\Extraction;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Tracker\ActionPageview;
use Piwik\Tracker\Request;

/**
 * @group CustomDimensions
 * @group ExtractionTest
 * @group Extraction
 * @group Dao
 * @group Plugins
 */
class ExtractionTest extends IntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        if (!Fixture::siteCreated(1)) {
            Fixture::createWebsite('2014-01-01 00:00:00');
        }
    }

    public function testNonCapturingGroup()
    {
        $extraction = $this->buildExtraction('url', '.com/(?:test)/.*camelCase=(.*)');

        $request = $this->buildRequest();
        $value   = $extraction->extract($request);

        $this->assertSame('fooBarBaz', $value);
    }

    public function testNonCapturingGroupWithinCaptureGroup()
    {
        $extraction = $this->buildExtraction('url', '.com/.*(?:camel=|camelCase=(.*))');

        $request = $this->buildRequest();
        $value   = $extraction->extract($request);

        $this->assertSame('fooBarBaz', $value);
    }

    public function testMultipleNonCapturingGroups()
    {
        $extraction = $this->buildExtraction('url', '.com/(?:test)/.*(?:camel=|camelCase=(.*))');

        $request = $this->buildRequest();
        $value   = $extraction->extract($request);

        $this->assertSame('fooBarBaz', $value);
    }

    public function testToArray()
    {
        $extraction = $this->buildExtraction('url', '.com/(.+)/index');
        $value = $extraction->toArray();

        $this->assertSame(array('dimension' => 'url', 'pattern' => '.com/(.+)/index'), $value);
    }

    public function testExtractUrlWithMatch()
    {
        $extraction = $this->buildExtraction('url', '.com/(.+)/index');

        $request = $this->buildRequest();
        $value   = $extraction->extract($request);

        $this->assertSame('test', $value);
    }

    public function testExtractUrlWithNoPattern()
    {
        $extraction = $this->buildExtraction('url', 'example');

        $request = $this->buildRequest();
        $value   = $extraction->extract($request);

        $this->assertNull($value);
    }

    public function testExtractUrlWithPatternButNoMatch()
    {
        $extraction = $this->buildExtraction('url', 'examplePiwik(.+)');

        $request = $this->buildRequest();
        $value   = $extraction->extract($request);

        $this->assertNull($value);
    }

    public function testActionNameMatch()
    {
        $extraction = $this->buildExtraction('action_name', 'My(.+)Title');

        $request = $this->buildRequest();
        $value   = $extraction->extract($request);

        $this->assertSame(' Test ', $value);
    }

    public function testExtractUrlparam()
    {
        $request = $this->buildRequest();

        $value = $this->buildExtraction('urlparam', 'module')->extract($request);
        $this->assertSame('CoreHome', $value);

        $value = $this->buildExtraction('urlparam', 'action')->extract($request);
        $this->assertSame('test', $value);

        $value = $this->buildExtraction('urlparam', 'notExist')->extract($request);
        $this->assertNull($value);
    }

    public function testExtractWithActionShouldReadValueFromActionNotFromPassedRequest()
    {
        $request = $this->buildRequest();
        $action = new ActionPageview($request);

        // we create a new empty request here to make sure it actually reads the value from $action and not from $request
        $request = new Request(array());
        $request->setMetadata('Actions', 'action', $action);

        $value = $this->buildExtraction('urlparam', 'module')->extract($request);
        $this->assertSame('CoreHome', $value);

        $value = $this->buildExtraction('action_name', 'My(.+)Title')->extract($request);
        $this->assertSame(' Test ', $value);
    }

    /**
     * @dataProvider getCaseSensitiveTestProvider
     */
    public function testExtractShouldBeCaseSensitiveByDefault($dimension, $pattern, $expectedExtracted)
    {
        $request = $this->buildRequest();

        // Title is "My Test Title"
        $value = $this->buildExtraction($dimension, $pattern)->extract($request);
        $this->assertSame($expectedExtracted, $value);
    }

    public function getCaseSensitiveTestProvider()
    {
        return array(
            array('action_name', 'My(.+)Title', ' Test '),
            array('action_name', 'my(.+)Title', null),
            array('action_name', 'My(.+)title', null),
            array('urlparam',    'camelCase',   'fooBarBaz'),
            array('urlparam',    'camelcase',   null),
            array('urlparam',    'Camelcase',   null),
        );
    }

    /**
     * @dataProvider getCaseInsensitiveTestProvider
     */
    public function testExtractWhenCaseInsensitiveIsEnabled($dimension, $pattern, $expectedExtracted)
    {
        $request = $this->buildRequest();

        $extraction = $this->buildExtraction($dimension, $pattern);
        $extraction->setCaseSensitive(false);
        // Title is "My Test Title"
        $value = $extraction->extract($request);
        $this->assertSame($expectedExtracted, $value);
    }

    public function getCaseInsensitiveTestProvider()
    {
        return array(
            array('action_name', 'My(.+)Title', ' Test '),
            array('action_name', 'my(.+)Title', ' Test '),
            array('action_name', 'My(.+)title', ' Test '),
            array('urlparam',    'camelCase',   'fooBarBaz'),
            array('urlparam',    'camelcase',   'fooBarBaz'),
            array('urlparam',    'Camelcase',   'fooBarBaz'),
        );
    }

    public function testExtractAnyRandomTrackingApiParameter()
    {
        $request = $this->buildRequest();

        $value = $this->buildExtraction('urlref', '/ref(.+)')->extract($request);
        $this->assertSame('errer', $value);
    }

    public function testExtractWhenOnlyPatternGiven()
    {
        $request = $this->buildRequest();

        $value = $this->buildExtraction('url', '(.+)')->extract($request);
        $this->assertSame('http://www.example.com/test/index.php?idsite=54&module=CoreHome&action=test&camelCase=fooBarBaz', $value);
    }

    public function testCheckShouldFailWhenInvalidDimensionGiven()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invald dimension \'anyInvalid\' used in an extraction. Available dimensions are: url, urlparam, action_name');

        $this->buildExtraction('anyInvalid', '/ref(.+)')->check();
    }

    public function testCheckShouldFailWhenInvalidRegGiven()
    {
        $check = '/foo(*)/';
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(Piwik::translate('General_ValidatorErrorNoValidRegex', array($check)));
        $this->buildExtraction('url', $check)->check();
    }

    /**
     * @dataProvider getInvalidPatterns
     */
    public function testCheckShouldFailWhenInvalidPatternGiven($pattern)
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('You need to group exactly one part of the regular expression inside round brackets, eg \'index_(.+).html\'');

        $this->buildExtraction('url', $pattern)->check();
    }

    public function testCheckShouldNotFailWhenValidCombinationsAreGiven()
    {
        $this->expectNotToPerformAssertions();
        $this->buildExtraction('urlparam', 'index')->check(); // does not have to contain brackets
        $this->buildExtraction('url', 'index_(.+).html')->check();
        $this->buildExtraction('action_name', 'index_(.+).html')->check();
        $this->buildExtraction('url', '')->check(); // empty value is allowed
        $this->buildExtraction('urlparam', 'index')->check(); // does not have to contain brackets
    }

    public function getInvalidPatterns()
    {
        return array(
            array('index.html'),
            array('index.(html'),
            array('index.)html'),
            array('index.)(html'),
            array('index.)(.+)html'),
            array('(?:index.(html)'),
            array('(?:index).html)'),
        );
    }

    private function buildRequest()
    {
        $url = 'http://www.example.com/test/index.php?idsite=54&module=CoreHome&action=test&camelCase=fooBarBaz';
        $referrer = 'http://www.example.com/referrer';
        $actionName = 'My Test Title';

        return new Request(array('idsite' => 1, 'url' => $url, 'action_name' => $actionName, 'urlref' => $referrer));
    }

    private function buildExtraction($dimension, $pattern)
    {
        return new Extraction($dimension, $pattern);
    }
}
