<?php
/**
 * @copyright Copyright (C) 2005-2007 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 * @version 1.1
 * @filesource
 * @package Url
 * @subpackage Tests
 */

/**
 * @package Url
 * @subpackage Tests
 */
class ezcUrlCreatorTest extends ezcTestCase
{
    public function testGetUrl()
    {
        ezcUrlCreator::registerUrl( 'map', 'http://www.example.com' );
        $expected = 'http://www.example.com';
        $this->assertEquals( $expected, ezcUrlCreator::getUrl( 'map' ) );
    }

    public function testGetUrlNotRegistered()
    {
        try
        {
            ezcUrlCreator::getUrl( 'not registered url' );
            $this->fail( 'Expected exception was not thrown' );
        }
        catch ( ezcUrlNotRegisteredException $e )
        {
            $expected = "The url 'not registered url' is not registered.";
            $this->assertEquals( $expected, $e->getMessage() );
        }
    }

    public function testGetUrlFormatted()
    {
        ezcUrlCreator::registerUrl( 'map', 'http://www.example.com/images/%s?xsize=%d&ysize=%d&zoom=%d' );
        $expected = 'http://www.example.com/images/map_sweden.gif?xsize=400&ysize=300&zoom=4';
        $this->assertEquals( $expected, ezcUrlCreator::getUrl( 'map', 'map_sweden.gif', 400, 300, 4 ) );
    }

    public function testPrependUrl()
    {
        ezcUrlCreator::registerUrl( 'map', 'http://www.example.com?id=1' );
        $expected = 'http://www.example.com/images?id=1';
        $this->assertEquals( $expected, ezcUrlCreator::prependUrl( 'map', 'images' ) );
    }

    public function testPrependUrlNotRegistered()
    {
        try
        {
            ezcUrlCreator::prependUrl( 'not registered url', 'images' );
            $this->fail( 'Expected exception was not thrown' );
        }
        catch ( ezcUrlNotRegisteredException $e )
        {
            $expected = "The url 'not registered url' is not registered.";
            $this->assertEquals( $expected, $e->getMessage() );
        }
    }

    public static function suite()
    {
         return new PHPUnit_Framework_TestSuite( "ezcUrlCreatorTest" );
    }
}
?>
