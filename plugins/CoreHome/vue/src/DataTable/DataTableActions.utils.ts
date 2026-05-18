/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

export function isBooleanLikeSet(value: number|string|boolean): boolean {
  return !!value && value !== '0';
}

export function resolveExportSupportsFlat(
  reportSupportsFlatten: boolean,
  flatParam: number|string|boolean,
): boolean {
  return reportSupportsFlatten || isBooleanLikeSet(flatParam);
}
