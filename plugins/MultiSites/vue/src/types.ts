/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

export type EvolutionTrend = -1 | 0 | 1;

export interface KPICardData {
  evolutionPeriod: string;
  evolutionTrend: EvolutionTrend;
  evolutionValue: string;
  badge: string;
  icon: string;
  title: string;
  value: string;
}
