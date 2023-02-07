<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Db\Utility;

use RecursiveIterator;

class CursorCallbackIterator implements \RecursiveIterator
{
    /**
     * @var \Zend_Db_Statement
     */
    private $cursor;

    /**
     * @var callable
     */
    private $callback;

    /**
     * @var int
     */
    private $rowCount = 0;

    /**
     * @var array|false
     */
    private $currentRow;

    public function __construct($cursor, callable $callback = null)
    {
        $this->cursor = $cursor;
        $this->callback = $callback;

        $this->fetch();
    }

    #[\ReturnTypeWillChange]
    public function current()
    {
        return $this->currentRow;
    }

    #[\ReturnTypeWillChange]
    public function next()
    {
        if (empty($this->currentRow)) {
            return;
        }

        $this->fetch();
    }

    #[\ReturnTypeWillChange]
    public function key()
    {
        return $this->rowCount;
    }

    #[\ReturnTypeWillChange]
    public function valid()
    {
        return !empty($this->currentRow);
    }

    #[\ReturnTypeWillChange]
    public function rewind()
    {
        // ignore
    }

    #[\ReturnTypeWillChange]
    public function hasChildren()
    {
        return $this->currentRow instanceof \Iterator;
    }

    #[\ReturnTypeWillChange]
    public function getChildren()
    {
        return $this->currentRow;
    }

    private function fetch()
    {
        $this->currentRow = $this->cursor->fetch();
        if (empty($this->currentRow)) {
            $this->cursor->closeCursor();
        } else if ($this->callback) {
            $callback = $this->callback;
            $this->currentRow = $callback($this->currentRow);
        }

        $this->rowCount += 1;
    }
}