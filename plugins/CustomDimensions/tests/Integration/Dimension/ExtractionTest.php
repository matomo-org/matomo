<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
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

    public function test_non_capturing_group()
    {
        $extraction = $this->buildExtraction('url', '.com/(?:test)/.*camelCase=(.*)');

        $request = $this->buildRequest();
        $value   = $extraction->extract($request);

        $this->assertSame('fooBarBaz', $value);
    }

    public function test_non_capturing_group_within_capture_group()
    {
        $extraction = $this->buildExtraction('url', '.com/.*(?:camel=|camelCase=(.*))');

        $request = $this->buildRequest();
        $value   = $extraction->extract($request);

        $this->assertSame('fooBarBaz', $value);
    }

    public function test_multiple_non_capturing_groups()
    {
        $extraction = $this->buildExtraction('url', '.com/(?:test)/.*(?:camel=|camelCase=(.*))');

        $request = $this->buildRequest();
        $value   = $extraction->extract($request);

        $this->assertSame('fooBarBaz', $value);
    }

    public function test_toArray()
    {
        $extraction = $this->buildExtraction('url', '.com/(.+)/index');
        $value = $extraction->toArray();

        $this->assertSame(array('dimension' => 'url', 'pattern' => '.com/(.+)/index'), $value);
    }

    public function test_extract_url_withMatch()
    {
        $extraction = $this->buildExtraction('url', '.com/(.+)/index');

        $request = $this->buildRequest();
        $value   = $extraction->extract($request);

        $this->assertSame('test', $value);
    }

    public function test_extract_url_withNoPattern()
    {
        $extraction = $this->buildExtraction('url', 'example');

        $request = $this->buildRequest();
        $value   = $extraction->extract($request);

        $this->assertNull($value);
    }

    public function test_extract_url_withPatternButNoMatch()
    {
        $extraction = $this->buildExtraction('url', 'examplePiwik(.+)');

        $request = $this->buildRequest();
        $value   = $extraction->extract($request);

        $this->assertNull($value);
    }

    public function test_actionName_match()
    {
        $extraction = $this->buildExtraction('action_name', 'My(.+)Title');

        $request = $this->buildRequest();
        $value   = $extraction->extract($request);

        $this->assertSame(' Test ', $value);
    }

    public function test_extract_urlparam()
    {
        $request = $this->buildRequest();

        $value = $this->buildExtraction('urlparam', 'module')->extract($request);
        $this->assertSame('CoreHome', $value);

        $value = $this->buildExtraction('urlparam', 'action')->extract($request);
        $this->assertSame('test', $value);

        $value = $this->buildExtraction('urlparam', 'notExist')->extract($request);
        $this->assertNull($value);
    }

    public function test_extract_withAction_shouldReadValueFromAction_NotFromPassedRequest()
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
    public function test_extract_shouldBeCaseSensitiveByDefault($dimension, $pattern, $expectedExtracted)
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
    public function test_extract_WhenCaseInsensitiveIsEnabled($dimension, $pattern, $expectedExtracted)
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

    public function test_extract_anyRandomTrackingApiParameter()
    {
        $request = $this->buildRequest();

        $value = $this->buildExtraction('urlref', '/ref(.+)')->extract($request);
        $this->assertSame('errer', $value);
    }

    public function test_extract_whenOnlyPatternGiven()
    {
        $request = $this->buildRequest();

        $value = $this->buildExtraction('url', '(.+)')->extract($request);
        $this->assertSame('http://www.example.com/test/index.php?idsite=54&module=CoreHome&action=test&camelCase=fooBarBaz', $value);
    }

    public function test_check_shouldFailWhenInvalidDimensionGiven()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invald dimension \'anyInvalid\' used in an extraction. Available dimensions are: url, urlparam, action_name');

        $this->buildExtraction('anyInvalid', '/ref(.+)')->check();
    }

    public function test_check_shouldFailWhenInvalidRegGiven()
    {
        $check = '/foo(*)/';
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(Piwik::translate('General_ValidatorErrorNoValidRegex', array($check)));
        $this->buildExtraction('url', $check)->check();
    }

    /**
     * @dataProvider getInvalidPatterns
     */
    public function test_check_shouldFailWhenInvalidPatternGiven($pattern)
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('You need to group exactly one part of the regular expression inside round brackets, eg \'index_(.+).html\'');

        $this->buildExtraction('url', $pattern)->check();
    }

    public function test_check_shouldNotFailWhenValidCombinationsAreGiven()
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
