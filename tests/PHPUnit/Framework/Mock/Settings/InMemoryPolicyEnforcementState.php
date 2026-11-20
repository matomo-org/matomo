<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Framework\Mock\Settings;

use Piwik\Settings\PolicyEnforcementState;

/**
 * Enforcement state kept in memory, so that the resolution rules of
 * {@link \Piwik\Settings\Interfaces\Traits\PolicyComparisonTrait} can be unit tested
 * without a database.
 */
class InMemoryPolicyEnforcementState extends PolicyEnforcementState
{
    /**
     * States per storage key, of [scope => bool]. The instance wide scope is stored under ''.
     *
     * @var array<string, array<string, bool>>
     */
    private static $states = [];

    /**
     * @var bool
     */
    private static $isWritable = true;

    /**
     * @var string
     */
    private $key;

    public function __construct(string $pluginName, string $settingName)
    {
        parent::__construct($pluginName, $settingName);

        $this->key = $pluginName . '.' . $settingName;
    }

    public static function reset(): void
    {
        self::$states = [];
        self::$isWritable = true;
    }

    public static function setIsWritable(bool $isWritable): void
    {
        self::$isWritable = $isWritable;
    }

    public function get(?int $idSite = null): ?bool
    {
        return self::$states[$this->key][$this->scope($idSite)] ?? null;
    }

    public function set(?int $idSite, ?bool $isEnforced): void
    {
        if (is_null($isEnforced)) {
            // the real storage drops null values when persisting, removing the row entirely
            unset(self::$states[$this->key][$this->scope($idSite)]);

            if (empty(self::$states[$this->key])) {
                unset(self::$states[$this->key]);
            }

            return;
        }

        self::$states[$this->key][$this->scope($idSite)] = $isEnforced;
    }

    public function isWritableByCurrentUser(?int $idSite = null): bool
    {
        return self::$isWritable;
    }

    /**
     * All stored states, for asserting that nothing was written.
     *
     * @return array<string, array<string, bool>>
     */
    public static function getAllStates(): array
    {
        return self::$states;
    }

    private function scope(?int $idSite): string
    {
        return is_null($idSite) ? '' : (string) $idSite;
    }
}
