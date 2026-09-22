/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/** `incoming` is the left side (referrers, previous pages), `outgoing` the right (following, exits). */
export type TransitionsSide = 'incoming'|'outgoing';

/** Where the report is mounted. The two differ in how a previous/following page row navigates. */
export type TransitionsContext = 'embedded'|'popover';

/** `action` rows are the open group's detail rows; `summary` rows stand for a closed group. */
export type TransitionsRowKind = 'action'|'summary';

/** A row as rendered in one of the two side columns. */
export interface TransitionsRowData {
  key: string;
  kind: TransitionsRowKind;
  icon: string;
  label: string;
  /** The untruncated label, or empty when `label` was not shortened. */
  fullLabel: string;
  countLabel: string;
  percentage: string;
  /** Share of this page's pageviews, 0..1. Drives the ribbon thickness. */
  share: number;
  externalUrl?: string;
  transitionUrl?: string;
  opensGroup?: string;
  isOthers: boolean;
}

/** One referrer type, or one kind of following action. */
export interface TransitionsGroupData {
  name: string;
  side: TransitionsSide;
  title: string;
  nbTransitions: number;
  /** Direct entries and exits have no detail rows to open. */
  canExpand: boolean;
  countLabel: string;
  rows: TransitionsRowData[];
  summaryRow: TransitionsRowData;
}

/** One block of rows in a column: either the open group, or everything else on that side. */
export interface TransitionsSectionData {
  key: string;
  side: TransitionsSide;
  title: string;
  /** Empty for the outgoing catch-all, whose groups share no unit to total. */
  badge: string;
  rows: TransitionsRowData[];
}

/** One line in the center card's metric list. Every metric is listed, zeros included. */
export interface TransitionsMetricData {
  key: string;
  groupName: string;
  side: TransitionsSide;
  /** The inline label split around its value, so the value can be emphasised without markup. */
  labelBefore: string;
  labelAfter: string;
  value: number;
  valueLabel: string;
  tooltip: string;
  canExpand: boolean;
}

/** The error contract of Transitions.getTransitionsForAction, resolved to displayable text. */
export interface TransitionsError {
  title: string;
  message: string;
  backLabel: string;
}

/** Everything the renderer needs for one action. */
export interface TransitionsReportData {
  actionName: string;
  title: string;
  /** Set when the action is a URL, so the card's icon can link out. */
  titleUrl?: string;
  loops: number;
  loopsLabel: string;
  loopsTooltip: string;
  pageviewsLabel: string;
  pageviewsTooltip: string;
  incomingTotal: number;
  outgoingTotal: number;
  groups: TransitionsGroupData[];
  metrics: TransitionsMetricData[];
}
