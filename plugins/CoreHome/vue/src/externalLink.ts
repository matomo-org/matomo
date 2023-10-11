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
 * @param url     URL to process
 * @param values  Optional [campaignOverride, sourceOverride, mediumOverride]
 * @return string
 */
export function externalRawLink(
  url: string,
  ...values: (string|null)[]
): string {
  if (!url) {
    return '';
  }

  const campaignOverride = (values.length > 0 && values[0] ? values[0] : null);
  const sourceOverride = (values.length > 1 && values[1] ? values[1] : null);
  const mediumOverride = (values.length > 2 && values[2] ? values[2] : null);
  const returnURL = new URL(url);
  const validDomain = returnURL.host.endsWith('matomo.org');
  const urlParams = new URLSearchParams(window.location.search);
  const module = urlParams.get('module');
  const action = urlParams.get('action');

  // Apply campaign parameters if domain is ok, config is not disabled and a value for medium exists
  if (validDomain && !window.piwik.disableTrackingMatomoAppLinks
    && ((module && action) || mediumOverride)) {
    const campaign = (campaignOverride === null ? 'Matomo_App' : campaignOverride);
    let source = (window.Cloud === undefined ? 'OnPremise' : 'Cloud');
    if (sourceOverride !== null) {
      source = sourceOverride;
    }

    /* eslint-disable prefer-template */
    const medium = (mediumOverride === null ? module + '.' + action : mediumOverride);

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
 * @param values  Optional [campaignOverride, sourceOverride, mediumOverride]
 * @return string
 */
export function externalLink(
  url: string,
  ...values: (string|null)[]
): string {
  if (!url) {
    return '';
  }

  const campaignOverride = (values.length > 0 && values[0] ? values[0] : null);
  const sourceOverride = (values.length > 1 && values[1] ? values[1] : null);
  const mediumOverride = (values.length > 2 && values[2] ? values[2] : null);
  const returnUrl = externalRawLink(url, campaignOverride, sourceOverride, mediumOverride);

  /* eslint-disable prefer-template */
  return '<a target="_blank" rel="noreferrer noopener" href="' + returnUrl + '">';
}
