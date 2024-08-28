<?php

namespace Piwik\Db;

interface TransactionalDatabaseInterface
{
    public function fetchOne($sql, $parameters = []);
    public function query($sql, $parameters = []);
    public function setSupportsTransactionLevelForNonLockingReads(bool $supports): void;
    public function getSupportsTransactionLevelForNonLockingReads(): ?bool;
}