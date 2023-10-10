/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Takes a raw URL and returns an HTML link tag for the URL, if the URL is for a matomo.org
 * domain then the URL will be modified to include campaign parameters
 *
 * @param url              URL to process
 * @param campaignOverride Optional
 * @param sourceOverride   Optional
 * @param mediumOverride   Optional
 */
export function externalRawLink(
  url: string,
  campaignOverride: string,
  sourceOverride: string,
  mediumOverride: string,
): string {
  if (!url) {
    return '';
  }

  const returnURL = new URL(url);
  const validDomain = returnURL.host.endsWith('matomo.org');
  const urlParams = new URLSearchParams(window.location.search);
  const module = urlParams.get('module');
  const action = urlParams.get('action');

  // Apply campaign parameters if domain is ok, config is not disabled and a value for medium exists
  if (validDomain && !window.piwik.disableTrackingMatomoAppLinks
    && ((module && action) || mediumOverride)) {
    const campaign = (campaignOverride === undefined ? 'Matomo_App' : campaignOverride);
    let source = (window.Cloud === undefined ? 'OnPremise' : 'Cloud');
    if (sourceOverride !== undefined) {
      source = sourceOverride;
    }

    /* eslint-disable prefer-template */
    const medium = (mediumOverride === undefined ? module + '.' + action : mediumOverride);

    returnURL.searchParams.set('mtm_campaign', campaign);
    returnURL.searchParams.set('mtm_source', source);
    returnURL.searchParams.set('mtm_medium', medium);
  }

  return returnURL.toString();
}

/**
 * Takes a raw URL and returns an HTML link tag for the URL, if the URL is for a matomo.org
 * domain then the URL will be modified to include campaign parameters
 *
 * @param url              URL to process
 * @param campaignOverride Optional
 * @param sourceOverride   Optional
 * @param mediumOverride   Optional
 */
export function externalLink(
  url: string,
  campaignOverride: string,
  sourceOverride: string,
  mediumOverride: string,
): string {
  if (!url) {
    return '';
  }
  const returnUrl = externalRawLink(url, campaignOverride, sourceOverride, mediumOverride);

  /* eslint-disable prefer-template */
  return '<a target="_blank" rel="noreferrer noopener" href="' + returnUrl + '">';
}
