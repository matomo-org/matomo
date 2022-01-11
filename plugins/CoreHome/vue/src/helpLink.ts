/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

export const keywordSearch = (keyword: string): string => {
  let mtm_medium = 'cpc';
  // test if is email
  if (keyword.toLowerCase().match(/^([a-zA-Z0-9_\-\.]+)%40([a-zA-Z0-9_\-\.]+)\.([a-zA-Z]{2,5})$/)) {
    mtm_medium = 'email';
  }
  return `https://matomo.org?mtm_campaign=App_Help&mtm_source=Matomo_App&mtm_medium=${mtm_medium}&s=${keyword}`;
};
