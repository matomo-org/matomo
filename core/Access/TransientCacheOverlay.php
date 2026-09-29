<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Access;

use Matomo\Cache\Transient;

/**
 * Transient cache used while {@link \Piwik\Access::doAsSuperUser()} runs its callback.
 *
 * Reads fall through to the cache of the caller, writes stay in the overlay. Once the callback
 * returns, {@link applyInvalidationsTo()} drops every entry the callback saved or deleted from the
 * caller's cache, so the caller does not read entries saved with elevated access. Objects read from
 * the caller's cache are the same instances, so changes the callback makes to them remain.
 *
 * @internal
 */
class TransientCacheOverlay extends Transient
{
    /**
     * @var Transient
     */
    private $parent;

    /**
     * @var array<string, true>
     */
    private $touchedIds = [];

    /**
     * @var bool
     */
    private $isFlushed = false;

    public function __construct(Transient $parent)
    {
        $this->parent = $parent;
    }

    public function fetch($id)
    {
        if ($this->isLocal($id)) {
            return parent::fetch($id);
        }

        return $this->parent->fetch($id);
    }

    public function contains($id)
    {
        if ($this->isLocal($id)) {
            return parent::contains($id);
        }

        return $this->parent->contains($id);
    }

    public function save($id, $content, $lifeTime = 0)
    {
        $this->touchedIds[$id] = true;

        return parent::save($id, $content, $lifeTime);
    }

    public function delete($id)
    {
        $existed = $this->contains($id);
        $this->touchedIds[$id] = true;
        parent::delete($id);

        return $existed;
    }

    public function flushAll()
    {
        $this->isFlushed = true;
        $this->touchedIds = [];

        return parent::flushAll();
    }

    public function applyInvalidationsTo(Transient $cache): void
    {
        if ($this->isFlushed) {
            $cache->flushAll();
            return;
        }

        foreach (array_keys($this->touchedIds) as $id) {
            $cache->delete((string) $id);
        }
    }

    private function isLocal($id): bool
    {
        return $this->isFlushed || isset($this->touchedIds[$id]);
    }
}
