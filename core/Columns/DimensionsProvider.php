<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Columns;

use Piwik\CacheId;
use Piwik\Cache as PiwikCache;

class DimensionsProvider
{
    /**
     * @param $dimensionId
     * @return Dimension
     */
    public function factory($dimensionId)
    {
        $listDimensions = self::getMapOfNameToDimension();

        if (empty($listDimensions) || !is_array($listDimensions) || !$dimensionId || !array_key_exists($dimensionId, $listDimensions)) {
            return null;
        }

        return $listDimensions[$dimensionId];
    }

    private static function getMapOfNameToDimension()
    {
        $cacheId = CacheId::siteAware(CacheId::pluginAware('DimensionFactoryMap'));

        $cache = PiwikCache::getTransientCache();
        if ($cache->contains($cacheId)) {
            $mapIdToDimension = $cache->fetch($cacheId);
        } else {
            $dimensions = new static();
            $dimensions = $dimensions->getAllDimensions();

            $mapIdToDimension = array();
            foreach ($dimensions as $dimension) {
                $mapIdToDimension[$dimension->getId()] = $dimension;
            }

            $cache->save($cacheId, $mapIdToDimension);
        }

        return $mapIdToDimension;
    }

    /**
     * Returns a list of all available dimensions.
     * @return Dimension[]
     */
    public function getAllDimensions()
    {
        return Dimension::getAllDimensions();
    }

}