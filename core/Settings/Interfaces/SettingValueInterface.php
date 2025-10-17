<?php

namespace Piwik\Settings\Interfaces;

/**
 * @template T of mixed
 */
interface SettingValueInterface
{
    /**
     * @return self<T>
     */
    public static function getInstance(?int $idSite = null);

    /**
     * @return T
     */
    public function getValue();

    public static function getTitle(): string;

    public static function getInlineHelp(): string;
}
