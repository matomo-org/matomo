/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

export interface SegmentMetadata {
  acceptedValues: string;
  category: string;
  name: string;
  needsMostFrequentValues: boolean;
  segment: string;
  sqlFilterValue: unknown;
  sqlSegment: string;
  type: string;
}

export interface SegmentOrCondition {
  segment: string;
  matches: string;
  value: string;

  id?: string;
  isLoading?: boolean;
}

export interface SegmentAndCondition {
  orConditions: SegmentOrCondition[];
}

export interface SavedSegment {
  idsegment?: string | number;
  definition: string;
  name: string;
  enable_all_users?: string | number;
  enable_only_idsite?: string | number;
  auto_archive?: string | number;
  starred?: boolean | string | number;
  starred_by?: string | null;
  login?: string;
  [key: string]: unknown;
}

export interface SegmentSelectorTranslations {
  [key: string]: string;
}

export interface SegmentSelectorEntry {
  key: string;
  type: 'header' | 'segment' | 'no-results';
  className?: string;
  classes?: string | string[] | Record<string, boolean>;
  compareButtonClass?: string;
  compareState?: string;
  compareTitle?: string;
  definition?: string;
  editState?: string;
  editTitle?: string;
  idsegment?: string | number;
  label: string;
  showCompareButton?: boolean;
  showEditButton?: boolean;
  showStarButton?: boolean;
  starState?: string;
  starTitle?: string;
  tooltip: string;
}

export interface SegmentSelectorViewModel {
  authorizedToCreateSegments: boolean;
  currentSegmentTitle: string;
  currentSegmentTooltip: string;
  currentSegmentValue: string;
  entries: SegmentSelectorEntry[];
  isExpanded: boolean;
  isUserAnonymous: boolean;
  loginUrl: string;
  manageSegmentsUrl: string;
}

export interface SegmentSelectorUserContext {
  isAnonymous: boolean;
  hasSuperUserAccess: boolean;
  login: string;
}
