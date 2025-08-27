<?php

namespace Piwik\Policy\Settings;

/**
 * @template T of mixed
 */
interface SettingValueInterface
{
    public static function getInstance(?int $idSite = null);

    /**
     * @return T
     */
    public function getValue();
}
