<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\DataTable;

use Piwik\DataTable;

/**
 * A lightweight proxy Row whose column data lives in the owning DataTable's
 * packed arrays. Created on demand by DataTable::getRowFromId(),
 * getRowFromLabel(), getRows(), etc.
 *
 * The ArrayObject internal storage is never populated — all offsetGet /
 * offsetSet / offsetExists / offsetUnset calls are intercepted and forwarded
 * to DataTable::getPackedValue() / setPackedValue().
 *
 * Mutations to columns, metadata, and subtableId are written back to the
 * DataTable immediately.
 *
 * @internal
 */
class ViewRow extends Row
{
    /** @var DataTable */
    private $table;

    /** @var int */
    private $rowId;

    /**
     * @param DataTable $table The owning table.
     * @param int       $rowId Row ID within that table (may be ID_SUMMARY_ROW or ID_TOTALS_ROW).
     */
    public function __construct(DataTable $table, int $rowId)
    {
        // Do NOT call parent::__construct() — that would populate ArrayObject storage.
        $this->table            = $table;
        $this->rowId            = $rowId;
        $this->subtableId       = $table->getRowSubtableId($rowId);
        $this->isSubtableLoaded = ($this->subtableId !== null);
    }

    // ── ArrayObject intercepts ────────────────────────────────────────────────

    public function offsetExists($name): bool
    {
        return $this->table->rowColumnExists($this->rowId, (string) $name);
    }

    public function offsetGet($name)
    {
        return $this->table->getPackedValue($this->rowId, (string) $name);
    }

    public function offsetSet($name, $value): void
    {
        $this->table->setPackedValue($this->rowId, (string) $name, $value);
    }

    public function offsetUnset($name): void
    {
        $this->deleteColumn((string) $name);
    }

    /** Enables: foreach ($row as $colName => $value) */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->getColumns());
    }

    public function count(): int
    {
        return $this->table->getColumnCount();
    }

    // ── Row API overrides ─────────────────────────────────────────────────────

    public function getColumn($name)
    {
        $value = $this->table->getPackedValue($this->rowId, (string) $name);
        return $value ?? false;
    }

    public function setColumn($name, $value)
    {
        $this->table->setPackedValue($this->rowId, (string) $name, $value);
    }

    public function deleteColumn($name): bool
    {
        return $this->table->deletePackedColumn($this->rowId, (string) $name);
    }

    public function renameColumn($oldName, $newName)
    {
        // Schema-level rename — delegate to DataTable so all rows are updated.
        $this->table->renameColumn($oldName, $newName);
    }

    public function getColumns(): array
    {
        return $this->table->getPackedRow($this->rowId);
    }

    public function setColumns($columns)
    {
        foreach ($columns as $name => $value) {
            $this->table->setPackedValue($this->rowId, (string) $name, $value);
        }
    }

    public function getArrayCopy(): array
    {
        return $this->getColumns();
    }

    // ── Metadata ─────────────────────────────────────────────────────────────

    public function getMetadata($name = null)
    {
        $meta = $this->table->getRowMetadata($this->rowId);
        if ($name === null) {
            return $meta;
        }
        return $meta[$name] ?? false;
    }

    public function setMetadata($name, $value)
    {
        $this->table->setRowMetadataValue($this->rowId, $name, $value);
    }

    public function setAllMetadata($metadata)
    {
        $this->table->setRowMetadata($this->rowId, $metadata);
    }

    public function deleteMetadata($name = false): bool
    {
        if ($name === false) {
            $this->table->setRowMetadata($this->rowId, []);
            return true;
        }
        return $this->table->deleteRowMetadataKey($this->rowId, (string) $name);
    }

    public function addMetadata($name, $value)
    {
        if ($this->getMetadata($name) !== false) {
            throw new \Exception("Metadata '$name' already exists.");
        }
        $this->setMetadata($name, $value);
    }

    // ── Subtable ──────────────────────────────────────────────────────────────

    public function setSubtable(DataTable $subTable): DataTable
    {
        $result = parent::setSubtable($subTable);
        // Write back the assigned subtableId to the DataTable's sparse map.
        $this->table->setRowSubtableId($this->rowId, $this->subtableId);
        return $result;
    }

    public function removeSubtable()
    {
        parent::removeSubtable();
        $this->table->setRowSubtableId($this->rowId, null);
    }

    // ── Identity ──────────────────────────────────────────────────────────────

    public function isSummaryRow(): bool
    {
        return $this->rowId === DataTable::ID_SUMMARY_ROW;
    }

    // ── Lifecycle ─────────────────────────────────────────────────────────────

    /**
     * ViewRow does NOT own the subtable — the DataTable's packed storage does.
     * Override to prevent Row::__destruct() from calling deleteTable().
     */
    public function __destruct()
    {
        // intentionally empty
    }

    // ── Serialization ─────────────────────────────────────────────────────────

    public function export(): array
    {
        return [
            self::COLUMNS              => $this->getColumns(),
            self::METADATA             => $this->getMetadata(),
            self::DATATABLE_ASSOCIATED => $this->subtableId,
        ];
    }
}
