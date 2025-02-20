/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { Site } from 'CoreHome';

export type DashboardSortOrder = 'asc' | 'desc';
export type EvolutionTrend = -1 | 0 | 1;

export interface DashboardMetrics {
  hits: number;
  previous_hits: number;
  nb_pageviews: number;
  previous_nb_pageviews: number;
  nb_visits: number;
  previous_nb_visits: number;
  ratio: number;
  revenue: number;
  previous_revenue: number;
}

export interface DashboardSiteData extends DashboardMetrics, Site {
  isGroup?: number,
  label: string;
}

export interface KPICardBadge {
  label: string;
  title?: string;
}

export interface KPICardData {
  evolutionPeriod: string;
  evolutionTrend: EvolutionTrend;
  evolutionValue: string;
  badge?: KPICardBadge | null;
  icon: string;
  title: string;
  value: string;
  valueCompact: string;
}
