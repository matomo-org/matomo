<?php

namespace Piwik\Policy\SettingValues;

abstract class SettingValue
{
    /** @var mixed */
    protected $value; 

    /** @var int|null */
    protected $idSite;

    /** @var string|null */
    protected $notes;

    public function __construct(?int $idSite, mixed $value, ?string $notes)
    {
        $this->idSite = $idSite;
        $this->value = $value;
        $this->notes = $notes;
    }

    public abstract function compare(?self $setting): self;

    public function getValue()
    {
        return $this->value;
    }

    public function getNotes()
    {
        return $this->notes;
    }
}
