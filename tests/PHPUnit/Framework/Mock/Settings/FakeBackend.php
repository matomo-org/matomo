<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Framework\Mock\Settings;

use Piwik\Settings\Storage\Backend\BackendInterface;

class FakeBackend implements BackendInterface
{
    private $storageId;

    private $data = array('field1' => 'value1', 'field2' => 'value2');

    public function __construct($storageId)
    {
        $this->storageId = $storageId;
    }

    public function load()
    {
        return $this->data;
    }

    public function getStorageId()
    {
        return $this->storageId;
    }

    public function delete()
    {
        $this->data = array();
    }

    public function save($values)
    {
        $this->data = $values;
    }
}
