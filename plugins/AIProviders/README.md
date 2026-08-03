# AIProviders

[![Tests](https://github.com/matomo-org/plugin-AIProviders/actions/workflows/matomo-tests.yml/badge.svg)](https://github.com/matomo-org/plugin-AIProviders/actions/workflows/matomo-tests.yml)
[![Vue build check](https://github.com/matomo-org/plugin-AIProviders/actions/workflows/buildvue.yml/badge.svg)](https://github.com/matomo-org/plugin-AIProviders/actions/workflows/buildvue.yml)

Configure AI provider connections and default model settings used by Matomo AI features.

Built-in providers: Anthropic, OpenAI, Google, AWS Bedrock, and a generic custom provider for OpenAI-compatible endpoints.

## Configuration

Settings are managed from **Administration > System > AI Providers**.

The plugin stores the default provider, default capability level, and provider connection settings as Matomo system settings (not shown on the generic plugin settings page). API keys are only returned to the administration UI as masked state, not as secret values.

On a managed environment, the default provider is forced and locked via the `[AIProviders] defaultProvider` config setting. The AI Providers settings page and its admin menu entry are hidden entirely.

### Custom provider and local LLM servers

The generic custom provider talks to any **OpenAI-compatible** Chat Completions API. This includes hosted OpenAI-compatible services as well as local LLM servers such as Ollama, LM Studio, llama.cpp (`llama-server`), vLLM and LocalAI, which all expose the same `/v1/chat/completions` wire format.

Two things matter when configuring it:

- **API base URL.** Enter the URL up to and including the OpenAI-compatible API root. Most often, that is the `/v1` path. Matomo appends `/chat/completions` itself, so do not include it. Examples:
  - Ollama: `http://localhost:11434/v1`
  - LM Studio: `http://localhost:1234/v1`
  - vLLM / llama.cpp: `http://localhost:8000/v1`


- **API key.** Optional for the custom provider. Many local servers run without authentication, so the key may be left blank.

- **Model.** The "test connection" action probes `GET {base}/models` and populates the model picker from the result. Pick the model to use. The custom provider has **no built-in default model**.

### Managed credentials

Provider connection settings can also be supplied through namespaced DI values, the `[AIProviders]` config section, or environment variables instead of the administration UI. Per field, managed values win over the database value:

```php
AIProviders.openaiApiKey
```

```ini
[AIProviders]
openaiApiKey = "..."                ; or env MATOMO_AIPROVIDERS_OPENAI_API_KEY
custom-providerApiKey = "..."       ; or env MATOMO_AIPROVIDERS_CUSTOM_PROVIDER_API_KEY
custom-providerEndpointUrl = "..."  ; or env MATOMO_AIPROVIDERS_CUSTOM_PROVIDER_ENDPOINT_URL
```

`<provider>EndpointUrl` only applies to the providers that have an endpoint field at all — the custom provider's base URL and AWS Bedrock's region. The fixed hosted providers (OpenAI, Anthropic, Google) always talk to their own API, so a value supplied for them is ignored.

Credentials supplied this way never appear in the UI as secret values and cannot be edited or removed there.

A managed API key is bound to the endpoint it belongs to. For a provider whose endpoint field is a free-form URL (the custom provider), supply `<provider>EndpointUrl` together with `<provider>ApiKey`: such a key is only ever sent to the endpoint supplied alongside it, and an endpoint stored on the instance is ignored. Without a managed endpoint the provider has no destination and reports as not connected, so do not set `<provider>ApiKey` for a provider whose endpoint the instance should choose itself.

Saving or testing a different endpoint is then rejected with an error naming the config key to set instead. Only the endpoint is bound this way, not the key: a connection test may still submit an API key of its own, which is then the one sent to the managed endpoint. Providers that expand the field into a host they control, such as AWS Bedrock with its region, are unaffected by the *key* binding.

Supplying `<provider>EndpointUrl` on its own — without an API key — locks the endpoint field the same way, for the custom provider and for Bedrock's region alike: the supplied value is the one used, and saving or testing a different one is rejected rather than silently discarded, so a connection test can never report a pairing that a save would refuse to store. An endpoint the instance had configured before stays in the database untouched and takes effect again once the supplied value is removed.

In a multi-tenant setup the `config.ini.php` is scoped to each tenant, so the `[AIProviders]` section is the natural place to give each tenant its own provider credentials and forced default. Environment variables are process-wide and shared across tenants, so prefer the config file when the value must differ per tenant.

### Restricted providers and the provider selection allowlist

A managed environment can demote providers to *restricted* (non-selectable) in the `AIProviders.filterAIProviders` event via `AIProvidersList::setSelectable()`. Restricted providers are hidden from every admin surface and can never become the default, but stay registered for completions.

Plugins listed in the allowlist may target a specific provider and model per request even though the default provider is forced.

```ini
[AIProviders]
defaultProvider = "openai"
providerSelectionAllowlist[] = "ExamplePlugin"
```


## Usage from other plugins

To use a provider from another plugin, call the AIProvider service like so:

```php
use Piwik\Container\StaticContainer;
use Piwik\Plugins\AIProviders\AIRequest;
use Piwik\Plugins\AIProviders\AIProviderService;

$service = StaticContainer::get(AIProviderService::class);
$response = $service->complete(new AIRequest('why is the sky blue, answer in 7 words', 'YourPlugin'));
$text = $response->getText();
```

When a managed environment forces a provider from configuration, the service also ignores caller-provided provider and model overrides unless the calling plugin is on the `providerSelectionAllowlist` (see above), in which case its requested provider and model are honoured.

`AIProviderResponse` returns the generated text plus request metadata. `toArray()` returns:

```php
[
    'providerId' => 'openai',              // provider identifier used for the request
    'providerName' => 'OpenAI',            // human-readable provider name
    'model' => 'gpt-4.1-mini',             // model used
    'text' => 'Generated response text.',
    'inputTokens' => 42,                   // input/prompt tokens reported by the provider, or null
    'outputTokens' => 12,                  // output/completion tokens reported by the provider, or null
    'reasoningLevel' => 'none',            // reasoning level used
    'webSearchEnabled' => false,           // whether provider-side web search was used
    'executionTimeMs' => 1234,             // total request time in milliseconds, including retries, or null
    'stopReason' => 'stop',                // provider stop reason, if available, or null
]
```

### JSON mode

For structured output, call `withJsonResponse()` and read the decoded object with `getJsonData()`:

```php
$response = $service->complete(
    (new AIRequest($prompt, 'Goals'))->withJsonResponse()
);
$data = $response->getJsonData(); // array, or null if the model did not return valid JSON
```

Each provider asks for JSON the best way it can: `response_format` (OpenAI-compatible), `responseMimeType` (Google). And AIProviders always adds a system instruction to return a single JSON object, so it works for providers without a native option (for example Anthropic) too. The model can still occasionally return invalid JSON, so always handle a `null` from `getJsonData()`.

## Conversations (multi-turn, tool calling)

`complete()` is prompt-in/text-out. When your plugin maintains a back-and-forth conversation and dispatches tool calls itself (as AskMatomo does), use `AIConversationRequest` with `AIProviderService::converse()` instead. The service resolves the provider exactly like `complete()` (forced provider, allowlisted caller selection, then the configured default) and returns one assistant turn per call.

```php
use Piwik\Container\StaticContainer;
use Piwik\Plugins\AIProviders\AIConversationRequest;
use Piwik\Plugins\AIProviders\AIConversationResponse;
use Piwik\Plugins\AIProviders\AIProviderService;

$service = StaticContainer::get(AIProviderService::class);

$messages = [
    ['role' => 'user', 'content' => [['type' => 'text', 'text' => 'How many visits yesterday?']]],
];

$response = $service->converse(
    (new AIConversationRequest($messages, 'YourPlugin'))
        ->withSystemPrompt($systemPrompt)
        ->withTools($toolCatalog) // optional; MCP-aligned shape, see below
        ->withMaxTokens(2048)
);

if ($response->getStopReason() === AIConversationResponse::STOP_TOOL_USE) {
    // Run the requested tool_use blocks, append this turn and the tool
    // results to $messages, and call converse() again.
}
```

Messages, tools, and the assistant's content all use one **provider-agnostic canonical shape** documented in [`CanonicalMessage.php`](CanonicalMessage.php). Each provider translates that shape to and from its own wire format inside `converse()`. Running tools and appending their results to the history for the next call is the caller's responsibility. The service performs a single round-trip per call.

`AIConversationResponse` exposes the assistant content blocks (`getContent()`), the stop reason mapped onto the `STOP_*` constants (`getStopReason()`), a text convenience (`getText()`), token usage, and the raw decoded provider response. Treat an unknown stop reason like `STOP_END_TURN`. Unlike `complete()`, an empty text turn is valid here: a turn may consist solely of `tool_use` blocks.

Not every provider supports conversations. Gate conversational features on availability.

```php
if (!$service->canConverse()) {
    // Hide or disable the feature.
}
```
