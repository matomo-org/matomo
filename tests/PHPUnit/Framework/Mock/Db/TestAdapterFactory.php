<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Framework\Mock\Db;

use Piwik\Db\AdapterFactory;

class TestAdapterFactory extends AdapterFactory
{
    public function factory($adapterName, &$dbInfos, $connect = true)
    {
        return parent::factory($adapterName, $dbInfos, false);
    }
}