/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

export const FORMATS_WITHOUT_EXPANDED = ['CSV', 'TSV'];

export type SubtableMode = 'flat' | 'expanded' | null;

export interface SubtablePreference {
  hasUserPreference: boolean;
  preferredMode: SubtableMode;
}

export interface EffectiveSubtableOptions {
  optionFlat: boolean;
  optionExpanded: boolean;
}

export function isFormatWithoutExpanded(format: string): boolean {
  return FORMATS_WITHOUT_EXPANDED.includes(format);
}

export function resolveInitialSubtablePreference(
  initialOptionFlat: boolean,
  initialOptionExpanded: boolean,
  initialReportFormat: string,
): SubtablePreference {
  // `hasUserPreference=false` means "use the product default behavior":
  // TSV/CSV start flat, and non-TSV/CSV start expanded.
  if (initialOptionFlat) {
    if (isFormatWithoutExpanded(initialReportFormat)) {
      return {
        hasUserPreference: false,
        preferredMode: null,
      };
    }

    return {
      hasUserPreference: true,
      preferredMode: 'flat',
    };
  }

  if (initialOptionExpanded) {
    return {
      hasUserPreference: true,
      preferredMode: 'expanded',
    };
  }

  return {
    hasUserPreference: true,
    preferredMode: null,
  };
}

export function resolveEffectiveSubtableOptions(
  hasSubtables: boolean,
  canExportFlat: boolean,
  reportFormat: string,
  subtablePreference: SubtablePreference,
): EffectiveSubtableOptions {
  const { hasUserPreference, preferredMode } = subtablePreference;

  if (!hasSubtables) {
    return {
      optionFlat: false,
      optionExpanded: false,
    };
  }

  if (isFormatWithoutExpanded(reportFormat)) {
    if (!canExportFlat) {
      return {
        optionFlat: false,
        optionExpanded: false,
      };
    }

    return {
      optionFlat: !hasUserPreference || preferredMode === 'flat',
      optionExpanded: false,
    };
  }

  if (!hasUserPreference) {
    return {
      optionFlat: false,
      optionExpanded: true,
    };
  }

  if (preferredMode === 'flat') {
    return canExportFlat
      ? { optionFlat: true, optionExpanded: false }
      : { optionFlat: false, optionExpanded: true };
  }

  if (preferredMode === 'expanded') {
    return {
      optionFlat: false,
      optionExpanded: true,
    };
  }

  return {
    optionFlat: false,
    optionExpanded: false,
  };
}
