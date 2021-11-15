/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import jqXHR = JQuery.jqXHR;
import { IAngularStatic } from 'angular';

declare global {
  type ParameterValue = string | number | null | undefined | ParameterValue[];
  type QueryParameters = {[name: string]: ParameterValue | QueryParameters};

  interface WrappedEventListener extends Function {
    wrapper?: (evt: Event) => void;
  }

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
    push(...args: (XMLHttpRequest|jqXHR|null)[]);

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

  interface ModalConfirmCallbacks {
    yes: () => void;
    no: () => void;
  }

  interface ModalConfirmOptions {
    onCloseEnd: () => void;
  }

  interface PiwikHelperGlobal {
    escape(text: string): string;
    redirect(params: any);
    htmlDecode(encoded: string): string;
    modalConfirm(element: JQuery|JQLite|HTMLElement|string, callbacks: ModalConfirmCallbacks, options: ModalConfirmOptions);
    getAngularDependency(eventName: string): any;
    isAngularRenderingThePage(): boolean;
    setMarginLeftToBeInViewport(elementToPosition: JQuery|JQLite|HTMLElement|string);
    lazyScrollTo(element: JQuery|JQLite|HTMLElement|string, time: number, forceScroll?: boolean);
  }

  let piwikHelper: PiwikHelperGlobal;

  interface BroadcastGlobal {
    getValueFromUrl(paramName: string, url?: string): string;
    getValuesFromUrl(paramName: string, decode?: boolean): QueryParameters;
    getValueFromHash(paramName: string, url?: string): string;
    isWidgetizeRequestWithoutSession(): boolean;
    updateParamValue(newParamValue: string, urlStr: string): string;
    propagateNewPage(str: string, showAjaxLoading?: boolean, strHash?: string, paramsToRemove?: string[]);
  }

  let broadcast: BroadcastGlobal;

  interface ColorManagerService {
    getColor(namespace: string, name: string): string;
    getColors(namespace: string, names: string[], asArray?: boolean): string[]|{[name: string]: string};
  }

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
    ColorManager: ColorManagerService;
    ajaxRequestFinished?: () => void;
    minDateYear: number;
    minDateMonth: number;
    minDateDay: number;
    maxDateYear: number;
    maxDateMonth: number;
    maxDateDay: number;

    updatePeriodParamsFromUrl(): void;
    updateDateInTitle(date: string, period: string): void;
    hasUserCapability(capability: string): boolean;
    getBaseDatePickerOptions(): {[key: string]: any};

    on(eventName: string, listener: WrappedEventListener): void;
    off(eventName: string, listener: WrappedEventListener): void;
    postEvent(eventName: string, ...args: any[]): void;
    postEventNoEmit(eventName: string, ...args: any[]): void;
  }

  let piwik: PiwikGlobal;

  interface Window {
    angular: IAngularStatic;
    globalAjaxQueue: GlobalAjaxQueue;
    piwik: PiwikGlobal;
    piwikHelper: PiwikHelperGlobal;
    broadcast: BroadcastGlobal;
    hasBlockedContent: boolean;
    piwik_translations: {[key: string]: string};

    _pk_translate(translationStringId: string, values: string[]): string;
    require(p: string): any;
  }
}
