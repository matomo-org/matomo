/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

interface Goal {
  idgoal: string|number;
  name: string;
  allow_multiple: string|number|boolean;
  case_sensitive: string|number|boolean;
  deleted: string|number|boolean;
  description: string;
  event_value_as_revenue: string|number|boolean;
  idsite: string|number;
  match_attribute: string;
  pattern: string;
  pattern_type: string;
  revenue: string|number;
  revenue_pretty?: string;
}

export default Goal;
