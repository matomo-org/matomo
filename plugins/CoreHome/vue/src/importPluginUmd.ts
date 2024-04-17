/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/* eslint-disable @typescript-eslint/no-explicit-any */

const pluginLoadingPromises: Record<string, Promise<unknown>> = {};

const PLUGIN_LOAD_TIMEOUT = 120;
const POLL_INTERVAL = 50;
const POLL_LIMIT = 1000;

// code based off webpack's generated code for import()
// currently does not load styles on demand
export default function importPluginUmd(plugin: string): Promise<unknown> {
  if (pluginLoadingPromises[plugin]) {
    return pluginLoadingPromises[plugin];
  }

  if ((window as any)[plugin]) {
    return Promise.resolve((window as any)[plugin]);
  }

  const pluginUmdPath = `?module=Proxy&action=getPluginUmdJs&plugin=${plugin}`;

  let promiseReject: ((value: unknown) => void) | undefined;
  let promiseResolve: ((value: unknown) => void) | undefined;

  const script = document.createElement('script') as any;
  script.charset = 'utf-8';
  script.timeout = PLUGIN_LOAD_TIMEOUT;
  script.src = pluginUmdPath;

  let timeout: ReturnType<typeof setTimeout>;

  // create error before stack unwound to get useful stacktrace later
  const error = new Error() as any;
  const onScriptComplete = (event?: Event) => {
    // avoid mem leaks in IE.
    script.onerror = null;
    script.onload = null;
    clearTimeout(timeout);

    // the script may not load entirely at the time onload is called, so we poll for a small
    // amount of time until the window.PluginName object appears
    let pollProgress = 0;
    function checkPluginInWindow() {
      pollProgress += POLL_INTERVAL;

      // promise was already handled
      if (!promiseReject || !promiseResolve) {
        return;
      }

      // promise was not resolved, and window object exists
      if ((window as any)[plugin] && promiseResolve) {
        try {
          promiseResolve((window as any)[plugin]);
        } finally {
          promiseReject = undefined;
          promiseResolve = undefined;
        }
        return;
      }

      // script took too long to execute or failed to execute, and no plugin object appeared in
      // window, so we report an error
      if (pollProgress > POLL_LIMIT) {
        try {
          const errorType = event && (event.type === 'load' ? 'missing' : event.type);
          const realSrc = event && event.target && (event.target as HTMLScriptElement).src;
          error.message = `Loading plugin ${plugin} on demand failed.\n(${errorType}: ${realSrc})`;
          error.name = 'PluginOnDemandLoadError';
          error.type = errorType;
          error.request = realSrc;
          promiseReject(error);
        } finally {
          promiseReject = undefined;
          promiseResolve = undefined;
        }

        return;
      }

      setTimeout(checkPluginInWindow, POLL_INTERVAL);
    }

    setTimeout(checkPluginInWindow, POLL_INTERVAL);
  };

  timeout = setTimeout(() => {
    onScriptComplete({ type: 'timeout', target: script } as unknown as Event);
  }, PLUGIN_LOAD_TIMEOUT);
  script.onerror = onScriptComplete;
  script.onload = onScriptComplete;

  document.head.appendChild(script);

  return new Promise((resolve, reject) => {
    promiseResolve = resolve;
    promiseReject = reject;
  });
}
