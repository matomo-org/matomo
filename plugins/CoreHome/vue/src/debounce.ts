interface Callable {
  (...args: unknown[]): void;
}

const DEFAULT_DEBOUNCE_DELAY = 300;

export default function debounce<F extends Callable>(fn: F, delayInMs = DEFAULT_DEBOUNCE_DELAY): F {
  let timeout: ReturnType<typeof setTimeout>;

  return function wrapper(...args: Parameters<F>): void {
    if (timeout) {
      clearTimeout(timeout);
    }

    timeout = setTimeout(() => {
      fn.call(this, ...args);
    }, delayInMs);
  };
}
