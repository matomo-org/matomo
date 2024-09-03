<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\Archive;

use Piwik\Archive\DataCollection;
use Piwik\Archive\DataTableFactory;
use Piwik\Period;
use Piwik\Segment;

/**
 * @group DataCollectionTest
 * @group Archive
 */
class DataCollectionTest extends \PHPUnit\Framework\TestCase
{
    private $site1 = 1;
    private $site2 = 2;
    private $date1 = '2012-12-12,2012-12-12';
    private $date2 = '2012-12-13,2012-12-13';

    private function createCollection($onlyOnePeriod = false, $onlyOneSite = false)
    {
        $periods   = array(
            Period\Factory::build('day', '2012-12-12'),
            Period\Factory::build('day', '2012-12-13'),
        );
        $dataType  = 'numeric';
        $siteIds   = array($this->site1, $this->site2);
        $dataNames = array('Name1', 'Name2');
        $defaultRow = array(
            'default' => 1
        );

        if ($onlyOnePeriod) {
            $periods = array($periods[0]);
        }

        if ($onlyOneSite) {
            $siteIds = array($siteIds[0]);
        }

        // using mock since Segment makes API queries
        $mockSegment = $this->getMockBuilder(Segment::class)
            ->disableOriginalConstructor()
            ->getMock()
            ->method('getString')->willReturn('');
        return new DataCollection($dataNames, $dataType, $siteIds, $periods, $mockSegment, $defaultRow);
    }

    public function testGetIndexedArrayNumericNoResultIndicesNoData()
    {
        $collection = $this->createCollection($onlyOnePeriod = true, $onlyOneSite = true);
        $this->assertEquals(array(), $collection->getIndexedArray($resultIndices = array()));
    }

    public function testGetIndexedArrayNumericNoResultIndicesWithData()
    {
        $collection = $this->createCollection($onlyOnePeriod = true, $onlyOneSite = true);
        $collection->set($this->site1, '2012-12-12,2012-12-12', 'nb_visits', '5');
        $collection->set($this->site1, '2012-12-12,2012-12-12', 'nb_unique_visits', '10');

        $expected = array(
            'default' => 1,
            'nb_visits' => '5',
            'nb_unique_visits' => '10',
        );

        $this->assertEquals($expected, $collection->getIndexedArray($resultIndices = array()));
    }

    public function testGetIndexedArrayNumericNoResultIndicesWithDefaultOverwritten()
    {
        $collection = $this->createCollection($onlyOnePeriod = true, $onlyOneSite = true);
        $collection->set($this->site1, '2012-12-12,2012-12-12', 'nb_visits', '5');
        $collection->set($this->site1, '2012-12-12,2012-12-12', 'default', '10');

        $expected = array(
            'default' => '10',
            'nb_visits' => '5'
        );

        $this->assertEquals($expected, $collection->getIndexedArray($resultIndices = array()));
    }

    private function getSiteResultIndices()
    {
        return array(DataTableFactory::TABLE_METADATA_SITE_INDEX => 'idSite');
    }

    public function testGetIndexedArrayNumericWithSiteResultIndicesNoData()
    {
        $collection = $this->createCollection();

        $this->assertEquals(array(
            1 => array(),
            2 => array()
        ), $collection->getIndexedArray($this->getSiteResultIndices()));
    }

    public function testGetIndexedArrayNumericWithSiteResultIndicesWithData()
    {
        $collection = $this->createCollection();
        $collection->set($this->site1, '2012-12-12,2012-12-12', 'nb_visits', '5');
        $collection->set($this->site1, '2012-12-12,2012-12-12', 'nb_unique_visits', '10');

        $expected = array(
            1 => array(
                'default' => 1,
                'nb_visits' => '5',
                'nb_unique_visits' => '10',
            ),
            2 => array(
            )
        );

        $this->assertEquals($expected, $collection->getIndexedArray($this->getSiteResultIndices()));
    }

    public function testGetIndexedArrayNumericWithSiteResultIndicesWithDefaultOverwritten()
    {
        $collection = $this->createCollection();
        $collection->set($this->site1, '2012-12-12,2012-12-12', 'nb_visits', '5');
        $collection->set($this->site1, '2012-12-12,2012-12-12', 'default', '10');
        $collection->set($this->site2, '2012-12-12,2012-12-12', 'nb_visits', '15');

        $expected = array(
            1 => array(
                'default' => '10',
                'nb_visits' => '5'
            ),
            2 => array(
                'default' => 1,
                'nb_visits' => '15'
            )
        );

        $this->assertEquals($expected, $collection->getIndexedArray($this->getSiteResultIndices()));
    }

    private function getPeriodResultIndices()
    {
        return array(DataTableFactory::TABLE_METADATA_PERIOD_INDEX => 'date');
    }

    public function testGetIndexedArrayNumericWithPeriodResultIndicesNoData()
    {
        $collection = $this->createCollection($onlyOnePeriod = false, $onlyOneSite = true);

        $this->assertEquals(array(
            $this->date1 => array(),
            $this->date2 => array()
        ), $collection->getIndexedArray($this->getPeriodResultIndices()));
    }

    public function testGetIndexedArrayNumericWithPeriodResultIndicesWithData()
    {
        $collection = $this->createCollection($onlyOnePeriod = false, $onlyOneSite = true);
        $collection->set($this->site1, $this->date1, 'nb_visits', '5');
        $collection->set($this->site1, $this->date1, 'nb_unique_visits', '10');

        $expected = array(
            $this->date1 => array(
                'default' => 1,
                'nb_visits' => '5',
                'nb_unique_visits' => '10',
            ),
            $this->date2 => array(
            )
        );

        $this->assertEquals($expected, $collection->getIndexedArray($this->getPeriodResultIndices()));
    }

    public function testGetIndexedArrayNumericWithPeriodResultIndicesWithDefaultOverwritten()
    {
        $collection = $this->createCollection($onlyOnePeriod = false, $onlyOneSite = true);
        $collection->set($this->site1, $this->date1, 'nb_visits', '5');
        $collection->set($this->site1, $this->date1, 'default', '10');
        $collection->set($this->site1, $this->date2, 'nb_visits', '15');

        $expected = array(
            $this->date1 => array(
                'default' => '10',
                'nb_visits' => '5'
            ),
            $this->date2 => array(
                'default' => 1,
                'nb_visits' => '15'
            )
        );

        $this->assertEquals($expected, $collection->getIndexedArray($this->getPeriodResultIndices()));
    }


    private function getPeriodAndSiteResultIndices()
    {
        return array_merge($this->getSiteResultIndices(), $this->getPeriodResultIndices());
    }

    public function testGetIndexedArrayNumericWithPeriodAndSiteResultIndicesNoData()
    {
        $collection = $this->createCollection();

        $expected = array(
            $this->site1 => array(
                $this->date1 => array(),
                $this->date2 => array()
            ),
            $this->site2 => array(
                $this->date1 => array(),
                $this->date2 => array()
            )
        );

        $this->assertEquals($expected, $collection->getIndexedArray($this->getPeriodAndSiteResultIndices()));
    }

    public function testGetIndexedArrayNumericWithPeriodAndSiteResultIndicesWithData()
    {
        $collection = $this->createCollection();
        $collection->set($this->site1, $this->date1, 'nb_visits', '5');
        $collection->set($this->site1, $this->date1, 'nb_unique_visits', '10');
        $collection->set($this->site2, $this->date1, 'nb_unique_visits', '21');
        $collection->set($this->site2, $this->date2, 'nb_unique_visits', '22');

        $expected = array(
            $this->site1 => array(
                $this->date1 => array(
                    'default' => 1,
                    'nb_visits' => '5',
                    'nb_unique_visits' => '10',
                ),
                $this->date2 => array()
            ),
            $this->site2 => array(
                $this->date1 => array(
                    'default' => 1,
                    'nb_unique_visits' => '21',
                ),
                $this->date2 => array(
                    'default' => 1,
                    'nb_unique_visits' => '22',
                )
            )
        );

        $this->assertEquals($expected, $collection->getIndexedArray($this->getPeriodAndSiteResultIndices()));
    }

    public function testGetIndexedArrayNumericWithPeriodAndSiteResultIndicesWithDefaultOverwritten()
    {
        $collection = $this->createCollection();
        $collection->set($this->site1, $this->date1, 'nb_visits', '5');
        $collection->set($this->site1, $this->date1, 'default', '10');
        $collection->set($this->site2, $this->date1, 'default', '21');

        $expected = array(
            $this->site1 => array(
                $this->date1 => array(
                    'default' => 10,
                    'nb_visits' => '5',
                ),
                $this->date2 => array()
            ),
            $this->site2 => array(
                $this->date1 => array('default' => 21),
                $this->date2 => array()
            )
        );

        $this->assertEquals($expected, $collection->getIndexedArray($this->getPeriodAndSiteResultIndices()));
    }
}
