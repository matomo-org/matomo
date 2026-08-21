/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Everything the report's specs need from CoreHome.
 *
 * One stand-in for all of them on purpose. Each spec used to declare its own translate(), and the
 * three did not agree: one substituted placeholders, one dropped its arguments entirely, and one
 * joined them with a different separator. A missing placeholder was therefore a failure in one
 * spec and invisible in the next.
 *
 * Takes postEvent as a function rather than the spy itself, so the caller can defer the lookup:
 * vi.mock() factories are hoisted above the spec body's const declarations.
 *
 * Lives in its own module, importing nothing, because a mock factory cannot reach a top-level
 * import -- the specs pull this in with a dynamic import() from inside the factory instead.
 */
export function coreHomeMock(postEvent: (...args: unknown[]) => void) {
  return {
    ActivityIndicator: { template: '<div class="activityIndicator" />', props: ['loading'] },
    Matomo: {
      postEvent: (...args: unknown[]) => postEvent(...args),
      helper: { addBreakpointsToUrl: (url: string) => url },
    },
    NumberFormatter: {
      formatNumber: (value: number) => String(value),
      formatPercent: (value: number) => `${value}%`,
    },
    translate: translateStub,
  };
}

/**
 * Renders the key, then its arguments, then substitutes %s and %1$s style placeholders the way
 * Matomo's translate() does. Keeping the arguments in the output lets a spec assert that a value
 * reached the string at all, which asserting the key alone cannot.
 */
export function translateStub(key: string, ...args: string[]): string {
  let index = 0;
  return `${key}`.concat(args.length ? `:${args.join('|')}` : '')
    .replace(/%(\d+\$)?s/g, () => {
      const value = args[index];
      index += 1;
      return value;
    });
}
