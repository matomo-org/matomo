<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Monolog\Handler;

use Monolog\Handler\AbstractHandler;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\HandlerWrapper;

class PluginLevelFilterHandler extends HandlerWrapper
{
    /**
     * @var int
     */
    private $defaultLevel;

    /**
     * @var int[]
     */
    private $pluginLevels;

    /**
     * @var int
     */
    private $minimumLevel;

    /**
     * @param int $defaultLevel
     * @param int[] $pluginLevels
     */
    public function __construct(HandlerInterface $handler, $defaultLevel, array $pluginLevels)
    {
        parent::__construct($handler);

        $this->defaultLevel = $defaultLevel;
        $this->pluginLevels = $pluginLevels;
        $this->minimumLevel = $this->resolveMinimumLevel($defaultLevel, $pluginLevels);

        if ($handler instanceof AbstractHandler) {
            $handler->setLevel($this->minimumLevel);
        }
    }

    public function isHandling(array $record)
    {
        return isset($record['level']) && $record['level'] >= $this->minimumLevel;
    }

    public function handle(array $record)
    {
        if (!$this->shouldHandle($record)) {
            return false;
        }

        return parent::handle($record);
    }

    public function handleBatch(array $records)
    {
        $records = array_values(array_filter($records, [$this, 'shouldHandle']));
        if (empty($records)) {
            return;
        }

        parent::handleBatch($records);
    }

    private function shouldHandle(array $record)
    {
        return isset($record['level']) && $record['level'] >= $this->getLevelForRecord($record);
    }

    private function getLevelForRecord(array $record)
    {
        $plugin = $record['extra']['class'] ?? null;
        if (!empty($plugin) && isset($this->pluginLevels[$plugin])) {
            return $this->pluginLevels[$plugin];
        }

        return $this->defaultLevel;
    }

    /**
     * @param int[] $pluginLevels
     * @return int
     */
    private function resolveMinimumLevel($defaultLevel, array $pluginLevels)
    {
        $levels = array_values($pluginLevels);
        $levels[] = $defaultLevel;

        return min($levels);
    }
}
