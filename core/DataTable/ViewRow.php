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
    /**
     * @param DataTable $table The owning table.
     * @param int       $rowId Row ID within that table (may be ID_SUMMARY_ROW or ID_TOTALS_ROW).
     */
    public function __construct(
        private readonly DataTable $table,
        private readonly int $rowId
    ) {
        // Do NOT call parent::__construct() — that would populate ArrayObject storage.
        $this->subtableId       = $table->getRowSubtableId($rowId);
        $this->isSubtableLoaded = ($this->subtableId !== null);
    }

    // ── ArrayObject intercepts ────────────────────────────────────────────────

    public function offsetExists(mixed $name): bool
    {
        return $this->table->rowColumnExists($this->rowId, (string) $name);
    }

    public function offsetGet(mixed $name): mixed
    {
        return $this->table->getPackedValue($this->rowId, (string) $name);
    }

    public function offsetSet(mixed $name, mixed $value): void
    {
        $this->table->setPackedValue($this->rowId, (string) $name, $value);
    }

    public function offsetUnset(mixed $name): void
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

    public function getColumn($name): mixed
    {
        $value = $this->table->getPackedValue($this->rowId, (string) $name);
        return $value ?? false;
    }

    public function setColumn($name, $value): void
    {
        $this->table->setPackedValue($this->rowId, (string) $name, $value);
    }

    public function deleteColumn($name): bool
    {
        if (!isset($this->table->columnIndex[(string) $name])) {
            return false;
        }
        $this->table->setPackedValue($this->rowId, (string) $name, null);
        return true;
    }

    public function renameColumn($oldName, $newName): void
    {
        // Schema-level rename — delegate to DataTable so all rows are updated.
        $this->table->renameColumn($oldName, $newName);
    }

    public function getColumns(): array
    {
        return $this->table->getPackedRow($this->rowId);
    }

    public function setColumns(array $columns): void
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

    public function getMetadata($name = null): mixed
    {
        $meta = $this->table->getRowMetadata($this->rowId);
        if ($name === null) {
            return $meta;
        }
        return $meta[$name] ?? false;
    }

    public function setMetadata($name, $value): void
    {
        $this->table->setRowMetadataValue($this->rowId, $name, $value);
    }

    public function setAllMetadata(array $metadata): void
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

    public function addMetadata($name, $value): void
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

    public function removeSubtable(): void
    {
        parent::removeSubtable();
        $this->table->setRowSubtableId($this->rowId, null);
    }

    // ── Identity ──────────────────────────────────────────────────────────────

    public function isSummaryRow(): bool
    {
        return $this->rowId === DataTable::ID_SUMMARY_ROW;
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
