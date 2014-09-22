<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

use Piwik\API\ResponseBuilder;
use Piwik\DataTable;

/**
 * @group Core
 */
class ResponseBuilderTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        \Piwik\Plugin\Manager::getInstance()->loadPlugins(array('API'));
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

        $builder  = new ResponseBuilder('php', array('serialize' => 0));
        $response = $builder->getResponse($input);

        $this->assertEquals($input, $response);
        $this->assertCount(10, $response);

        $builder  = new ResponseBuilder('php', array('serialize' => 0, 'showColumns' => 'test'));
        $response = $builder->getResponse($input);

        $this->assertEquals(array('test' => 'two0'), array_shift($response));
    }

    public function test_getResponse_shouldApplyFilterLimitOnIndexedArray()
    {
        $input = range(0, 100);

        $builder  = new ResponseBuilder('php', array('serialize' => 0, 'filter_limit' => 15));
        $response = $builder->getResponse($input);

        $this->assertEquals(range(0, 14), $response);
    }

    public function test_getResponse_shouldReturnEmptyArrayOnIndexedArray_IfOffsetIsTooHigh()
    {
        $input = range(0, 100);

        $builder  = new ResponseBuilder('php', array('serialize' => 0, 'filter_limit' => 15, 'filter_offset' => 200));
        $response = $builder->getResponse($input);

        $this->assertEquals(array(), $response);
    }

    public function test_getResponse_shouldReturnAllOnIndexedArray_IfLimitIsTooHigh()
    {
        $input = range(0, 100);

        $builder  = new ResponseBuilder('php', array('serialize' => 0, 'filter_limit' => 200));
        $response = $builder->getResponse($input);

        $this->assertEquals($input, $response);
    }

    public function test_getResponse_shouldNotApplyFilterLimitOnIndexedArrayIfParamNotSet()
    {
        $input = range(0, 100);

        $builder  = new ResponseBuilder('php', array('serialize' => 0));
        $response = $builder->getResponse($input);

        $this->assertEquals($input, $response);
    }

    public function test_getResponse_shouldApplyFilterOffsetOnIndexedArray_IfFilterLimitIsGiven()
    {
        $input = range(0, 100);

        $builder  = new ResponseBuilder('php', array('serialize' => 0, 'filter_limit' => 15, 'filter_offset' => 30));
        $response = $builder->getResponse($input);

        $this->assertEquals(range(30, 44), $response);
    }

    public function test_getResponse_shouldNotApplyFilterOffsetOnIndexedArray_IfNoFilterLimitIsSetButOffset()
    {
        $input = range(0, 100);

        $builder  = new ResponseBuilder('php', array('serialize' => 0, 'filter_offset' => 30));
        $response = $builder->getResponse($input);

        $this->assertEquals($input, $response);
    }

    public function test_getResponse_shouldReturnEmptyArrayOnIndexedArray_IfFilterLimitIsZero()
    {
        $input = range(0, 100);

        $builder  = new ResponseBuilder('php', array('serialize' => 0, 'filter_limit' => 0, 'filter_offset' => 30));
        $response = $builder->getResponse($input);

        $this->assertEquals(array(), $response);
    }

    public function test_getResponse_shouldIgnoreFilterOffsetOnIndexedArray_IfFilterLimitIsMinusOne()
    {
        $input = range(0, 100);

        $builder  = new ResponseBuilder('php', array('serialize' => 0, 'filter_limit' => -1, 'filter_offset' => 30));
        $response = $builder->getResponse($input);

        $this->assertEquals($input, $response);
    }

    public function test_getResponse_shouldReturnAllOnIndexedArray_IfFilterLimitIsMinusOne()
    {
        $input = range(0, 100);

        $builder  = new ResponseBuilder('php', array('serialize' => 0, 'filter_limit' => -1));
        $response = $builder->getResponse($input);

        $this->assertEquals($input, $response);
    }
}
