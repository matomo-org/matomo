<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\API;

use Exception;
use Piwik\API\ResponseBuilder;
use Piwik\Config;
use Piwik\DataTable;
use Piwik\Plugin\Manager;

/**
 * @group Core
 */
class ResponseBuilderTest extends \PHPUnit\Framework\TestCase
{
    public function setUp(): void
    {
        Manager::getInstance()->loadPlugins(array('API'));
    }

    public function test_getResponseException_shouldFormatExceptionDependingOnFormatAndAddDebugHelp()
    {
        $builder = new ResponseBuilder('xml', array());
        $response = $builder->getResponseException(new Exception('My Message'));

        $this->assertEquals('<?xml version="1.0" encoding="utf-8" ?>
<result>
	<error message="My Message
 
 --&gt; To temporarily debug this error further, set const PIWIK_PRINT_ERROR_BACKTRACE=true; in index.php" />
</result>', $response);
    }

    public function test_getResponse_shouldTreatAsSuccessIfNoValue()
    {
        $builder = new ResponseBuilder('xml', array());
        $response = $builder->getResponse(null);

        $this->assertEquals('<?xml version="1.0" encoding="utf-8" ?>
<result>
	<success message="ok" />
</result>', $response);
    }

    public function test_getResponse_shouldNotReturnAnythingIfContentWasOutput()
    {
        echo 5;
        $builder = new ResponseBuilder('xml', array());
        $response = $builder->getResponse(null);

        $this->assertNull($response);
        ob_clean();
    }

    public function test_getResponse_shouldHandleScalar()
    {
        $builder = new ResponseBuilder('xml', array());

        $response = $builder->getResponse(true);
        $this->assertEquals('<?xml version="1.0" encoding="utf-8" ?>
<result>1</result>', $response);

        $response = $builder->getResponse(5);
        $this->assertEquals('<?xml version="1.0" encoding="utf-8" ?>
<result>5</result>', $response);

        $response = $builder->getResponse('string');
        $this->assertEquals('<?xml version="1.0" encoding="utf-8" ?>
<result>string</result>', $response);
    }

    public function test_getResponse_shouldHandleDataTable()
    {
        $builder = new ResponseBuilder('xml', array());

        $dataTable = new DataTable();
        $dataTable->addRowFromSimpleArray(array('nb_visits' => 5, 'nb_random' => 10));

        $response = $builder->getResponse($dataTable);
        $this->assertEquals('<?xml version="1.0" encoding="utf-8" ?>
<result>
	<row>
		<nb_visits>5</nb_visits>
		<nb_random>10</nb_random>
	</row>
</result>', $response);
    }

    public function test_getResponse_shouldHandleObject()
    {
        $object   = new \stdClass();

        $builder  = new ResponseBuilder('xml', array());
        $response = $builder->getResponse($object);
        $this->assertSame('<?xml version="1.0" encoding="utf-8" ?>
<result>
	<error message="The API cannot handle this data structure." />
</result>', $response);

        $builder  = new ResponseBuilder('original', array());
        $response = $builder->getResponse($object);
        $this->assertSame($object, $response);
    }

    public function test_getResponse_shouldHandleResource()
    {
        $resource = curl_init();

        $builder  = new ResponseBuilder('xml', array());
        $response = $builder->getResponse($resource);
        $this->assertSame('<?xml version="1.0" encoding="utf-8" ?>
<result>
	<error message="The API cannot handle this data structure." />
</result>', $response);

        $builder  = new ResponseBuilder('original', array());
        $response = $builder->getResponse($resource);
        $this->assertSame($resource, $response);
    }

    public function test_getResponse_shouldHandleArray()
    {
        $builder  = new ResponseBuilder('xml', array());
        $response = $builder->getResponse(array(1, 2, 3, 'string', 10));
        $this->assertSame('<?xml version="1.0" encoding="utf-8" ?>
<result>
	<row>1</row>
	<row>2</row>
	<row>3</row>
	<row>string</row>
	<row>10</row>
</result>', $response);
    }

    public function test_getResponse_shouldHandleAssociativeArray()
    {
        $builder = new ResponseBuilder('xml', array());
        $response = $builder->getResponse(array('test' => 'two', 'test2' => 'three'));
        $this->assertSame('<?xml version="1.0" encoding="utf-8" ?>
<result>
	<row>
		<test>two</test>
		<test2>three</test2>
	</row>
</result>', $response);
    }

    public function test_getResponse_shouldHandleIndexedAssociativeArray()
    {
        $builder  = new ResponseBuilder('xml', array());
        $response = $builder->getResponse(array(array('test' => 'two', 'test2' => 'three')));
        $this->assertSame('<?xml version="1.0" encoding="utf-8" ?>
<result>
	<row>
		<test>two</test>
		<test2>three</test2>
	</row>
</result>', $response);
    }

    public function test_getResponse_shouldBeAbleToApplyFilterOnIndexedAssociativeArray()
    {
        $input = array();
        for ($i = 0; $i < 10; $i++) {
            $input[] = array('test' => 'two' . $i, 'test2' => 'three');
        }

        $builder  = new ResponseBuilder('original', array('serialize' => 0));
        $response = $builder->getResponse($input);

        $this->assertEquals($input, $response);
        $this->assertCount(10, $response);

        $builder  = new ResponseBuilder('original', array('serialize' => 0, 'showColumns' => 'test'));
        $response = $builder->getResponse($input);

        $this->assertEquals(array('test' => 'two0'), array_shift($response));
    }

    public function test_getResponse_shouldApplyFilterLimitOnIndexedArray()
    {
        $input    = range(0, 100);
        $expected = range(0, 14);
        $this->assertLimitedResponseEquals($expected, $input, $limit = 15, $offset = null);
    }

    public function test_getResponse_shouldReturnEmptyArrayOnIndexedArray_IfOffsetIsTooHigh()
    {
        $input = range(0, 100);
        $this->assertLimitedResponseEquals(array(), $input, $limit = 15, $offset = 200);
    }

    public function test_getResponse_shouldReturnAllOnIndexedArray_IfLimitIsTooHigh()
    {
        $input = range(0, 100);
        $this->assertLimitedResponseEquals($input, $input, $limit = 200, $offset = null);
    }

    public function test_getResponse_shouldNotApplyFilterLimitOnIndexedArrayIfParamNotSet()
    {
        $input = range(0, 100);
        $this->assertLimitedResponseEquals($input, $input, $limit = null, $offset = null);
    }

    public function test_getResponse_shouldApplyLimitOnIndexedArray_IfLimitIsDefaultFilterLimitValue()
    {
        $limit    = Config::getInstance()->General['API_datatable_default_limit'];
        $input    = range(0, 2000);
        $expected = range(0, $limit - 1);
        $this->assertLimitedResponseEquals($expected, $input, $limit, $offset = 0);
    }

    private function assertLimitedResponseEquals($expectedResponse, $input, $limit, $offset = 0)
    {
        $params = array('serialize' => 0);

        if (!is_null($limit)) {
            $params['filter_limit'] = $limit;
        }

        if (!is_null($offset)) {
            $params['filter_offset'] = $offset;
        }

        $builder  = new ResponseBuilder('json', $params);
        $response = json_decode($builder->getResponse($input), true);

        $this->assertEquals($expectedResponse, $response);
    }

    public function test_getResponse_shouldAlwaysApplyDefaultFilterLimit_EvenWhenResponseIsAnArray()
    {
        $input = range(0, 200);
        $limit = Config::getInstance()->General['API_datatable_default_limit'];

        $builder  = new ResponseBuilder('json', array(
            'serialize' => 0,
            'api_datatable_default_limit' => $limit,
            'filter_limit' => $limit,
            'filter_offset' => 0));
        $response = $builder->getResponse($input);

        $this->assertEquals(range(0, 99), json_decode($response, true));
    }

    public function test_getResponse_shouldApplyLimit_IfLimitIsSetBySystemButDifferentToDefaultLimit()
    {
        $input = range(0, 200);
        $defaultLimit = Config::getInstance()->General['API_datatable_default_limit'];
        $limit = $defaultLimit - 1;

        $builder  = new ResponseBuilder('json', array(
            'serialize' => 0,
            'api_datatable_default_limit' => $defaultLimit,
            'filter_limit' => $limit,
            'filter_offset' => 0));
        $response = $builder->getResponse($input);

        $this->assertEquals(range(0, $limit - 1), json_decode($response, true));
    }

    public function test_getResponse_shouldApplyFilterOffsetOnIndexedArray_IfFilterLimitIsGiven()
    {
        $input    = range(0, 100);
        $expected = range(30, 44);
        $this->assertLimitedResponseEquals($expected, $input, $limit = 15, $offset = 30);
    }

    public function test_getResponse_shouldApplyFilterOffsetOnIndexedArray_IfNoFilterLimitIsSetButOffset()
    {
        $input = range(0, 100);
        $expected = range(30, 100);
        $this->assertLimitedResponseEquals($expected, $input, $limit = null, $offset = 30);
    }

    public function test_getResponse_shouldReturnEmptyArrayOnIndexedArray_IfFilterLimitIsZero()
    {
        $input = range(0, 100);
        $this->assertLimitedResponseEquals($expected = array(), $input, $limit = 0, $offset = 30);
    }

    public function test_getResponse_shouldApplyFilterOffsetOnIndexedArray_IfFilterLimitIsMinusOne()
    {
        $input = range(0, 100);
        $expected = range(30, 100);
        $this->assertLimitedResponseEquals($expected, $input, $limit = -1, $offset = 30);
    }

    public function test_getResponse_shouldReturnAllOnIndexedArray_IfFilterLimitIsMinusOne()
    {
        $input = range(0, 100);
        $this->assertLimitedResponseEquals($input, $input, $limit = -1, $offset = null);
    }

    public function test_getResponse_shouldApplyPattern_IfFilterColumnAndPatternIsGiven()
    {
        $input = array(
            0 => array('name' => 'google', 'url' => 'www.google.com'),
            1 => array('name' => 'ask', 'url' => 'www.ask.com'),
            2 => array('name' => 'piwik', 'url' => 'piwik.org'),
            3 => array('url' => 'nz.yahoo.com'),
            4 => array('name' => 'amazon', 'url' => 'amazon.com'),
            5 => array('url' => 'nz.piwik.org'),
        );

        $builder  = new ResponseBuilder('json', array(
            'serialize' => 0,
            'filter_limit' => -1,
            'filter_column' => array('name', 'url'),
            'filter_pattern' => 'piwik'
        ));
        $response = $builder->getResponse($input);

        $expected = array(
            2 => array('name' => 'piwik', 'url' => 'piwik.org'),
            5 => array('url' => 'nz.piwik.org'),
        );
        $this->assertEquals($expected, json_decode($response, true));
    }

    public function test_getResponse_shouldBeAbleToApplyColumFilterAndLimitFilterOnIndexedAssociativeArray()
    {
        $input = array();
        for ($i = 0; $i < 10; $i++) {
            $input[] = array('test' => 'two' . $i, 'test2' => 'three');
        }

        $limit = 3;

        $builder  = new ResponseBuilder('json', array(
            'serialize' => 0,
            'filter_limit' => $limit,
            'filter_offset' => 3,
            'showColumns' => 'test'
        ));
        $response = $builder->getResponse($input);

        $this->assertEquals(array(
            0 => array('test' => 'two3'),
            1 => array('test' => 'two4'),
            2 => array('test' => 'two5'),
        ), json_decode($response, true));
    }
}
