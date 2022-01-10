const DEFAULT_DEBOUNCE_DELAY = 300;

export default function debounce<This, Args extends unknown[]>(
  fn: (this: This, ...args: Args) => void,
  delayInMs = DEFAULT_DEBOUNCE_DELAY,
): (this: This, ...args: Args) => void {
  let timeout: ReturnType<typeof setTimeout>;

  return function wrapper(this: This, ...args: Args): void {
    if (timeout) {
      clearTimeout(timeout);
    }

    timeout = setTimeout(() => {
      fn.call(this, ...args);
    }, delayInMs);
  };
}
