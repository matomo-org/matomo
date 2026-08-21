/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Which half of the report a group belongs to. `incoming` is what the legacy renderer called the
 * left side (referrers and previous pages), `outgoing` the right side (following pages and exits).
 */
export type TransitionsSide = 'incoming'|'outgoing';

/**
 * Where the report is mounted. `embedded` is the Transitions page/widget, `popover` the row action
 * opened from a data table. The two differ in how a "previous/following page" row navigates.
 */
export type TransitionsContext = 'embedded'|'popover';

/**
 * `action` rows are the detail rows of the open group, one per page, download or outlink.
 * `summary` rows stand for a whole group that is not currently open.
 */
export type TransitionsRowKind = 'action'|'summary';

/** A single detail row inside a group, as returned by Transitions.getTransitionsForAction. */
export interface TransitionsDetail {
  label?: string;
  url?: string;
  referrals: number;
  /** Share of the group total, in percent. Added by Piwik_Transitions_Model.addPercentagesToData. */
  percentage: number;
}

/** A row as rendered in one of the two side columns. */
export interface TransitionsRowData {
  key: string;
  kind: TransitionsRowKind;
  /** Morpheus icon class shown in the row's leading tile. */
  icon: string;
  /** Text shown in the row, already shortened for display. */
  label: string;
  /** Untruncated label, shown as a tooltip when `label` was shortened. */
  fullLabel: string;
  /** The row's own count, phrased in its metric's unit, e.g. "3 pageviews" or "1 downloads". */
  countLabel: string;
  /** Formatted percentage shown in the row pill. */
  percentage: string;
  /** Share of all pageviews, 0..1. Drives the ribbon thickness. */
  share: number;
  /** An external URL to open, when the row points at one. */
  externalUrl?: string;
  /** A page the report can switch to, when the row points at an internal page. */
  transitionUrl?: string;
  /** The group a summary row opens when clicked, when it has detail rows behind it. */
  opensGroup?: string;
  isOthers: boolean;
}

/** A group of rows, i.e. one referrer type or one kind of following action. */
export interface TransitionsGroupData {
  name: string;
  side: TransitionsSide;
  title: string;
  nbTransitions: number;
  /** Share of all pageviews, 0..1. */
  share: number;
  /** Whether the group has detail rows to open. Direct entries and exits do not. */
  canExpand: boolean;
  /** The group's own count, phrased in its metric's unit. */
  countLabel: string;
  /** The detail rows, shown while the group is the open one on its side. */
  rows: TransitionsRowData[];
  /** The single row that stands for this group while it is not the open one. */
  summaryRow: TransitionsRowData;
}

/** One block of rows in a column: either the open group, or everything else on that side. */
export interface TransitionsSectionData {
  key: string;
  side: TransitionsSide;
  title: string;
  /** The outgoing side's catch-all block carries no heading. */
  showHeading: boolean;
  /** Total across the block, phrased as pageviews, shown in the heading badge. */
  badge: string;
  rows: TransitionsRowData[];
}

/** One line in the center card's metric list. Every metric is listed, including the zeros. */
export interface TransitionsMetricData {
  key: string;
  /** Group this metric summarises, so hovering it can highlight the matching ribbons. */
  groupName: string;
  side: TransitionsSide;
  /**
   * The inline label split around its value, so the value can be emphasised without the label
   * having to carry markup.
   */
  labelBefore: string;
  labelAfter: string;
  value: number;
  valueLabel: string;
  /** Tooltip showing the share of all pageviews. */
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
  actionType: string;
  actionName: string;
  title: string;
  /** Set when the action is a URL, so the card's icon can link out. */
  titleUrl?: string;
  date: string;
  pageviews: number;
  loops: number;
  loopsLabel: string;
  loopsTooltip: string;
  pageviewsLabel: string;
  pageviewsTooltip: string;
  /** Totals shown above each of the card's metric lists. */
  incomingTotal: number;
  outgoingTotal: number;
  groups: TransitionsGroupData[];
  metrics: TransitionsMetricData[];
}
