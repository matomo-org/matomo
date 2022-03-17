/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

export interface Extraction {
  dimension: string;
  pattern: string;
}

export interface CustomDimension {
  active: boolean;
  case_sensitive: boolean;
  extractions: Extraction[];
  idcustomdimension: string|number;
  idsite: string|number;
  index: string|number;
  name: string;
  scope: string;
}

export interface AvailableScope {
  name: string;
  numSlotsAvailable: number;
  numSlotsLeft: number;
  numSlotsUsed: number;
  supportsExtractions: boolean;
  value: string;
}

export interface ExtractionDimension {
  name: string;
  value: string;
}
