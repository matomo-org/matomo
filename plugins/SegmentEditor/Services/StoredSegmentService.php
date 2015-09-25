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
     * Returns all stored segments that haven't been deleted.
     *
     * @return array
     */
    public function getAllSegmentsAndIgnoreVisibility()
    {
        $cacheKey = 'SegmentEditor.getAllSegmentsAndIgnoreVisibility';
        if (!$this->transientCache->contains($cacheKey)) {
            $result = $this->model->getAllSegmentsAndIgnoreVisibility();

            $this->transientCache->save($cacheKey, $result);
        }
        return $this->transientCache->fetch($cacheKey);
    }
}
