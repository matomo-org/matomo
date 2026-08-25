<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\AIProviders;

/**
 * Reference documentation for the canonical, provider-agnostic conversation
 * message shape used by {@link AIConversationRequest} and {@link AIConversationResponse}.
 *
 * Callers persist, build, and read this shape and every provider translates
 * it to and from its own format inside its `converse()` implementation,
 * so swapping the configured provider never changes the calling plugin implementation.
 *
 * A message is an envelope of role + content blocks:
 *
 *     {
 *         role: 'user' | 'assistant' | 'tool',
 *         content: list<ContentBlock>
 *     }
 *
 * Roles:
 * - 'user'      Human-authored text.
 * - 'assistant' Model output (text and/or tool_use blocks).
 * - 'tool'      Tool execution results fed back to the model.
 *
 * Content block shapes:
 *
 *   Text block (user or assistant):
 *     { type: 'text', text: string }
 *
 *   Reasoning block (assistant only):
 *     { type: 'reasoning', text: string }
 *
 *     Model reasoning surfaced separately from the answer. This block is
 *     display-only and providers must not replay it to a model on later turns.
 *
 *   Tool use block (assistant only):
 *     { type: 'tool_use', id: string, name: string, input: array<string, mixed> }
 *
 *     `id` is the provider's correlation id: callers must never invent or
 *     rewrite it. `input` is a JSON object; providers must keep an empty
 *     object as `{}` (stdClass) on the wire to satisfy providers that reject
 *     `[]` for object slots.
 *
 *     A block may carry additional provider-specific fields (e.g. Google's
 *     `thoughtSignature`, which Gemini 3 requires echoed back on replay).
 *     Callers must persist and replay blocks verbatim so such fields survive
 *     the round trip.
 *
 *   Tool result block (tool role only):
 *     {
 *         type: 'tool_result',
 *         tool_use_id: string,
 *         content: list<ToolContentBlock>,
 *         structuredContent?: array<string, mixed>|null,
 *         is_error: bool
 *     }
 *
 *     Where `content` is the MCP content block list emitted by the tool
 *     (each `{type: 'text' | 'image' | …, …}`) and `structuredContent`
 *     carries the tool's optional structured output word for word. Providers with
 *     a native JSON return path will prefer `structuredContent` when present.
 *
 * ## Importable PHPStan types
 *
 * The request/response and provider boundaries import these aliases (with
 * `@phpstan-import-type ... from CanonicalMessage`) instead of restating the
 * shape inline, so the contract has a single home that the type checker pins:
 *
 * @phpstan-type CanonicalContentBlockArray array<string, mixed>
 * @phpstan-type CanonicalMessageArray array{role: string, content: list<CanonicalContentBlockArray>}
 *
 * `CanonicalContentBlockArray` is intentionally an open map rather than a union of
 * the text/tool_use/tool_result shapes above: providers build and read blocks
 * dynamically, so the per-type shapes are documented here as the contract but
 * not enforced as a closed type.
 *
 * The class is never instantiated; it hosts this contract, the PHPStan
 * aliases the boundaries import, and small static helpers for reading the
 * canonical shape that producers and providers would otherwise reimplement.
 */
final class CanonicalMessage
{
    private function __construct()
    {
    }

    /**
     * Extracts the valid `tool_use` blocks from a canonical content list, in
     * order, as `{id, name, input}`. Non-tool_use blocks and malformed
     * tool_use blocks (missing id/name or a non-object input) are skipped, and
     * input keys are normalised to strings so the result always carries a plain
     * arguments object.
     *
     * @param list<CanonicalContentBlockArray> $content canonical content blocks
     * @return list<array{id: string, name: string, input: array<string, mixed>}>
     */
    public static function toolUseBlocks(array $content): array
    {
        $blocks = [];
        foreach ($content as $block) {
            if (($block['type'] ?? null) !== 'tool_use') {
                continue;
            }
            $id = $block['id'] ?? null;
            $name = $block['name'] ?? null;
            $input = $block['input'] ?? [];
            if (!is_string($id) || !is_string($name) || !is_array($input)) {
                continue;
            }

            $normalizedInput = [];
            foreach ($input as $key => $value) {
                if (is_string($key)) {
                    $normalizedInput[$key] = $value;
                }
            }

            $blocks[] = ['id' => $id, 'name' => $name, 'input' => $normalizedInput];
        }

        return $blocks;
    }
}
