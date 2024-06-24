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
  hits_evolution: string;
  hits_evolution_trend: EvolutionTrend;
  nb_hits: string;
  nb_pageviews: string;
  nb_visits: string;
  pageviews_evolution: string;
  pageviews_evolution_trend: EvolutionTrend;
  visits_evolution: string;
  visits_evolution_trend: EvolutionTrend;
  revenue: string;
  revenue_evolution: string;
  revenue_evolution_trend: EvolutionTrend;
}

export interface DashboardSiteData extends DashboardMetrics, Site {
  isGroup?: number,
  label: string;
}

export interface KPICardData {
  evolutionPeriod: string;
  evolutionTrend: EvolutionTrend;
  evolutionValue: string;
  badge: string;
  icon: string;
  title: string;
  value: string;
}
