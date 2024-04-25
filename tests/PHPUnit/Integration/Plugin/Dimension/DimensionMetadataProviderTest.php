<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Plugin\Dimension;

use Piwik\Container\StaticContainer;
use Piwik\Plugin\Dimension\DimensionMetadataProvider;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Plugin\Manager as PluginManager;

class DimensionMetadataProviderTest extends IntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        /** @var PluginManager $manager */
        $manager = StaticContainer::get('Piwik\Plugin\Manager');
        $manager->loadPlugins(array('Events', 'Contents'));
    }

    public function testGetActionReferenceColumnsByTableDetectsActionReferenceDimensionsAndIncludesHardcodedColumns()
    {
        $dimensionMetadataProvider = new DimensionMetadataProvider();

        $actualColumns = $dimensionMetadataProvider->getActionReferenceColumnsByTable();

        $expectedColumns = array(
            'log_link_visit_action' => array(
                'idaction_url',
                'idaction_url_ref',
                'idaction_name_ref',
                'idaction_event_action',
                'idaction_event_category',
                'idaction_name',
                'idaction_content_interaction',
                'idaction_content_name',
                'idaction_content_piece',
                'idaction_content_target'
            ),
            'log_conversion' => array(
                'idaction_url',
            ),
            'log_visit' => array(
                'visit_exit_idaction_url',
                'visit_exit_idaction_name',
                'visit_entry_idaction_url',
                'visit_entry_idaction_name',
            ),
            'log_conversion_item' => array(
                'idaction_sku',
                'idaction_name',
                'idaction_category',
                'idaction_category2',
                'idaction_category3',
                'idaction_category4',
                'idaction_category5',
            ),
        );

        $this->assertEquals($expectedColumns, $actualColumns);
    }

    public function testGetActionReferenceColumnsByTableAppliesOverrideColumnsCorrectlyWithoutAllowingDuplicates()
    {
        $dimensionMetadataProvider = new DimensionMetadataProvider(array(
            'log_link_visit_action' => array('idaction_url',
                'idaction_event_category'
            ),

            'log_conversion' => array(),

            'log_conversion_item' => array('some_unknown_idaction_column'),

            'log_custom_table' => array('some_column1', 'some_column2')
        ));

        $actualColumns = $dimensionMetadataProvider->getActionReferenceColumnsByTable();

        $expectedColumns = array(
            'log_link_visit_action' => array(
                'idaction_url',
                'idaction_url_ref',
                'idaction_name_ref',
                'idaction_event_action',
                'idaction_event_category',
                'idaction_name',
                'idaction_content_interaction',
                'idaction_content_name',
                'idaction_content_piece',
                'idaction_content_target'
            ),
            'log_conversion' => array(
                'idaction_url',
            ),
            'log_visit' => array(
                'visit_exit_idaction_url',
                'visit_exit_idaction_name',
                'visit_entry_idaction_url',
                'visit_entry_idaction_name',
            ),
            'log_conversion_item' => array(
                'idaction_sku',
                'idaction_name',
                'idaction_category',
                'idaction_category2',
                'idaction_category3',
                'idaction_category4',
                'idaction_category5',
                'some_unknown_idaction_column'
            ),
            'log_custom_table' => array(
                'some_column1',
                'some_column2'
            )
        );

        $this->assertEquals($expectedColumns, $actualColumns);
    }
}
