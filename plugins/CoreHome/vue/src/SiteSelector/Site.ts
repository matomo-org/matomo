/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

interface Site {
  idsite: string|number;
  name: string;
  type: string;
  group?: string;
  timezone: string;
  currency?: string;
  timezone_name: string;
  currency_name?: string;
  main_url: string;
  alias_urls: string[];
  excluded_ips: string;
  excluded_parameters: string;
  excluded_user_agents: string;
  delete_site_explanation?: string;
}

export default Site;
