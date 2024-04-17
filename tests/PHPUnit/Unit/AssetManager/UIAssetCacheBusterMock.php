<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\AssetManager;

use Piwik\AssetManager\UIAssetCacheBuster;

class UIAssetCacheBusterMock extends UIAssetCacheBuster
{
    /**
     * @var string
     */
    private $piwikVersionBasedCacheBuster;

    /**
     * @var string
     */
    private $md5BasedCacheBuster;

    public function piwikVersionBasedCacheBuster($pluginNames = false)
    {
        return $this->piwikVersionBasedCacheBuster;
    }

    public function md5BasedCacheBuster($content)
    {
        return $this->md5BasedCacheBuster;
    }

    /**
     * @param string $md5BasedCacheBuster
     */
    public function setMd5BasedCacheBuster($md5BasedCacheBuster)
    {
        $this->md5BasedCacheBuster = $md5BasedCacheBuster;
    }

    /**
     * @param string $piwikVersionBasedCacheBuster
     */
    public function setPiwikVersionBasedCacheBuster($piwikVersionBasedCacheBuster)
    {
        $this->piwikVersionBasedCacheBuster = $piwikVersionBasedCacheBuster;
    }
}
