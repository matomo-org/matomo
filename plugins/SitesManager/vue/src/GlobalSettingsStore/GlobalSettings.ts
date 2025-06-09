/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

interface GlobalSettings {
  keepURLFragmentsGlobal: boolean;
  defaultCurrency: string;
  defaultTimezone: string;
  excludedIpsGlobal?: string;
  excludedQueryParametersGlobal?: string;
  excludedUserAgentsGlobal?: string;
  excludedReferrersGlobal?: string;
  searchKeywordParametersGlobal?: string;
  searchCategoryParametersGlobal?: string;
  exclusionTypeForQueryParams: string;
}

export default GlobalSettings;
