interface PiwikGlobal {
  timezoneOffset: number;
  addCustomPeriod: <T>(name: string, periodClass: T) => void;
  shouldPropagateTokenAuth: boolean;
  token_auth: string;
  idSite: string|number;
  period: string;
  currentDateString: string;
}

let piwik: PiwikGlobal;

function _pk_translate(translationStringId: string, values: string[]): string;

/**
 * global ajax queue
 *
 * @type {Array} array holding XhrRequests with automatic cleanup
 */
interface GlobalAjaxQueue extends Array<XMLHttpRequest|null> {
  active:number;

  /**
   * Removes all finished requests from the queue.
   *
   * @return {void}
   */
  clean();

  /**
   * Extend Array.push with automatic cleanup for finished requests
   *
   * @return {Object}
   */
  push();

  /**
   * Extend with abort function to abort all queued requests
   *
   * @return {void}
   */
  abort();
}

interface Window {
  globalAjaxQueue: GlobalAjaxQueue;
}

interface PiwikPopoverGlobal {
  isOpen();
}

let Piwik_Popover: PiwikPopoverGlobal;

interface PiwikHelperGlobal {
  escape(text: string): string;
}

let piwikHelper: PiwikHelperGlobal;

interface BroadcastGlobal {
  getValueFromUrl(paramName: string, url?: string): string;
  getValueFromHash(paramName: string, url?: string): string;
  isWidgetizeRequestWithoutSession(): boolean;
}

let broadcast: BroadcastGlobal;
