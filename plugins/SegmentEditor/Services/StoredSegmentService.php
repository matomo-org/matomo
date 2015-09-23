<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\SegmentEditor\Services;

use Piwik\Plugins\SegmentEditor\Model;
use Piwik\Cache\Transient as TransientCache;


/**
 * Service layer class for stored segments.
 */
class StoredSegmentService
{
    /**
     * @var Model
     */
    private $model;

    /**
     * @var TransientCache
     */
    private $transientCache;

    public function __construct(Model $model, TransientCache $transientCache)
    {
        $this->model = $model;
        $this->transientCache = $transientCache;
    }

    /**
     * Returns stored segments that are set to be archived during cron archiving.
     *
     * @param int|bool $idSite
     * @return array
     */
    public function getSegmentsToAutoArchive($idSite = false)
    {
        if ($idSite != 'all') {
            $idSite = (int)$idSite;
        }

        $cacheKey = 'SegmentEditor.getSegmentsToAutoArchive_' . (!empty($idSite) ? $idSite : 'enabled_all');
        if (!$this->transientCache->contains($cacheKey)) {
            $result = $this->model->getSegmentsToAutoArchive($idSite);

            $this->transientCache->save($cacheKey, $result);
        }
        return $this->transientCache->fetch($cacheKey);
    }
}
