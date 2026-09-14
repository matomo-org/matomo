/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Everything the report's specs need from CoreHome. One stand-in for all of them, so a missing
 * placeholder cannot fail in one spec and pass in the next.
 *
 * postEvent arrives as a function, not the spy, because vi.mock() factories are hoisted above the
 * spec's consts. Imports nothing, because a factory cannot reach a top-level import.
 */
export function coreHomeMock(postEvent: (...args: unknown[]) => void) {
  return {
    ActivityIndicator: { template: '<div class="activityIndicator" />', props: ['loading'] },
    Matomo: {
      postEvent: (...args: unknown[]) => postEvent(...args),
      helper: {
        htmlDecode: (value: string) => {
          const textArea = document.createElement('textarea');
          textArea.innerHTML = value;
          return textArea.value;
        },
      },
    },
    NumberFormatter: {
      formatNumber: (value: number) => String(value),
      formatPercent: (value: number) => `${value}%`,
    },
    translate: translateStub,
  };
}

/** Key, then arguments, then %s substitution -- so a spec can assert a value reached the string. */
export function translateStub(key: string, ...args: string[]): string {
  let index = 0;
  return `${key}`.concat(args.length ? `:${args.join('|')}` : '')
    .replace(/%(\d+\$)?s/g, () => {
      const value = args[index];
      index += 1;
      return value;
    });
}
