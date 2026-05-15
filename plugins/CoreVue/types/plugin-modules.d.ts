// Plugin-name imports (e.g. `import { translate } from 'CoreHome'`) are wired
// at build time by webpack externals + a generated @types/<Plugin>/ tree. That
// generated tree is only present during a full prod build, so ts-jest needs
// these ambient declarations to type-check spec files that pull in production
// source which uses the externals.

declare module 'CoreHome' {
  export const ComparisonsStoreInstance: {
    getSegmentComparisons(): Array<{ params: { segment: string } }>;
    isComparisonEnabled(): boolean | null;
  };
  export function translate(translationStringId: string, values?: unknown[]): string;
}
