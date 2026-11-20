<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\AIProviders;

use Piwik\Container\StaticContainer;
use Piwik\Plugins\AIProviders\Provider\AIProvider;
use Psr\Log\LoggerInterface;

/**
 * Registry of the AI providers registered by active plugins.
 *
 * Providers come in two flavours:
 *
 * - **Selectable** providers (the default) are offered to super users in the
 *   administration UI and are eligible to become the instance-wide default
 *   provider.
 * - **Restricted** (non-selectable) providers stay registered and can serve
 *   completions, but are hidden from every admin surface and can never become
 *   the default. A managed environment uses this to keep the
 *   basic providers available for plugins that are allowed to target a
 *   specific provider (see {@link AIProviderService::complete()}) while only
 *   offering the managed provider to users.
 */
class AIProvidersList
{
    /**
     * @var array<string, AIProvider>
     */
    private $providers = [];

    /**
     * Selectable flag per provider ID, see the class docblock.
     *
     * @var array<string, bool>
     */
    private $selectable = [];

    /**
     * Registers a provider, unless its ID is already taken.
     *
     * Provider IDs are unique and cannot be overwritten: the first registration
     * for a given ID wins and later registrations are ignored. This protects the
     * built-in providers (and a provider that is centrally forced in a managed
     * multi-tenant environment) from being shadowed by another plugin. The
     * selectable flag of the first registration is kept for the same reason.
     *
     * @return bool True when the provider was added, false when an entry for the
     *              same ID already existed and this registration was ignored
     *              (also logged as a warning so the collision is visible).
     */
    public function addProvider(AIProvider $provider, bool $selectable = true): bool
    {
        $providerId = $provider->getId();

        if (isset($this->providers[$providerId])) {
            StaticContainer::get(LoggerInterface::class)->warning(
                'AI provider "{id}" is already registered as {existing}; ignoring duplicate registration of {ignored}.',
                [
                    'id' => $providerId,
                    'existing' => get_class($this->providers[$providerId]),
                    'ignored' => get_class($provider),
                ]
            );

            return false;
        }

        $this->providers[$providerId] = $provider;
        $this->selectable[$providerId] = $selectable;

        return true;
    }

    public function removeProvider(string $providerId): void
    {
        unset($this->providers[$providerId], $this->selectable[$providerId]);
    }

    /**
     * Marks a registered provider as selectable or restricted. Intended for
     * `AIProviders.filterAIProviders` subscribers; unknown IDs are ignored.
     */
    public function setSelectable(string $providerId, bool $selectable): void
    {
        if (isset($this->providers[$providerId])) {
            $this->selectable[$providerId] = $selectable;
        }
    }

    /**
     * Returns whether the provider may be offered in the administration UI and
     * used as the default provider. Unknown providers are not selectable.
     */
    public function isSelectable(string $providerId): bool
    {
        return $this->selectable[$providerId] ?? false;
    }

    public function hasProvider(string $providerId): bool
    {
        return isset($this->providers[$providerId]);
    }

    public function getProvider(string $providerId): ?AIProvider
    {
        return $this->providers[$providerId] ?? null;
    }

    /**
     * Returns all registered providers, including restricted ones. Completion
     * requests resolve against this list; admin surfaces must use
     * {@link getSelectableProviders()} instead so restricted providers stay
     * hidden.
     *
     * @return list<AIProvider>
     */
    public function getProviders(): array
    {
        return array_values($this->providers);
    }

    /**
     * Returns the providers that may be shown in the administration UI and
     * chosen as the default provider.
     *
     * @return list<AIProvider>
     */
    public function getSelectableProviders(): array
    {
        return array_values(array_filter(
            $this->providers,
            function (AIProvider $provider): bool {
                return $this->isSelectable($provider->getId());
            }
        ));
    }
}
