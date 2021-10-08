/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { IAngularStatic } from 'angular';

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

interface PiwikPopoverGlobal {
  isOpen();
}

let Piwik_Popover: PiwikPopoverGlobal;

interface PiwikHelperGlobal {
  escape(text: string): string;
  redirect(params: any);
}

let piwikHelper: PiwikHelperGlobal;

interface BroadcastGlobal {
  getValueFromUrl(paramName: string, url?: string): string;
  getValueFromHash(paramName: string, url?: string): string;
  isWidgetizeRequestWithoutSession(): boolean;
}

let broadcast: BroadcastGlobal;

interface PiwikGlobal {
  timezoneOffset: number;
  addCustomPeriod: (name: string, periodClass: any) => void;
  shouldPropagateTokenAuth: boolean;
  token_auth: string;
  idSite: string|number;
  siteName: string;
  period?: string;
  currentDateString?: string;
  startDateString?: string;
  endDateString?: string;
  userCapabilities: string[];
  piwik_url: string;
  helper: PiwikHelperGlobal;
  broadcast: BroadcastGlobal;

  updatePeriodParamsFromUrl(): void;
  updateDateInTitle(date: string, period: string): void;
  hasUserCapability(capability: string): boolean;
}

let piwik: PiwikGlobal;

// add the objects to Window so we can access them through window if needed
declare global {
  interface Window {
    angular: IAngularStatic;
    globalAjaxQueue: GlobalAjaxQueue;
    piwik: PiwikGlobal;
    piwikHelper: PiwikHelperGlobal;
    broadcast: BroadcastGlobal;
    hasBlockedContent: boolean;

    _pk_translate(translationStringId: string, values: string[]): string;
  }
}
