<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\SitesManager\Test\Unit;

use DatabaseTestCase;
use Piwik\Plugins\SitesManager\Model;
use Piwik\Plugins\SitesManager\Test\Helper\SiteCreator;

/**
 * Class Model
 * @package Piwik\Plugins\SitesManager\Test\Unit
 *
 * @group SitesManager
 */
class ModelTest extends \DatabaseTestCase
{
    /**
     * @var SiteCreator
     */
    protected $siteCreator;

    /**
     * @inheritdoc
     */
    public function __construct($name = null, array $data = array(), $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->siteCreator = new SiteCreator();
    }

    /**
     * @test
     */
    public function shouldReturnSiteById()
    {
        $id = $this->siteCreator->createSite(array(
            'name' => 'test',
            'main_url' => 'http://example.com'
        ));

        $model = new Model();

        $site = $model->getSitesFromIds(array($id));

        $this->assertEquals('test', $site[0]['name']);
    }

    /**
     * @test
     */
    public function withoutIdSitesShouldReturnEmptyArray()
    {
        $model = new Model();

        $site = $model->getSitesFromIds(array());

        $this->assertCount(0, $site);
    }

    /**
     * @test
     */
    public function afterCreatingTwoSitesShouldReturnThisWithLowerIdWithLimiting()
    {
        $first = $this->siteCreator->createSite(array(
            'name' => 'test1',
            'main_url' => 'http://example1.com'
        ));

        $second = $this->siteCreator->createSite(array(
            'name' => 'test2',
            'main_url' => 'http://example2.com'
        ));

        $model = new Model();

        $site = $model->getSitesFromIds(array($first, $second), 1);

        $this->assertCount(1, $site);
        $this->assertEquals('test1', $site[0]['name']);
    }

    /**
     * @test
     */
    public function afterCreatingThreeSitesShouldReturnAllOfThemWhenLimitingIsNotSet()
    {
        $sites = array();

        $sites[] = $this->siteCreator->createSite(array(
            'name' => 'test1',
            'main_url' => 'http://example1.com'
        ));

        $sites[] = $this->siteCreator->createSite(array(
            'name' => 'test2',
            'main_url' => 'http://example2.com'
        ));

        $sites[] = $this->siteCreator->createSite(array(
            'name' => 'test3',
            'main_url' => 'http://example3.com'
        ));

        $model = new Model();

        $site = $model->getSitesFromIds($sites);

        $this->assertCount(3, $site);
        $this->assertEquals('test1', $site[0]['name']);
        $this->assertEquals('test2', $site[1]['name']);
        $this->assertEquals('test3', $site[2]['name']);
    }

    /**
     * @test
     */
    public function shouldReturnNullIfSiteDoesNotExists()
    {
        $id = $this->siteCreator->createSite(array(
            'name' => 'test',
            'main_url' => 'http://example.com'
        ));

        $model = new Model();

        $site = $model->getSiteFromId($id + 1);

        $this->assertFalse($site);
    }
} 
