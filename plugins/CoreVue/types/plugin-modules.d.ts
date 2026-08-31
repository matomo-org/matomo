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
  export function translate(
    translationStringId: string,
    ...values: (string|string[]|number|number[]|boolean|boolean[])[]
  ): string;

  // Matomo is window.piwik, so the global interface is its complete public type.
  export const Matomo: PiwikGlobal;

  export const NumberFormatter: {
    defaultMinFractionDigits: number;
    defaultMaxFractionDigits: number;
    parseFormattedNumber(value: string): number;
    formatNumber(
      value: string|number,
      maxFractionDigits?: number,
      minFractionDigits?: number,
    ): string;
    formatPercent(
      value: string|number,
      maxFractionDigits?: number,
      minFractionDigits?: number,
    ): string;
    formatCurrency(
      value: string|number,
      currency: string,
      maxFractionDigits?: number,
      minFractionDigits?: number,
    ): string;
    formatNumberCompact(value: string|number): string;
    formatCurrencyCompact(value: string|number, currency: string): string;
    formatEvolution(
      evolution: string|number,
      maxFractionDigits?: number,
      minFractionDigits?: number,
      noSign?: boolean,
    ): string;
    calculateAndFormatEvolution(
      currentValue: string|number,
      pastValue: string|number,
      noSign?: boolean,
    ): string;
  };

  export const ActivityIndicator: import('vue').Component;
  export const MatomoLoader: import('vue').Component;
  export const ContentBlock: import('vue').Component;

  export function createVueApp(
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    ...args: any[]
  ): import('vue').App<Element>;

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
}
