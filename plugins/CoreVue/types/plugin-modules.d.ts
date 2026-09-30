// Plugin-name imports (e.g. `import { translate } from 'CoreHome'`) are wired
// at build time by webpack externals + a generated @types/<Plugin>/ tree. That
// generated tree is only present during a full prod build, so ts-jest needs
// these ambient declarations to type-check spec files that pull in production
// source which uses the externals.
//
// When adding a symbol here, mirror its COMPLETE public type from source, not a
// narrow subset: this ambient module also merges into other plugins' spec
// type-checks, so a narrow entry both drops members and collides with plugins
// that augment `CoreHome` with the fuller type.

declare module 'CoreHome' {
  export interface AjaxOptions {
    withTokenInUrl?: boolean;
    postParams?: QueryParameters;
    headers?: Record<string, string>;
    format?: string;
    createErrorNotification?: boolean;
    abortController?: AbortController;
    returnResponseObject?: boolean;
    errorElement?: HTMLElement|JQuery|string;
    redirectOnSuccess?: QueryParameters|boolean;
    abortable?: boolean;
  }

  export const AjaxHelper: {
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    fetch<R = any>(
      params: QueryParameters|QueryParameters[],
      options?: AjaxOptions,
    ): Promise<R>;
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    post<R = any>(
      params: QueryParameters,
      // eslint-disable-next-line @typescript-eslint/no-explicit-any
      postParams?: any,
      options?: AjaxOptions,
    ): Promise<R>;
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    oneAtATime<R = any>(
      method: string,
      options?: AjaxOptions,
    ): (params: QueryParameters, postParams?: QueryParameters) => Promise<R>;
  };

  export const ComparisonsStoreInstance: {
    getSegmentComparisons(): Array<{ params: { segment: string } }>;
    isComparisonEnabled(): boolean | null;
  };
  // Variadic, matching plugins/CoreHome/vue/src/translate.ts - the previous declaration
  // took a single array, which rejected the ordinary `translate(key, value)` call.
  export function translate(
    translationStringId: string,
    ...values: (string|string[]|number|number[]|boolean|boolean[])[]
  ): string;

  // `Matomo` is exported by CoreHome's index but was never declared here, so any plugin
  // importing it from the module alias failed to type-check.
  export const Matomo: {
    helper: {
      showAjaxLoading(): void;
      hideAjaxLoading(): void;
      modalConfirm(
        element: HTMLElement|string,
        handles?: Record<string, () => void>,
        below?: boolean,
      ): void;
    };
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    [key: string]: any;
  };

  export function translateOrDefault(
    translationStringIdOrText?: string,
    ...values: (string|string[]|number|number[]|boolean|boolean[])[]
  ): string;
  export function ucfirst(text?: string, locale?: string): string;

  // MatomoUrl's parsed/urlParsed/hashParsed are Vue computed refs over the decoded query.
  type ParsedQueryRef = import('vue').ComputedRef<
    import('vue').DeepReadonly<Record<string, unknown>>
  >;

  export const MatomoUrl: {
    readonly url: import('vue').Ref<URL | null>;
    readonly urlQuery: import('vue').ComputedRef<string>;
    readonly hashQuery: import('vue').ComputedRef<string>;
    readonly urlParsed: ParsedQueryRef;
    readonly hashParsed: ParsedQueryRef;
    readonly parsed: ParsedQueryRef;
    updateHashToUrl(urlWithoutLeadingHash: string): void;
    updateHash(params: QueryParameters | string): void;
    updateUrl(params: QueryParameters | string, hashParams?: QueryParameters | string): void;
    updateLocation(params: QueryParameters | string): void;
    getSearchParam(paramName: string): string;
    parse(query: string): QueryParameters;
    stringify(search: QueryParameters): string;
    getMenuPathSuffix(): { category: string; subcategory: string };
    getDateAndPeriodFromUrl(): { date: string; period: string };
    updatePageTitle(): void;
    updatePeriodParamsFromUrl(): void;
  };

  export interface NotificationType {
    id?: string;
    notificationInstanceId?: string;
    group?: string;
    title?: string;
    message: string;
    context: 'success'|'error'|'info'|'warning';
    type: 'toast'|'persistent'|'transient'|'help';
    noclear?: boolean;
    toastLength?: number;
    style?: string|Record<string, unknown>;
    class?: string;
    animate?: boolean;
    placeat?: string|HTMLElement|JQuery;
    prepend?: boolean;
  }

  export const NotificationsStore: {
    readonly state: import('vue').DeepReadonly<{ notifications: NotificationType[] }>;
    appendNotification(notification: NotificationType): void;
    prependNotification(notification: NotificationType): void;
    remove(id: string): void;
    parseNotificationDivs(): void;
    clearTransientNotifications(): void;
    show(notification: NotificationType): string;
    scrollToNotification(notificationInstanceId: string): void;
    toast(notification: NotificationType): void;
  };
}
