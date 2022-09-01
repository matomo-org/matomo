/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
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
