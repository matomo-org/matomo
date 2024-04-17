<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\AssetManager;

use PHPUnit\Framework\TestCase;
use Piwik\AssetManager\UIAsset\OnDiskUIAsset;
use Piwik\AssetManager\UIAssetCatalog;
use Piwik\AssetManager\UIAssetCatalogSorter;

class UIAssetCatalogSorterTest extends TestCase
{
    /**
     * @group Core
     */
    public function testPrioritySort()
    {
        $baseDirectory = '/var/www/piwik/';

        $priorityPatterns = array(
            'libs/base.css',
            'libs/',
            'plugins/',
        );

        $catalogSorter = new UIAssetCatalogSorter($priorityPatterns);

        $unsortedCatalog = new UIAssetCatalog($catalogSorter);
        $unsortedCatalog->addUIAsset(new OnDiskUIAsset($baseDirectory, 'new_dir/new_file'));
        $unsortedCatalog->addUIAsset(new OnDiskUIAsset($baseDirectory, 'plugins/xyz'));
        $unsortedCatalog->addUIAsset(new OnDiskUIAsset($baseDirectory, 'plugins/abc'));
        $unsortedCatalog->addUIAsset(new OnDiskUIAsset($baseDirectory, 'libs/xyz'));
        $unsortedCatalog->addUIAsset(new OnDiskUIAsset($baseDirectory, 'libs/base.css'));
        $unsortedCatalog->addUIAsset(new OnDiskUIAsset($baseDirectory, 'libs/abc'));
        $unsortedCatalog->addUIAsset(new OnDiskUIAsset($baseDirectory, 'plugins/xyz'));
        $unsortedCatalog->addUIAsset(new OnDiskUIAsset($baseDirectory, 'libs/base.css'));
        $unsortedCatalog->addUIAsset(new OnDiskUIAsset($baseDirectory, 'libs/xyz'));

        $expectedCatalog = new UIAssetCatalog($catalogSorter);
        $expectedCatalog->addUIAsset(new OnDiskUIAsset($baseDirectory, 'libs/base.css'));
        $expectedCatalog->addUIAsset(new OnDiskUIAsset($baseDirectory, 'libs/xyz'));
        $expectedCatalog->addUIAsset(new OnDiskUIAsset($baseDirectory, 'libs/abc'));
        $expectedCatalog->addUIAsset(new OnDiskUIAsset($baseDirectory, 'plugins/xyz'));
        $expectedCatalog->addUIAsset(new OnDiskUIAsset($baseDirectory, 'plugins/abc'));
        $expectedCatalog->addUIAsset(new OnDiskUIAsset($baseDirectory, 'new_dir/new_file'));

        $sortedCatalog = $unsortedCatalog->getSortedCatalog();

        $this->assertEquals($expectedCatalog, $sortedCatalog);
    }
}
