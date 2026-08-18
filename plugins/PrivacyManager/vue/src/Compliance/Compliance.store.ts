/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { AjaxHelper } from 'CoreHome';

export interface CompliancePolicy {
  id: string;
  title: string;
  description: string;
}

export async function fetchCompliancePolicies(): Promise<CompliancePolicy[]> {
  return AjaxHelper.fetch<CompliancePolicy[]>(
    {
      method: 'PrivacyManager.getCompliancePolicies',
    },
    {
      createErrorNotification: false,
    },
  );
}
