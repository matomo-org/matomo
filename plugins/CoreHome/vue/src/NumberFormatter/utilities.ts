/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import NumberFormatter from './NumberFormatter';

export function formatNumber(
  val: string|number,
  maxFractionDigits?: number,
  minFractionDigits?: number,
): string {
  return NumberFormatter.formatNumber(val, maxFractionDigits, minFractionDigits);
}

export function formatPercent(
  val: string|number,
  maxFractionDigits?: number,
  minFractionDigits?: number,
): string {
  return NumberFormatter.formatPercent(val, maxFractionDigits, minFractionDigits);
}

export function formatCurrency(
  val: string|number,
  cur: string,
  maxFractionDigits?: number,
  minFractionDigits?: number,
): string {
  return NumberFormatter.formatCurrency(val, cur, maxFractionDigits, minFractionDigits);
}

export function formatEvolution(
  val: string|number,
  maxFractionDigits?: number,
  minFractionDigits?: number,
  noSign?: boolean,
): string {
  return NumberFormatter.formatEvolution(val, maxFractionDigits, minFractionDigits, noSign);
}

export function calculateAndFormatEvolution(
  valCur: string|number,
  valPrev: string|number,
  noSign?: boolean,
): string {
  return NumberFormatter.calculateAndFormatEvolution(valCur, valPrev, noSign);
}
