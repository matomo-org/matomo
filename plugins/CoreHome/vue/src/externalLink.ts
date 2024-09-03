/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
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
  const pkArgs = values as (string|null)[];
  if (!window._pk_externalRawLink) { // eslint-disable-line
    return url;
  }
  return window._pk_externalRawLink(url, pkArgs); // eslint-disable-line
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
