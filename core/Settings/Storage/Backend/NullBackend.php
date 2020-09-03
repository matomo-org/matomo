<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Settings\Storage\Backend;

/**
 * Static / temporary storage where a value shall never be persisted. Meaning it will use the default value
 * for each request until configured differently. Useful for tests etc.
 */
class NullBackend implements BackendInterface
{
    private $storageId;

    public function __construct($storageId)
    {
        $this->storageId = $storageId;
    }

    public function load()
    {
        return array();
    }

    public function getStorageId()
    {
        return $this->storageId;
    }

    public function delete()
    {
    }

    public function save($values)
    {
    }
}
