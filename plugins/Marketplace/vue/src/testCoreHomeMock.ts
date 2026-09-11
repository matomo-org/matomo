/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/** Key, then its arguments, so a spec can assert a value reached the string. */
export function translateStub(key: string, ...args: string[]): string {
  return args.length ? `${key}:${args.join(',')}` : key;
}

/**
 * Everything the Marketplace's component specs need from CoreHome, so a missing placeholder cannot
 * fail in one spec and pass in the next. Imports nothing: a vi.mock() factory is hoisted above
 * every import, so pull this in dynamically instead:
 *
 *   vi.mock('CoreHome', async () => (await import('../testCoreHomeMock')).coreHomeMock());
 */
export function coreHomeMock() {
  return {
    translate: translateStub,
    // Returns the key, which is what an untranslated key does in the browser: categoryLabel()
    // then falls back to ucfirst(slug), the path most category slugs really take.
    translateOrDefault: (key: string) => key,
    ucfirst: (value: string) => `${value.charAt(0).toUpperCase()}${value.slice(1)}`,
    MatomoUrl: {
      urlParsed: { value: {} },
      parsed: { value: { idSite: '1' } },
      hashParsed: { value: {} },
      stringify: (params: Record<string, unknown>) => new URLSearchParams(
        Object.entries(params).map(([key, value]) => [key, String(value)]),
      ).toString(),
    },
  };
}
