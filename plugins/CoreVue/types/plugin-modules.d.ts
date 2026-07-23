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
  export const ComparisonsStoreInstance: {
    getSegmentComparisons(): Array<{ params: { segment: string } }>;
    isComparisonEnabled(): boolean | null;
  };
  export function translate(translationStringId: string, values?: unknown[]): string;

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
