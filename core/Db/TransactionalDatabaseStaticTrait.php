<?php

namespace Piwik\Db;

trait TransactionalDatabaseStaticTrait
{
    private $supportsTransactionLevelForNonLockingReads;

    public function setTransactionIsolationLevel(string $level): void
    {
        static::query("SET SESSION TRANSACTION ISOLATION LEVEL $level");
    }

    public function getCurrentTransactionIsolationLevelForSession(): string
    {
        try {
            return static::fetchOne('SELECT @@TX_ISOLATION');
        } catch (\Exception $e) {
            return static::fetchOne('SELECT @@transaction_isolation');
        }
    }

    public function setSupportsTransactionLevelForNonLockingReads(?bool $supports = null): void
    {
        $this->supportsTransactionLevelForNonLockingReads = $supports;
    }

    public function getSupportsTransactionLevelForNonLockingReads(): ?bool
    {
        return $this->supportsTransactionLevelForNonLockingReads;
    }
}
