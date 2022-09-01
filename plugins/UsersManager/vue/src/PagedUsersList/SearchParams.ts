/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

interface SearchParams {
  idSite: number|string;
  limit: number;
  offset: number;
  filter_search: string;
  filter_access: string;
  filter_status: string;
}

export default SearchParams;
