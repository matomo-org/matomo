/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

export const keywordSearch = (keyword: string): string => {
  return `https://matomo.org?mtm_campaign=App_Help&mtm_source=Matomo_App&mtm_keyword=${keyword}&s=${keyword}`;
};
