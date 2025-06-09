<?php

namespace Piwik\Db;

trait TransactionalDatabaseDynamicTrait
{
    private $supportsTransactionLevelForNonLockingReads;

    public function getCurrentTransactionIsolationLevelForSession(): string
    {
        try {
            return $this->fetchOne('SELECT @@TX_ISOLATION');
        } catch (\Exception $e) {
            return $this->fetchOne('SELECT @@transaction_isolation');
        }
    }

    public function setTransactionIsolationLevel(string $level): void
    {
        $this->query("SET SESSION TRANSACTION ISOLATION LEVEL $level");
    }

    public function getSupportsTransactionLevelForNonLockingReads(): ?bool
    {
        return $this->supportsTransactionLevelForNonLockingReads;
    }

    public function setSupportsTransactionLevelForNonLockingReads(?bool $supports = null): void
    {
        $this->supportsTransactionLevelForNonLockingReads = $supports;
    }
}
