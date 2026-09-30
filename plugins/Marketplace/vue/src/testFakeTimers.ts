/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Fake timers that let promises settle between the timers they fire.
 *
 * The Marketplace specs step through fetches raced against timeouts and fades chained off
 * resolved requests, so each timer has to see the promises the one before it resolved. Jest's own
 * fake timers cannot do that here: the jsdom environment this repository runs in only provides the
 * legacy implementation, which fires every due timer synchronously and has no async variants. So
 * this drives @sinonjs/fake-timers directly, the library Jest's modern timers are built on. It
 * fakes the timer functions and Date, but not process.nextTick or queueMicrotask, which promises
 * and Vue's scheduler still need.
 */

interface FakeClock {
  runToLastAsync(): Promise<number>;
  tickAsync(ms: number): Promise<number>;
  uninstall(): void;
}

interface FakeTimersModule {
  withGlobal(global: unknown): {
    install(config: { now: number, toFake: string[], loopLimit: number }): FakeClock;
  };
}

// Installed with Jest, which depends on it, rather than declared by Matomo itself.
/* eslint-disable global-require, import/no-extraneous-dependencies */
// eslint-disable-next-line @typescript-eslint/no-var-requires
const fakeTimers = require('@sinonjs/fake-timers') as FakeTimersModule;
/* eslint-enable global-require, import/no-extraneous-dependencies */

const TO_FAKE = [
  'setTimeout',
  'clearTimeout',
  'setInterval',
  'clearInterval',
  'setImmediate',
  'clearImmediate',
  'Date',
];

let clock: FakeClock|null = null;

function installedClock(): FakeClock {
  if (!clock) {
    throw new Error('call useFakeTimers() before stepping the fake clock');
  }

  return clock;
}

export function useFakeTimers(): void {
  if (clock) {
    return;
  }

  const globalObject = window as unknown as Record<string, unknown>;

  clock = fakeTimers.withGlobal(window).install({
    now: Date.now(),
    // sinon refuses to fake a method the environment does not have
    toFake: TO_FAKE.filter((method) => typeof globalObject[method] === 'function'),
    loopLimit: 10000,
  });
}

export function useRealTimers(): void {
  if (clock) {
    clock.uninstall();
    clock = null;
  }
}

/** Fires every timer due up to the last one pending now, settling promises around each. */
export async function runOnlyPendingTimersAsync(): Promise<void> {
  await installedClock().runToLastAsync();
}

/** Moves the clock on by `ms`, firing what falls due in order and settling promises around each. */
export async function advanceTimersByTimeAsync(ms: number): Promise<void> {
  await installedClock().tickAsync(ms);
}
