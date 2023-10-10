/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Add or replace a URL parameter
 * @param url
 * @param paramName
 * @param paramValue
 */
function addParameterToUrl(url: string, paramName:string, paramValue:string): string {
  let returnUrl = url;
  /* eslint-disable prefer-template */
  const rx = new RegExp('\\b(' + paramName + '=).*?(&|#|$)');

  // Replace any existing parameter
  if (returnUrl.search(rx) >= 0) {
    /* eslint-disable prefer-template */
    return returnUrl.replace(rx, '$1' + paramValue + '$2');
  }
  // Add new parameter
  returnUrl = returnUrl.replace(/[?#]$/, '');
  /* eslint-disable prefer-template */
  return returnUrl + (returnUrl.indexOf('?') > 0 ? '&' : '?') + paramName + '=' + paramValue;
}

/**
 * Takes a raw URL and returns an HTML link tag for the URL, if the URL is for a matomo.org
 * domain then the URL will be modified to include campaign parameters
 *
 * @param url              URL to
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

  // TODO Detect 'disable_tracking_matomo_app_links' = 1 and ignore

  // Check if matomo.org domain
  const domains = ['matomo.org', 'www.matomo.org', 'developer.matomo.org', 'plugins.matomo.org'];
  const { length } = domains;
  let validDomain = false;
  for (let i = 0; i < length; i += 1) {
    if (url.includes(domains[i])) {
      validDomain = true;
    }
  }

  let returnUrl = url;

  const urlParams = new URLSearchParams(window.location.search);
  const module = urlParams.get('module');
  const action = urlParams.get('action');

  window.console.log(window);

  // Apply campaign parameters
  if (validDomain && ((module && action) || mediumOverride)) {
    const campaign = (campaignOverride === undefined ? 'Matomo_App' : campaignOverride);
    let source = (!window.Cloud ? 'OnPremise' : 'Cloud');
    if (sourceOverride !== undefined) {
      source = sourceOverride;
    }
    const medium = (mediumOverride === undefined ? module + '.' + action : mediumOverride);

    returnUrl = addParameterToUrl(returnUrl, 'mtm_campaign', campaign);
    returnUrl = addParameterToUrl(returnUrl, 'mtm_source', source);
    returnUrl = addParameterToUrl(returnUrl, 'mtm_medium', medium);
  }

  /* eslint-disable prefer-template */
  return '<a target="_blank" rel="noreferrer noopener" href="' + returnUrl + '">';
}
