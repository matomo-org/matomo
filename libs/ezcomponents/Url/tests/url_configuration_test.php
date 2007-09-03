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
class ezcUrlConfigurationTest extends ezcTestCase
{
    public function testPropertiesGet()
    {
        $urlCfg = new ezcUrlConfiguration();
        $this->assertEquals( null, $urlCfg->basedir );
        $this->assertEquals( null, $urlCfg->script );
        $this->assertEquals( array(), $urlCfg->orderedParameters );
        $this->assertEquals( array(), $urlCfg->unorderedParameters );
        $this->assertEquals( array( '(', ')' ), $urlCfg->unorderedDelimiters );
    }

    public function testPropertiesGetInvalid()
    {
        $urlCfg = new ezcUrlConfiguration();
        try
        {
            $urlCfg->no_such_property;
            $this->fail( 'Expected exception was not thrown' );
        }
        catch ( ezcBasePropertyNotFoundException $e )
        {
            $expected = "No such property name 'no_such_property'.";
            $this->assertEquals( $expected, $e->getMessage() );
        }
    }

    public function testPropertiesSet()
    {
        $urlCfg = new ezcUrlConfiguration();
        $urlCfg->basedir = '/mydir/shop';
        $urlCfg->script = 'index.php';
        $urlCfg->unorderedDelimiters = array( '_', '_' );
        $urlCfg->addOrderedParameter( 'section' );
        $urlCfg->addOrderedParameter( 'module' );
        $urlCfg->addOrderedParameter( 'view' );
        $urlCfg->addOrderedParameter( 'branch' );
        $urlCfg->addUnorderedParameter( 'file' );

        $this->assertEquals( '/mydir/shop', $urlCfg->basedir );
        $this->assertEquals( 'index.php', $urlCfg->script );
        $this->assertEquals( array( 'section' => 0, 'module' => 1, 'view' => 2, 'branch' => 3 ),
                             $urlCfg->orderedParameters );
        $this->assertEquals( array( 'file' => 1 ), $urlCfg->unorderedParameters );
        $this->assertEquals( array( '_', '_' ), $urlCfg->unorderedDelimiters );
    }

    public function testPropertiesSetInvalid()
    {
        $urlCfg = new ezcUrlConfiguration();
        try
        {
            $urlCfg->no_such_property = 'some value';
            $this->fail( 'Expected exception was not thrown' );
        }
        catch ( ezcBasePropertyNotFoundException $e )
        {
            $expected = "No such property name 'no_such_property'.";
            $this->assertEquals( $expected, $e->getMessage() );
        }
    }

    public function testAddOrderedParameter()
    {
        $urlCfg = new ezcUrlConfiguration();
        $urlCfg->addOrderedParameter( 'folder' );
    }

    public function testAddUnorderedParameter()
    {
        $urlCfg = new ezcUrlConfiguration();
        $urlCfg->addUnorderedParameter( 'folder' );
    }

    public function testIsSet()
    {
        $urlCfg = new ezcUrlConfiguration();
        $this->assertEquals( false, isset( $urlCfg->basedir ) );
        $this->assertEquals( false, isset( $urlCfg->script ) );
        $this->assertEquals( true, isset( $urlCfg->unorderedDelimiters ) );
        $this->assertEquals( true, isset( $urlCfg->orderedParameters ) );
        $this->assertEquals( true, isset( $urlCfg->unorderedParameters ) );
        $this->assertEquals( false, isset( $urlCfg->no_such_property ) );
    }

    public function testDelayedInit()
    {
        ezcBaseInit::setCallback( 'ezcUrlConfiguration', 'testDelayedInitUrlConfiguration' );
        $urlCfg = ezcUrlConfiguration::getInstance();
        $this->assertEquals( array( 'section' => 0 ), $urlCfg->orderedParameters );
        $this->assertEquals( array( 'article' => 1 ), $urlCfg->unorderedParameters );
    }

    public static function suite()
    {
         return new PHPUnit_Framework_TestSuite( "ezcUrlConfigurationTest" );
    }
}

class testDelayedInitUrlConfiguration
{
    static function configureObject( $object )
    {
        $object->addOrderedParameter( 'section' );
        $object->addUnorderedParameter( 'article' );
    }
}
?>
