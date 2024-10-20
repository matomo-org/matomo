<?php

namespace Piwik\Db;

interface TransactionalDatabaseInterface
{
    public function getCurrentTransactionIsolationLevelForSession(): string;
    public function setTransactionIsolationLevel(string $level): void;
    public function getSupportsTransactionLevelForNonLockingReads(): ?bool;
    public function setSupportsTransactionLevelForNonLockingReads(?bool $supports = null): void;
}
